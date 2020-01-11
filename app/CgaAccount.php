<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CgaAccount extends Model
{
    //
    protected $table = 'compte_cga';

    protected $keyType = 'string';
    protected $primaryKey = 'numero';

    public function setSolde($value) {
  		$this->solde = $value;
  	}
}
