<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IntervalProduit extends Model
{
    //
    protected $table = 'interval_produits';
    
    public function intervalData() {
        return $this->belongsTo('App\Interval','interval_id','id');
    }
}
