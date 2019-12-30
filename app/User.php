<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use Notifiable;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function agence() {
      return $this->belongsTo('App\Agence','agence','reference')->first();
    }

    public function depot() {
      return $this->hasOne('App\Depots','vendeurs','username');
    }

    public function cgaAccount() {
      return  CgaAccount::where('vendeur',$this->username)->first();
    }

    public function afroCash($type = 'courant') {
      return $this->hasOne('App\Afrocash','vendeurs','username')->where("type",$type);
    }

    public function rexAccount() {
      return RexAccount::where('numero',$this->rex);
    }
}
