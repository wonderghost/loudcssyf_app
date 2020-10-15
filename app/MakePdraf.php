<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MakePdraf extends Model
{
    //
    protected $table = 'make_pdraf';

    
    public function pdcUser() {
        return $this->belongsTo('App\User','pdc_user_id','username')->first()->only('localisation','username');
    }
}
