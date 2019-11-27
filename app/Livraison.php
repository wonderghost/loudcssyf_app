<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    //
    protected $table = 'livraisons';

    protected $primaryKey = 'id';

    public function ravitaillementVendeurs() {
      return $this->belongsTo('App\RavitaillementVendeur','ravitaillement','id_ravitaillement')->first();
    }

    public function produits() {
      return $this->belongsTo('App\Produits','produits','reference')->first();
    }

    public function depot() {
      return $this->belongsTo('App\Depots');
    }

    public function serialFile() {
      return LivraisonSerialFile::where('livraison_id',$this->id)->first()->filename;
    }
}
