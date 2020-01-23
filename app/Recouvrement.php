<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Recouvrement extends Model
{
    //

    protected $table = 'recouvrements';

    protected $keyType = 'string';
    protected $primaryKey = 'id';

    public function makeId() {
      do {
        $this->id = Str::random(10);
      } while ($this->existeInDB());
    }

    public function existeInDB() {
      $tmp = self::find($this->id);
      if($tmp) {
        return $tmp;
      }
      return false;
    }

    public function vendeurs() {
      return $this->belongsTo('App\User','vendeurs','username')->first();
    }
}
