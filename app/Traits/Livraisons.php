<?php
namespace App\Traits;

use Illuminate\Http\Request;

use App\StockPrime;
use App\Stock;
use App\Livraison;
use App\Depots;
use App\RavitaillementVendeur;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AppException;

trait Livraisons {

  // RECUPERATION DE L'INVENTAIRE DU DEPOT
public function getInventaireForDepot(Request $request) {
  // global $tab =  [];
  $GLOBALS['tab'] = [];
  $stock  = StockPrime::where('depot',$request->input('ref'))->get();
  $serial   = Stock::where('depot',$request->input('ref'))->get();
  // dump($stock);
  $stock->each(function ($item,$key) {
    // dump($item->produits()->first());
    $GLOBALS['tab'][$key] =  [
      'item'  =>  $item->produits()->first()->libelle,
      'quantite'  =>  $item->quantite,
      'prix_ttc'  =>  $item->produits()->first()->prix_vente
    ];

  });
  return response()->json([
    'inventaire'  =>  $GLOBALS['tab'],
    'serials' =>  $serial
  ]);
}

// FAIRE L'INVENTAIRE D'UN Depots
public function inventaireDepot() {
  return view('gdepot.inventaire-depot');
}
// INVENTAIRE DES LIVRAISONS
public function inventaireLivraison() {
  return view('gdepot.all-livraison');
}
// liste des livraison non confirmee
  public function getListLivraison(Request $request,$status = 'unlivred') {
    $livraisons = Livraison::where([
      'depot' =>  Depots::where('vendeurs',Auth::user()->username)->first()->localisation,
      'status'  =>  $status
      ])->get();
    $all = [];
    $ids = [];
    foreach ($livraisons as $key => $value) {
      $date = new Carbon($value->created_at);
      $all[$key]  = [
        'date'  =>  $date->toFormattedDateString(),
        'vendeur' =>  $value->ravitaillementVendeurs()->vendeurs()->username.' _ '.$value->ravitaillementVendeurs()->vendeurs()->localisation,
        'item'  =>  $value->produits()->libelle,
        'commande'  =>  $value->ravitaillementVendeurs()->commands,
        'quantite'  =>   $value->quantite,
        'status'  =>  $value->status == 'unlivred'  ? "En attente de livraison" : "Livraison effectuee"
      ];
      $ids[$key]  = [
        'id'  =>  $value->id,
        'with_serial' =>  $value->produits()->with_serial
      ];
    }
    return response()->json([
      'list'  =>  $all,
      'ids' =>  $ids
    ]);
  }
  // list des livraison confirmee
  public function getListLivraisonConfirmee(Request $request , $status = 'unlivred') {
      return $this->getListLivraison($request,'livred');
  }
  // liste de livraison par vendeurs
  public function getListLivraisonByVendeurs(Request $request) {
    $ravitaillement = RavitaillementVendeur::select('id_ravitaillement')->where([
      'vendeurs'  =>  Auth::user()->username,
      'commands'  =>  $request->input('ref')
    ])->get();
    $livraison = Livraison::where('status','unlivred')->whereIn('ravitaillement',$ravitaillement)->get();
    $all = [];
    foreach ($livraison as $key => $value) {
      $all[$key]  = [
        'article' =>  $value->produits()->libelle,
        'quantite'  =>  $value->quantite,
        'status'  =>  $value->status == "unlivred" ? "En attente" : "Confirmer",
        'code_livraison'  =>  $value->code_livraison,
        'depot' =>  $value->depot
      ];
    }
    return response()->json($all);
  }

  public function confirmLivraison( Request $request) {
    $validation = $request->validate([
      'livraison' =>  'required|exists:livraisons,id',
      'with_serial' =>  'required|exists:produits,with_serial',
      'confirm_code'  =>  'required|string',
      'password'  =>  'required|string'
    ]);
    try {
      // verifier si le status est non Livrer
      if($this->livraisonStatus($request->input('livraison')) == 'unlivred') {
        // verifier si le mots de passe correspond
        if(Hash::check($request->input('password'),Auth::user()->password)) {
          // verifier si le code de confirmation est correcte
          if($this->confirmationCodeOk($request->input('livraison'),$request->input('confirm_code'))) {
            // Verifier si les numeros de series existes
            if($request->input('with_serial') == 1) {
              // Les Numeros de Series existes
              dd($request);
            } else {
              // Les Numeros de Series n'existes pas
              // Changement de status de livraison
              Livraison::where([
                'id'  =>  $request->input('livraison')
              ])->update([
                'status'  =>  'livred'
              ]);
              return redirect('/user/livraison')->withSuccess("Success!");
            }
          } else {
            throw new AppException("Le Code de confirmation est incorrect!",2);
          }
        } else {
          throw new AppException("Le Mot de passe ne corresponds pas , Ressayez !",1);
        }
      } else {
        throw new AppException("Erreur Deja Livrer",0);
      }

    } catch (AppException $e) {
      return back()->with("_errors",$e->getMessage());
    }

  }

// Verifier le status de la livraison (unlivred | livred)
  public function livraisonStatus($id) {
    return Livraison::where('id',$id)->first()->status;
  }
  // Verifier si le code de confirmation est correcte

  public function confirmationCodeOK($id,$code) {
    $livraison = Livraison::where([
      'id'  =>  $id,
      'code_livraison' =>  $code
    ])->first();
    if($livraison) {
      return $livraison;
    }
    return false;
  }

}
