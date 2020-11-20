<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Articles extends Model
{
    //
    protected $table = 'articles';

    public function kits() {
        return $this->belongsTo('App\Kits','kit_slug','slug');
    }

    public function produits() {
        return $this->belongsTo('App\Produits','produit','reference');
    }
}
