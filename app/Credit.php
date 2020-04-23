<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    //
    protected $table = 'credit';

    protected $keyType = 'string';
    protected $primaryKey = 'designation';

    public function setSolde($value) {
      $this->solde = $value;
    }
}
