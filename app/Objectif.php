<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Objectif extends Model
{
    //
    protected $table = 'objectifs';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    public function makeId() {
        do {
            $this->id = Str::random(64);
        } while($this->isExistId());
    }

    public function isExistId() {
        $result = self::where('id',$this->id)->first();
        if($result) {
            return $result;
        }

        return false;
    }
    
}
