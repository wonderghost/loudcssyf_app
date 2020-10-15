<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReseauxPdc extends Model
{
    //
    protected $table = 'reseaux_pdc';
    protected $primaryKey=['id_pdc','id_pdraf'];
    public $incrementing = false;


    public function usersPdc() {
        return $this->belongsTo('App\User','id_pdc','username')->first();
    }

    public function usersPdraf() {
        return $this->belongsTo('App\User','id_pdraf','username')->first();
    }
}
