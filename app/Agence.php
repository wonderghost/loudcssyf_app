<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    //
    protected $table = 'agence';
    protected $keyType = 'string';
    protected $primaryKey = 'reference';
}
