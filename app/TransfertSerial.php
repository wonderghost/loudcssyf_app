<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransfertSerial extends Model
{
    //
    protected $table = 'transfert_serial';
    protected $fillable = ['id_transfert','serial'];
}
