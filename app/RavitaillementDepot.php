<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RavitaillementDepot extends Model
{
    //
    protected $table = 'ravitaillement_depots';


    public function produit() {
        return $this->belongsTo('App\Produits','produit','reference')->first();
    }
}
