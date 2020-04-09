<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RemboursementPromo extends Model
{
    //
    protected $table = 'remboursement_promo';
    
    public function promos() {
        return $this->belongsTo('App\Promo','promo_id','id')->first();
    }
}
