<?php
namespace App\Traits;

use Illuminate\Http\Request;

use App\StockPrime;
use App\Stock;

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
}
