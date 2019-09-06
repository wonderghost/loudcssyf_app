<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommandMaterial extends Model
{
    //
    protected $table = 'command_material';

    protected $keyType = 'string';
    protected $primaryKey = 'id_commande';

    // RECUPERER LE VENDEUR QUI A EMIS LA COMMANDE
    public function vendeurs() {
      return $this->belongsTo('App\User');
    }

    public function ravitaillements() {
      return $this->hasMany('App\RavitaillementVendeur','commands')->get();
    }

}
