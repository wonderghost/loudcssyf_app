<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LivraisonAfrocash extends Model
{
    //
    protected $table = 'livraison_afrocashes';

    public function generateConfirmCode() {
        do {
            $this->confirm_code = mt_rand(100000,999999);
            $response = $this->where('confirm_code',$this->confirm_code)
                ->first();
        } while($response);
    }
}
