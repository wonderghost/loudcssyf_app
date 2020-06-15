<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Abonnement extends Model
{
    //
    protected $table = 'abonnements';
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    public function isExistAbonnementById() {
        $temp = self::where('id',$this->id)->first();
        if($temp) {
          return $temp;
        }
        return false;
    }

    public function makeAbonnementId() {
        do {
          $this->id =  Str::random(15).'_'.time();
        } while ($this->isExistAbonnementById());
    }

    public function rapportVente() {
      return $this->belongsTo('App\RapportVente','rapport_id','id_rapport')->first();
    }

    public function options() {
      return $this->hasMany('App\AbonneOption','id_abonnement','id')->get();
    }

    public function isExistAbonnementForDebutDate() {
      $tmp = self::where('serial_number',$this->serial_number)
        ->whereDate('debut',$this->debut)
        ->first();
      
      if($tmp) {
        return $tmp;
      }
      return false;
    }

    public function formule() {
      return $this->belongsTo('App\Formule','formule_name','nom')->first();
    }

    public function upgrade() {
      return $this->hasMany('App\Upgrade','id_abonnement','id')->first();
    }

}
