<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepotAfrocash extends Model
{
    //
    protected $table = 'depot_afrocashes';

    public function comissionData() {
        return $this->belongsTo('App\ComissionSettingAfrocash','id_frais','id')->first();
    }

    public function destinateur() {
        return $this->belongsTo('App\Afrocash','destinateur','numero_compte')->first();
    }

    public function expediteur() {
        return $this->belongsTo('App\Afrocash','expediteur','numero_compte')->first();
    }
}
