<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Afrocash extends Model
{
    //

    protected $table = 'afrocashes';

    protected $keyType = 'string';
    protected $primaryKey = 'numero_compte';
    
}
