<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockVendeur extends Model
{
    //
    protected $table = 'stock_vendeur';
    protected $keyType = 'string';

    public function produit() {
      return $this->belongsTo('App\Produits','produit','reference')->first();
    }
}
