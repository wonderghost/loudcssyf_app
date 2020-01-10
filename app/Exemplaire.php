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

    public function produits() {

    }

    public function depot() {
      return $this->hasMany('App\Stock','exemplaire','serial_number')->first();
    }
}
