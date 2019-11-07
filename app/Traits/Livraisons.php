<?php
namespace App\Traits;

use Illuminate\Http\Request;

use App\StockPrime;
use App\Stock;
use App\Livraison;
use App\Depots;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
// liste des livraison
  public function getListLivraison(Request $request) {
    $livraisons = Livraison::where('depot',Depots::where('vendeurs',Auth::user()->username)->first()->localisation)->get();
    $all = [];
    foreach ($livraisons as $key => $value) {
      $date = new Carbon($value->created_at);
      $all[$key]  = [
        'date'  =>  $date->toFormattedDateString(),
        'vendeur' =>  $value->ravitaillementVendeurs()->vendeurs()->username.' _ '.$value->ravitaillementVendeurs()->vendeurs()->localisation,
        'item'  =>  $value->produits()->libelle,
        'quantite'  =>   $value->quantite,
        'status'  =>  $value->status == 'unlivred'  ? "En attente de livraison" : "Livraison effectuee"
      ];
    }
    return response()->json($all);
  }

}
