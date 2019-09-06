<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RavitaillementVendeur extends Model
{
    //
    protected $table = 'ravitaillement_vendeurs';

    protected $primaryKey = 'id_ravitaillement';
    protected $keyType = 'string';

    public function livraison() {
      return $this->hasMany('App\Livraison');
    }

    public function vendeurs() {
      return $this->belongsTo('App\User');
    }

    public function commands() {
      return $this->belongsTo('App\CommandMaterial');
    }
}
