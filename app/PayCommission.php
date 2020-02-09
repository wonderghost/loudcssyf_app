<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayCommission extends Model
{
    //
    protected $table = 'pay_comissions';
    protected $keyType = 'string';

    public function rapports() {
      return $this->hasMany('App\RapportVente','pay_comission_id','id')->orderBy('date_rapport','asc');
    }
}
