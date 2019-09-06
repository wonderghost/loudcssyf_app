<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockPrime extends Model
{
    //
    protected $table = 'stock_central_prime';
    protected $keyType ='string';
    // protected $primaryKey = 'p'

    public function produits() {
      return $this->hasOne('App\Produits','reference','produit');
    }
}
