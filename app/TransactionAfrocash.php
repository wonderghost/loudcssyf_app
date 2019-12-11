<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionAfrocash extends Model
{
    //
    protected $table = 'transaction_afrocashes';

    protected $primaryKey = 'code_transaction';
}
