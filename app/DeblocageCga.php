<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeblocageCga extends Model
{
    //
    protected $table = 'deblocage_cga';
    

    public function vendeurs() {
        return $this->belongsTo('App\User','vendeurs','username')->first();
    }
}
