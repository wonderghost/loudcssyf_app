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

    public function options() {
        return $this->hasMany('App\RecrutementAfrocashOption','id_recrutement_afrocash','id')->get();
    }

    public function pdrafUser() {
        return $this->belongsTo('App\User','pdraf_id','username')->first();
    }

    public function serialNumber() {
        $data = $this->hasOne('App\Exemplaire','recrutement_afrocash_id','id')->first() ? $this->hasOne('App\Exemplaire','recrutement_afrocash_id','id')->first()->serial_number : null; 
        return $data;
    }

    public function serialNumberData() {
        return $this->hasOne('App\Exemplaire','recrutement_afrocash_id','id')->first();
    }

    public function transactions() {
        return $this->hasMany('App\TransactionAfrocash','recrutement_afrocash_id','id');
    }

    public function interventionTechnicien() {
        return $this->hasOne('App\InterventionTechnicien','id_recrutement_afrocash','id')->first();
    }
}
