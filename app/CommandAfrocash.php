<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommandAfrocash extends Model
{
    //
    protected $table = 'command_afrocashes';
    protected $keyType = 'string';
    protected $primaryKey = 'id_commande';

    public function user_id() {
        return $this->belongsTo('App\User','user_id','username');
    }

    public function produit_id() {
        return $this->belongsTo('App\Produits','produit_id','reference');
    }

    public function livraison() {
        return $this->hasMany('App\LivraisonAfrocash','command_afrocash','id_commande');
    }
}
