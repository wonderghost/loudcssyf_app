<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RexAccount extends Model
{
    //
    protected $table = 'compte_rex';

    public function setSolde($value) {
      $this->solde = $value;
    }
}
