<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    //
    protected $table = 'livraisons';

    protected $primaryKey = 'id';

    public function ravitaillementVendeurs() {
      return $this->belongsTo('App\RavitaillementVendeur');
    }

    public function produits() {
      return $this->belongsTo('App\Produits');
    }

    public function depot() {
      return $this->belongsTo('App\Depots');
    }
}
