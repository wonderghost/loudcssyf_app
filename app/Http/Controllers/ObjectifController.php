<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Exceptions\AppException;
use App\Objectif;

class ObjectifController extends Controller
{
    //
    
    public function firstStepValidation(Request $request , Objectif $obj) {
        try {
            $validation = $request->validate([
                'objectif_name' =>  'required|string',
                'debut' =>  'required|date',
                'fin'   =>  'required|date',
                'evaluation'    =>  'required|numeric'
            ],[
                'required'  =>  'Champ(s) :attribute requis',
                'numeric'   =>  'Champ(s) :attribute doit un nombre'
            ]);

            

            return response()
                ->json($request);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
