<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RecrutementAfrocash extends Model
{
    //
    protected $table = 'recrutement_afrocashes';
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    public function generateId() {
        do {
            $this->id = Str::random(10).'_'.time();
        } while($this->isExistId());
    }

    public function isExistId() {
        $tmp = self::where('id',$this->id)->first();
        if($tmp) {
            return $tmp;
        }
        return false;
    }
}
