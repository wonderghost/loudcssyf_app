<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repertoire extends Model
{
    //
    protected $table = 'repertoire';

    public function clients() {
        return $this->belongsTo('App\Client','id_clients','client_slug')->first();
    }
}
