<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReseauxPdc extends Model
{
    //
    protected $table = 'reseaux_pdc';

    public function usersPdc() {
        return $this->belongsTo('App\User','id_pdc','username')->first();
    }

    public function usersPdraf() {
        return $this->belongsTo('App\User','id_pdraf','username')->first();
    }
}
