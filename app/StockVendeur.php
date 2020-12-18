<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class StockVendeur extends Model
{
    //
    protected $table = 'stock_vendeur';
    protected $keyType = 'string';
    protected $primaryKey = ['vendeurs','produit'];
    public $incrementing = false;

    public function produit() {
      return $this->belongsTo('App\Produits','produit','reference')->first();
    }

    protected function setKeysForSaveQuery(Builder $query){
      $keys = $this->getKeyName();
      if(!is_array($keys)){
          return parent::setKeysForSaveQuery($query);
      }

      foreach($keys as $keyName){
          $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
      }

      return $query;
    }

    protected function getKeyForSaveQuery($keyName = null){
      if(is_null($keyName)){
          $keyName = $this->getKeyName();
      }

      if (isset($this->original[$keyName])) {
          return $this->original[$keyName];
      }

      return $this->getAttribute($keyName);
    }    
}
