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
      return $this->belongsTo('App\User','vendeurs','username');
    }

    public function ravitaillements() {
      return $this->hasMany('App\RavitaillementVendeur','commands')->get();
    }

    public function commandProduits() {
      return $this->hasMany('App\CommandProduit','commande','id_commande');
    }
}
