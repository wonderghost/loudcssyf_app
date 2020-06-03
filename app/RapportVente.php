<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RapportVente extends Model
{
    //
    protected $table = "rapport_vente";

    protected $keyType = 'string';
    protected $primaryKey = 'id_rapport';

    public function isExistRapportById() {
      $temp = RapportVente::where('id_rapport',$this->id_rapport)->first();
      if($temp) {
        return $temp;
      }
      return false;
    }

    public function makeRapportId() {
      do {
        $this->id_rapport =  Str::random(10).'_'.time();
      } while ($this->isExistRapportById());
    }

    public function calculCommission($type = 'recrutement') {

      if($type == 'recrutement') {

        $this->commission = ($this->montant_ttc / 1.18) * (12 / 100);

      } elseif ($type == 'reabonnement') {

        $this->commission = ($this->montant_ttc / 1.18) * (5.5 / 100);

      }
      else {

        $this->commission = 0;

      }
      $this->commission = round($this->commission);

    }
// @@@@@@@@@@@@@@@@@@@
    public function vendeurs() {
      return  $this->belongsTo('App\User','vendeurs','username')->first();
    }

    public function changeStatePayComission() {
      $this->statut_paiement_commission = 'paye';
    }

    public function exemplaire() {
      return $this->hasMany('App\Exemplaire','rapports','id_rapport')->get();
    }
    #@ tous les abonnement lies au rapports

    public function abonnements() {
      return $this->hasMany('App\Abonnement','rapport_id','id_rapport')
        ->orderBy('created_at','desc')
        ->get();
    }
}
