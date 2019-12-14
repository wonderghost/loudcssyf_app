<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionAfrocash extends Model
{
    //
    protected $table = 'transaction_afrocashes';

    protected $primaryKey = 'code_transaction';

    public function afrocash() {
      return $this->belongsTo('App\Afrocash','compte_debite','numero_compte')->first();
    }

    public function afrocashcredite() {
      return $this->belongsTo('App\Afrocash','compte_credite','numero_compte')->first();
    }
}
