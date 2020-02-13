<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
class User extends Authenticatable
{
    use Notifiable;

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

    public function exemplaire () {
      return $this->hasMany('App\Exemplaire');
    }

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

    public function rapports() {
      return $this->hasMany("App\RapportVente",'vendeurs','username')->get();
    }

    // rapport ayant deja recu une demande de paiement
    public function rapportPayNotNull() {
      return $this->hasMany("App\RapportVente",'vendeurs','username')->whereNotNull('pay_comission_id')->get();
    }
    // rapport n'ayant pas recu une demande de paiement
    public function rapportsPayNUll() {
      return $this->hasMany("App\RapportVente",'vendeurs','username')->whereNull('pay_comission_id')->get();
    }

    public function rapportGroupByPayId() {
      return RapportVente::select('pay_comission_id')->whereNotNull('pay_comission_id')->where("vendeurs",$this->username)->groupBy('pay_comission_id')->get();
    }
}
