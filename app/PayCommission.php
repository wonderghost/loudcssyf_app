<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayCommission extends Model
{
    //
    protected $table = 'pay_commissions';


    public function isExistPayment() {
      $temp = self::where([
        'vendeurs'  =>  $this->vendeurs,
        'status'  =>  'unvalidated'
      ])->first();
      if($temp) {
        return true;
      }
      return false;
    }

    public function vendeurs() {
      return $this->belongsTo('App\User','vendeurs','username')->first();
    }
}
