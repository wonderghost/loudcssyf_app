<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exemplaire extends Model
{
    //
    protected $table = 'exemplaire';
    protected $keyType = 'string';
    protected $primaryKey = 'serial_number';

    protected $fillable = ['vendeurs'];

    public function vendeurs() {
      return $this->hasOne('App\User','username','vendeurs');
    }
}
