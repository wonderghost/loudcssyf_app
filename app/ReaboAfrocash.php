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

    public function pdrafUser() {
        return $this->belongsTo('App\User','pdraf_id','username')->first();
    }

    public function options() {
        return $this->hasMany('App\OptionReaboAfrocash','id_reabo_afrocash','id')->get();
    }

    public function upgrade() {
        return $this->hasMany('App\UpgradeReaboAfrocash','id_reabo_afrocash','id');
    }

}
