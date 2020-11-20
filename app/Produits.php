<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produits extends Model
{
  //
  protected $table = 'produits';

  protected $keyType = 'string';
  protected $primaryKey = 'reference';

  public function exemplaire() {
    return $this->hasMany('App\Produits');
  }

  public function articles() {
    return $this->hasMany('App\Articles','produit','reference');
  }
    
}
