<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InterventionTechnicien extends Model
{
    //
    protected $table = 'intervention_techniciens';

    public function userTechnicien() {
        return $this->belongsTo('App\User','id_technicien','username')->first();
    }

    public function vendeur() {
        return $this->belongsTo('App\User','id_vendeur','username')->first();
    }

    public function recrutementAfrocash() {
        return $this->belongsTo('App\RecrutementAfrocash','id_recrutement_afrocash','id')->first();
    }

    public function pdrafUser() {
        return $this->recrutementAfrocash()
            ->pdrafUser();
    }
}
