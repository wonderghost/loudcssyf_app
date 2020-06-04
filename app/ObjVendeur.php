<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ObjVendeur extends Model
{
    //
    protected $table = 'obj_vendeurs';

    public function vendeurs() {
        return $this->belongsTo('App\User','vendeurs','username')->first();
    }

    public function objectif() {
        return $this->belongsTo('App\Objectif','id_objectif','id')->first();
    }
}
