<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockPrime extends Model
{
    //
    protected $table = 'stock_central_prime';
    protected $keyType ='string';


    public function produits() {
      return $this->belongsTo('App\Produits','produit','reference');
    }
}
