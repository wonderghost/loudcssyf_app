<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetraitAfrocash extends Model
{
    //
    protected $table = 'retrait_afrocashes';

    public function initiateur() {
        return $this->belongsTo('App\Afrocash','initiateur','numero_compte')->first();
    }

    public function destinateur() {
        return $this->belongsTo('App\Afrocash','destinateur','numero_compte')->first();
    }

    public function comissionData() {
        return $this->belongsTo('App\ComissionSettingAfrocash','id_frais','id')->first();
    }
    
}
