<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    //
    protected $table = 'promos';
    
    public function remboursementPromo() {
        return $this->hasMany('App\RemboursementPromo','promo_id','id')->get();
    }
}
