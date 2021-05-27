<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ventes extends Model
{
    //
    protected $table = 'ventes';
    

    /**
     * Trouver l'utilisateur associe a la vente
     */
    public function user() {
        return $this->belongsTo('App\User','id_user','username')->first();
    }
}
