<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReaboAfrocash extends Model
{
    //
    protected $table = 'reabo_afrocash';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    
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
