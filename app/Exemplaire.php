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

}
