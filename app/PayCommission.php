<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayCommission extends Model
{
    //
    protected $table = 'pay_commissions';


    public function isExistPayment() {
      $temp = self::where([
        'debut' =>  $this->debut,
        'fin' =>  $this->fin,
        'vendeurs'  =>  $this->vendeurs
      ])->first();
      if($temp) {
        return true;
      }
      return false;
    }
}
