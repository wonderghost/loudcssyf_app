<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    //
    protected $table = 'options';
    protected $keyType = 'string';
    protected $primaryKey = 'nom';
}
