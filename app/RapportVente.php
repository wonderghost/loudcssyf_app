<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
