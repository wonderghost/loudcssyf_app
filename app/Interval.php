<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Interval extends Model
{
    //
    protected $table = 'intervals';
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

    public function formule() {
        return $this->hasMany('App\FormuleInterval','id_interval','id');
    }

    public function produit() {
        return $this->hasMany('App\IntervalProduit','interval_id','id');
    }
}
