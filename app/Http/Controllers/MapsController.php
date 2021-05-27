<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Exceptions\AppException;

class MapsController extends Controller
{
    /**
     * Initialisation des donnees de la cartographie
     */
    public function index()
    {
        try
        {
            $users = User::select('localisation','lat','long','phone')
                ->whereIn('type',['pdraf','v_da','v_standart'])
                ->where('lat','<>',0)
                ->where('long','<>',0)
                ->get();
            return response()->json($users,200);
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }
}
