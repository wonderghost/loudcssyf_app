<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VenteGrandCompte;
use Carbon\Carbon;
class VenteGrandCompteController extends Controller
{
    /**
     * Listing des ventes grand compte
     */
    public function index()
    {
        try
        {
            $result = VenteGrandCompte::all();
            
            foreach($result as $value)
            {
                $date = new Carbon($value->created_at);
                $value->nom_complet = $value->abonne()->nom." ".$value->abonne()->prenom;
                $value->formule = $value->abonne()->formule;
                $value->duree = $value->abonne()->duree;
                $value->materiel = $value->abonne()->materiel;
                $value->date = $date->toDateString();
                $value->entreprise = $value->abonne()->entreprise()->entrepriseDetails()->nom;
                if(is_null($value->confirmed_at) && is_null($value->removed_at))
                {
                    $value->status = 'wait';
                }
                else if(!is_null($value->confirmed_at))
                {
                    $value->status = 'confirmed';
                }
                else 
                {
                    $value->status = 'removed';
                }
            }
            return response()->json($result,200);
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }
}
