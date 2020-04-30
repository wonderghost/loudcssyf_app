<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Depots extends Model
{
    //

    protected $table = 'depots';
    protected $keyType = 'string';
    protected $primaryKey = 'localisation';

    public function gestionnaire() {
      return $this->belongsTo('App\User','vendeurs','username');
    }

    public function stockMateriel() {
      return $this->hasMany('App\StockPrime','depot','localisation');
    }


}
