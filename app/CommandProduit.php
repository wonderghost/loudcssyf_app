<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommandProduit extends Model
{
    //
    protected $table = 'command_produits';

    protected $primaryKey = 'id';


    public function commande() {
      return $this->belongsTo('App\CommandMaterial');
    }

    public function produits() {
      return $this->belongsTo('App\Produits');
    }
}
