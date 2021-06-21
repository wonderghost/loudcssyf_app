<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AbonneGrandCompte extends Model
{
    //
    protected $table = 'abonne_grand_compte';
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    public function entreprise()
    {
        return $this->hasOne('App\EntrepriseAbonne','abonne_id','id')->first();
    }
}
