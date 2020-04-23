<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    //
    protected $table = 'conversations';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
}
