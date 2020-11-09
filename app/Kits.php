<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kits extends Model
{
    //
    protected $table = 'kits';


    public function getTerminalReference() {
        $first = $this->belongsTo('App\Produits','first_reference','reference')
            ->where('with_serial',1)
            ->first();
        $second = $this->belongsTo('App\Produits','second_reference','reference')
            ->where('with_serial',1)
            ->first();
        $third = $this->belongsTo('App\Produits','third_reference','reference')
            ->where('with_serial',1)
            ->first();

        if($first) {
            return $first;
        }
        else if($second) {
            return $second;
        }
        else {
            return $third;
        }
    }

    public function getAccessoryReference() {
        $first = $this->belongsTo('App\Produits','first_reference','reference')
            ->where('with_serial',0)
            ->first();
        $second = $this->belongsTo('App\Produits','second_reference','reference')
            ->where('with_serial',0)
            ->first();
        $third = $this->belongsTo('App\Produits','third_reference','reference')
            ->where('with_serial',0)
            ->first();

        $accessory_data = [];

        if($first) {
            array_push($accessory_data,$first);
        }
        
        if($second) {
            array_push($accessory_data,$second);
        }

        if($third) {
            array_push($accessory_data,$third);
        }
        return $accessory_data;
    }
    
}
