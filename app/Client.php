<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    //
    protected $table = 'clients';

    public function makeClientId() {
        do {
            $this->client_slug =  'client_'.Str::random(5).'_'.time();
          } while ($this->isExistClientById());
    }

    public function isExistClientById() {
      $temp = self::where('client_slug',$this->client_slug)->first();
      if($temp) {
        return $temp;
      }
      return false;
    }

    public function materiel() {
      return $this->hasMany('App\Exemplaire','client_id','client_slug')->get();
    }
}
