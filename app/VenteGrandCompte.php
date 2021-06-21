<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class VenteGrandCompte extends Model
{
    protected $table = 'vente_grand_compte';


    /**
     * infos abonnes
     */
    public function abonne()
    {
        return $this->belongsTo('App\AbonneGrandCompte','id_abonne','id')->first();
    }
}
