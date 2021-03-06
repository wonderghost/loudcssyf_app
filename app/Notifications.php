<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Notifications extends Model
{
    //
    protected $table = 'notifications';

    protected $keyType = 'string';
    protected $primaryKey = 'id';

    public function makeId() {
      do {
        $this->id = Str::random(10).time();
      } while($this->isExistId());
    }

    public function isExistId() {
      $temp = self::where('id',$this->id)->first();
      if($temp) {
        return $temp;
      }
      else {
        return false;
      }
    }

    public function humanDate() {
      $_date = new Carbon($this->created_at);
      $_date->setLocale('fr_FR');
      $this->date = $_date->diffForHumans();
    }


}
