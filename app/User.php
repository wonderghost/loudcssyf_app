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

    public function randomEmailGenerate() {
      do {  
        $this->email = Str::random(4)."@gmail.com";
      } while($this->isExistByEmail());
    }

    public function isExistByEmail() {
      $tmp = self::where("email",$this->email)->first();
      if($tmp) {
        return $tmp;
      }
      return false;
    }

    public function exemplaire () {
      return $this->hasMany('App\Exemplaire');
    }

    public function agence() {
      return $this->belongsTo('App\Agence','agence','reference')->first();
    }

    public function depot() {
      return $this->hasMany('App\Depots','vendeurs','username');
    }

    public function cgaAccount() {
      return  CgaAccount::where('vendeur',$this->username)->first();
    }

    public function afrocashAccount() {
      return $this->hasMany('App\Afrocash','vendeurs','username')->first();
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
      return $this->hasMany("App\RapportVente",'vendeurs','username')->where('state','unaborted')->whereNotNull('pay_comission_id')->get();
    }
    // rapport n'ayant pas recu une demande de paiement
    public function rapportsPayNUll() {
      return $this->hasMany("App\RapportVente",'vendeurs','username')->where('state','unaborted')->whereNull('pay_comission_id')->get();
    }

    public function rapportGroupByPayId() {
      return RapportVente::select('pay_comission_id')->whereNotNull('pay_comission_id')->where("vendeurs",$this->username)->groupBy('pay_comission_id')->get();
    }

    public function reaboGroupByPayId(Array $pdrafArray) {
      return ReaboAfrocash::select('pay_comission_id')
        ->whereNotNull('pay_comission_id')
        ->whereIn('pdraf_id',$pdrafArray)
        ->groupBy('pay_comission_id')
        ->get();
    }

    // details de l'objetif vendeurs
    
    public function objVendeur() {
      return $this->hasMany('App\ObjVendeur','vendeurs','username');
    }

    public function repertoire() {
      return $this->hasMany('App\Repertoire','vendeurs','username')->get();
    }

    // pdc -> pdraf

    public function pdrafUsers() {
      return $this->hasMany('App\ReseauxPdc','id_pdc','username')->get();
    }

    public function pdrafUsersForList() {
      return $this->hasMany('App\ReseauxPdc','id_pdc','username');
    }

    public function pdcUser() {
      return $this->hasOne('App\ReseauxPdc','id_pdraf','username')->first();
    }

    // all vente reabo afrocash

    public function reaboAfrocash() {
      return $this->hasMany('App\ReaboAfrocash','pdraf_id','username');
    }

    // ravitaillement vendeurs

    public function ravitaillementVendeur() {
      return $this->hasMany('App\RavitaillementVendeur','vendeurs','username');
    }

}
