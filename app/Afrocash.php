<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Afrocash extends Model
{
    //

    protected $table = 'afrocashes';

    protected $keyType = 'string';
    protected $primaryKey = 'numero_compte';

    public function vendeurs() {
      return $this->belongsTo('App\User','vendeurs','username')->first();
    }

    // DEBIT DE COMPTE AFROCASH
    public function debitAccountAfrocash($debitMontant) {
      $this->solde-=$debitMontant;
    }
    // CREDIT DE COMPTE AFROCASH
    public function creditAccountAfrocash($creditMontant) {
      $this->solde+=$creditMontant;
    }

    public function setSolde($value) {
      $this->solde = $value;
    }

    public function generateAccountNumber() {
      do {
        $this->numero_compte = mt_rand(000000000001,999999999999);
      }
      while(self::find($this->numero_compte));
    }

    public function retraitAfrocash() {
      return $this->hasMany('App\RetraitAfrocash','destinateur','numero_compte');
  }
}
