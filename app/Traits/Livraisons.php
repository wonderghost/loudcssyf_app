<?php
namespace App\Traits;

use Illuminate\Http\Request;
use App\StockPrime;
use App\Stock;
use App\Livraison;
use App\Depots;
use App\RavitaillementVendeur;
use App\Produits;
use App\Exemplaire;
use App\LivraisonSerialFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use App\Exceptions\AppException;
use App\User;
use App\DeficientMaterial;

trait Livraisons {

  // RECUPERATION DE L'INVENTAIRE DU DEPOT
public function getInventaireForDepot(Request $request) {
  // global $tab =  [];
  $GLOBALS['tab'] = [];
  $GLOBALS['serials'] = [];

  $stock  = StockPrime::where('depot',$request->input('ref'))->get();
  $serial   = Stock::where('depot',$request->input('ref'))->get();
  // dump($stock);
  $stock->each(function ($item,$key) {
    // dump($item->produits()->first());
    $GLOBALS['tab'][$key] =  [
      'item'  =>  $item->produits()->first()->libelle,
      'quantite'  =>  $item->quantite,
      'prix_initial'  =>  number_format($item->produits()->first()->prix_initial,0,'',' '),
      'prix_ttc'  =>  number_format($item->produits()->first()->prix_vente,0,'',' '),
      'ht'  =>  0,
      'tva' =>  0,
      'marge' =>  0
    ];

  });
  $serial->each(function ($item , $key) {
    $GLOBALS['serials'][$key] = [
      'serial'  =>  $item->exemplaire,
      'vendeurs'  =>  $item->exemplaire()->vendeurs,
      'status'  =>  $item->exemplaire()->status
    ];
  });

  return response()->json([
    'inventaire'  =>  $GLOBALS['tab'],
    'serials' =>  $GLOBALS['serials']
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

  public function confirmLivraison( Request $request , DeficientMaterial $df) {
    try {

      $validation = $request->validate([
        'livraison' =>  'required|exists:livraisons,id',
        'with_serial' =>  'required|exists:produits,with_serial',
        'confirm_code'  =>  'required|string',
        'password'  =>  'required|string',
        'serial_number.*' =>  'string|distinct|exists:exemplaire,serial_number'
      ],[
        'distinct' => 'Vous ne pouvez envoyer le meme numero de serie plus d\'une fois :-(',
        'exists'  =>  ':attribute est Inexistant :-('
      ]);
      
      // verifier si le materiel est defectueux ou pas!

      foreach($request->input('serial_number') as $value) {
        $mat = $df->where('serial_to_replace',$value)->first();
        if($mat) {
          throw new AppException("le materiel est defectueux :".$value);
        }
      }

      // verifier si le status est non Livrer
      if($this->livraisonStatus($request->input('livraison')) == 'unlivred') {
        // verifier si le mots de passe correspond
        if(Hash::check($request->input('password'),Auth::user()->password)) {
          // verifier si le code de confirmation est correcte
          if($this->confirmationCodeOk($request->input('livraison'),$request->input('confirm_code'))) {
            // Verifier si les numeros de series existes
            if($request->input('with_serial') == 1) {
              // Les Numeros de Series existes
              // verifier que les champs ne sont pas vides !
              if(count($request->input('serial_number')) <= 0) {
                throw new AppException("Remplissez les champs vides !");
              }

              foreach($request->input('serial_number') as $value) {
                if(trim($value) == "") {
                  throw new AppException("Remplissez les champs vides !");
                }
              }
              // ecriture dans un fichier texte et stockage du dit fichier
              $fileSerial = new LivraisonSerialFile;
              $fileSerial->filename = 'serial_file_'.time().'.txt';
              $fileSerial->livraison_id = $request->input('livraison');
              $file = config('serial_file.path')."/".$fileSerial->filename;

              $handle = fopen($file,'w');
              for ($i=0; $i < $request->input('quantite') ; $i++) {
                fputs($handle,$request->input('serial_number')[$i]."\n");
              }
              fclose($handle);
              $fileSerial->save();
            } else {
              // Les Numeros de Series n'existes pas
              // VALIDATION DANS LE CAS DE LA PARABOLE
              $_livraison = Livraison::find($request->input('livraison'));
              $ravit = $_livraison->ravitaillementVendeurs();
              $ravit->livraison = 'confirmer';
              $ravit->save();
            }
            $livraison = Livraison::find($request->input('livraison'));
            // Changement de status de livraison
            Livraison::where([
              'id'  =>  $request->input('livraison')
              ])->update([
                'status'  =>  'livred'
              ]);
              $this->sendNotification("Livraison Materiel" ,"Livraison effectuee pour : ".$livraison->ravitaillementVendeurs()->vendeurs()->localisation." de la part du depot de : ".$livraison->depot,User::where('type','admin')->first()->username);
              // logistique
              $this->sendNotification("Validation de la livraison" ,"Vous avez une livraison en attente de validation ",User::where('type','logistique')->first()->username);
              // vendeurs
              $this->sendNotification("Livraison Materiel" ,"Vous avez recu une livraison de : ".$livraison->depot,$livraison->ravitaillementVendeurs()->vendeurs);
              // depot de livraison
              $this->sendNotification("Livraison Materiel" ,"Vous avez effectue une livraison au compte de : ".$livraison->ravitaillementVendeurs()->vendeurs()->localisation,Auth::user()->username);

              return response()
                ->json('done');
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
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
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
// recuperation de la liste des livraisons a valider
  public function getListLivraisonToValidate(Request $request) {
    $livraison = $this->livraisonStateRequest('unvalidate');
    return $this->organizeLivraison($livraison);
  }

  public function organizeLivraison($livraison) {
    $all=[];
    foreach ($livraison as $key => $value) {
      $date = new Carbon($value->created_at);

      $all[$key] = [
        'date'  =>  $date->toFormattedDateString(),
        'vendeur' => $value->ravitaillementVendeurs()->vendeurs()->localisation,
        'produit' =>  $value->produits()->libelle,
        'command' =>  $value->ravitaillementVendeurs()->commands,
        'quantite'  =>  $value->quantite,
        'status'  =>  $value->status,
        'validation'  =>  $value->ravitaillementVendeurs()->livraison,
        'depot' =>  $value->depot()->first()->localisation,
        'filename'  =>  $value->serialFile() !== 'undefined' ? url('livraison_serial_files').'/'.$value->serialFile() : 'undefined',
        'id'  =>  $value->id,
        'code_livraison'  =>  $value->code_livraison,
        'with_serial' =>  $value->produits()->with_serial
      ];
    }
    return response()->json([
      'all' =>  $all,
      'next_url'	=> $livraison->nextPageUrl(),
      'last_url'	=> $livraison->previousPageUrl(),
      'per_page'	=>	$livraison->perPage(),
      'current_page'	=>	$livraison->currentPage(),
      'first_page'	=>	$livraison->url(1),
      'first_item'	=>	$livraison->firstItem(),
      'total'	=>	$livraison->total()
    ]);
  }

  public function getListLivraisonValidee(Request $request) {
    $livraison = $this->livraisonStateRequest();
    return $this->organizeLivraison($livraison);
  }

  // Livraison state request
  public function livraisonStateRequest ($state = "validate") {
    if($state == "validate") {

    return Livraison::where('status','livred')
      ->whereIn('produits',Produits::select('reference')
      ->where('with_serial',1)->get())
      ->whereIn('ravitaillement',RavitaillementVendeur::select('id_ravitaillement')->where('livraison','confirmer')->get())->get();
    } else {
      return Livraison::where('status','livred')
        ->whereIn('produits',Produits::select('reference')
        ->where('with_serial',1)->get())
        ->whereIn('ravitaillement',RavitaillementVendeur::select('id_ravitaillement')->where('livraison','non_confirmer')->get())->get();
    }
  }

  public function livraisonRequest(Livraison $l , Request $request) {
    $user = $request->user();
    if($user->type != 'v_standart' && $user->type != 'v_da') {
      
      return $l->whereIn('produits',Produits::select('reference')
        ->get())
        ->orderBy('created_at','desc')
        ->paginate(500);
    }

    $ravitaillement = $user->ravitaillementVendeur()
      ->select('id_ravitaillement')
      ->groupBy('id_ravitaillement')
      ->get();
    
    return $l->whereIn('produits',Produits::select('reference')
      ->get())
      ->whereIn('ravitaillement',$ravitaillement)
      ->orderBy('created_at','desc')
      ->paginate(500);
              
  }

  public function getSerialInFileText($filename) {
    $tabSerial = [];
    $handle = fopen($filename,"r");
    if($handle) {
      while(!feof($handle)) {
        $serial = fgets($handle);
        $serial = str_replace("\n","",$serial);
        array_push($tabSerial,$serial);
      }
      fclose($handle);
    }
    return $tabSerial;
  }

  public function getSerialForValidateLivraison(Request $request) {
    $file = LivraisonSerialFile::where('livraison_id',$request->input('ref'))->first()->filename;
    // ouverture du fichier en lecture seul
    $tabSerial = [];

    $tabSerial  = $this->getSerialInFileText(config('serial_file.path')."/".$file);

    // filtrer le tableau en supprimant les valeurs vides
    $filtered = Arr::where($tabSerial, function ($value , $key ) {
      return !empty($value);
    });

    foreach ($filtered as $key => $value) {
      $tabSerial[$key] = [
        'serial'  =>  $value
      ];
    }

    return response()->json($tabSerial);
  }

  public function validateLivraisonSerial(Request $request) {
    $validation = $request->validate([
      'livraison' =>  'required|numeric|exists:livraisons,id',
      'password_confirmation' =>  'required|string'
    ]);
    try {
      // verifier la validite du mot de passe pour la confirmation
      if(Hash::check($request->input('password_confirmation'),$request->user()->password)) {
        // Le mot de passe correspond
        #recuperation des numeros de serie
        $serials = [];
        $filename = LivraisonSerialFile::where('livraison_id',$request->input('livraison'))->first()->filename;
        $serials =  $this->getSerialInFileText(config('serial_file.path')."/".$filename);
        $serials = Arr::where($serials , function ($value, $key) {
          return !empty($value);
        });
        $livraison = Livraison::where("id",$request->input('livraison'))->first();
        $ravitaillementVendeur = $livraison->ravitaillementVendeurs();
        #attribution des numeros de series au vendeur

        foreach ($serials as $key => $value) {
          Exemplaire::where('serial_number',$value)->update([
            'vendeurs'  =>  $ravitaillementVendeur->vendeurs
          ]);
        }
        #changement de status de livraison dans la table ravitaillement
        RavitaillementVendeur::where('id_ravitaillement',$ravitaillementVendeur->id_ravitaillement)->update([
          'livraison' =>  'confirmer'
        ]);
        //
        // ENREGISTREMENT DE LA NOTIFICATION
        // Logistique
        $this->sendNotification("Validation de Livraison","Vous avez valide une livraison pour : ".$ravitaillementVendeur->vendeurs()->localisation,User::where('type','logistique')->first()->username);
        // Vendeurs
        $this->sendNotification("Validation de Livraison","Votre livraison a ete valide , les materiels ont ete transfere dans votre pack!",$ravitaillementVendeur->vendeurs);
        // depot concerne
        $this->sendNotification("Validation de Livraison","Livraison validee pour :".$ravitaillementVendeur->vendeurs()->localisation,$livraison->depot()->first()->vendeurs);
        // //
        return response()
          ->json('done');
      } else {
        // Le mot de passe ne correspond pas
        throw new AppException("Mot de passe incorrect!");
      }
    } catch (AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }
}
