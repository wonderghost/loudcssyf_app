<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommandCredit extends Model
{
    //
    protected $table = 'command_credits';

    protected $primaryKey = 'id';

    public function vendeurs() {
      return $this->belongsTo('App\User','vendeurs','username')->first();
    }
}
