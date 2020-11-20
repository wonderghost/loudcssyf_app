<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kits extends Model
{
    //
    protected $table = 'kits';
    protected $keyType = 'string';
    protected $primaryKey = 'slug';

    public function articles() {
        return $this->hasMany('App\Articles','kit_slug','slug');
    }

    public function getTerminalReference() {
        $data = $this->articles()->get();
        
        foreach($data as $value) {
            if($tmp = $value->produits()->where('with_serial',1)->first()) {
                $term = $tmp;
            }
        }

        return $term;
    }

    public function getAccessoryReference() {
        $data = $this->articles()->get();

        $accessory = [];

        foreach($data as $value) {
            if($tmp = $value->produits()->where('with_serial',0)->first()) {
                array_push($accessory,$tmp);
            }
        }

        return $accessory;
    }
    
}
