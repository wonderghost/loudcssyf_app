<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    //

    protected $table = 'alertes';

    protected $keyType = 'string';
    protected $primaryKey = 'vendeurs';

    public function notifications() {
      return $this->belongsTo('App\Notifications','notification','id')->first();
    }

}
