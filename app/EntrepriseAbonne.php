<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntrepriseAbonne extends Model
{
    protected $table = 'entreprise_abonne_gc';
    

    public function entrepriseDetails()
    {
        return $this->belongsTo('App\Entreprise','entreprise_id','id')->first();
    }
}
