<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use App\User;

class TechniqueController extends Controller
{
    //

    public function getListTechnicien() {
        try {
            $techniciens = User::where('type','technicien')
                ->where('status','unblocked')
                ->get();

            return response()
                ->json($techniciens);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
