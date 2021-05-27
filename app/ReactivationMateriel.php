<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReactivationMateriel extends Model
{
    //

    protected $table = 'reactivation_materiels';


    public function pdrafUser() {
        return $this->belongsTo('App\User','pdraf_id','username')->first();
    }
}
