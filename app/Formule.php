<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Formule extends Model
{
    //
    protected $table = 'formule';
    protected $keyType = 'string';
    protected $primaryKey = 'nom';
}
