<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $table = 'stock_central';

    public function exemplaire() {
        return $this->belongsTo('App\Exemplaire','exemplaire','serial_number')->first();
    }
    
}
