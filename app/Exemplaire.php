<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exemplaire extends Model
{
    //
    protected $table = 'exemplaire';
    protected $keyType = 'string';
    protected $primaryKey = 'serial_number';

    protected $fillable = ['vendeurs'];

    public function vendeurs() {
      return $this->belongsTo('App\User','vendeurs','username')->first();
    }

    public function produit() {
      return $this->belongsTo('App\Produits','produit','reference')->first();
    }

    public function depot() {
      return $this->hasMany('App\Stock','exemplaire','serial_number')->first();
    }

    public function deficientMaterial() {
      return $this->hasMany('App\DeficientMaterial','serial_to_replace','serial_number')->first();
    }

    public function rapport() {
      return $this->belongsTo('App\RapportVente','rapports','id_rapport')->first();
    }

    public function abonnements() {
      return $this->hasMany('App\Abonnement','serial_number','serial_number')
        ->orderBy('created_at','desc')
        ->get();
    }

}
