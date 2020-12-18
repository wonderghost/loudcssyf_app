<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StockPrime extends Model
{
    //
    protected $table = 'stock_central_prime';
    protected $keyType ='string';
    protected $primaryKey = ['produit','depot'];

    public $incrementing = false;


    public function produits() {
      return $this->belongsTo('App\Produits','produit','reference');
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
