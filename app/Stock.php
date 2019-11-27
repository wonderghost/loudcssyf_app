<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $table = 'stock_central';

    public function exemplaire() {
      return  Exemplaire::where('serial_number',$this->exemplaire)->first();
    }
}
