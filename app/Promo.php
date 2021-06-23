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

    public function promoFormule()
    {
        $result =  $this->hasMany('App\PromoFormule','promo_id','id')->select('formule')->get();
        $arr = [];
        foreach($result as $key => $value)
        {
            array_push($arr,$value->formule);
        }
        return $arr;
    }
}
