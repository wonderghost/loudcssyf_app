<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RapportPromo extends Model
{
    //

    protected $table = 'rapport_promos';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    public function isExistId() {
        $tmp = self::where('id',$this->id)->first();
        if($tmp) {
            return true;
        }
        return false;
    }
}
