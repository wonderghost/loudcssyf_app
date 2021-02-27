<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use App\Ventes;
use App\Credit;
use App\Exemplaire;
use App\TransactionAfrocash;
use Illuminate\Support\Facades\Hash;

class ReabonnementController extends Controller
{
    /**
     * Enregistrement d'un nouveau reabonnement
     */
    public function create() {
        try {
            $validator = request()->validate([
                'materiel'  =>  'required|string|min:14|max:14',
                'prenom'    =>  'required|string',
                'nom'   =>  'required|string',
                'formule'   =>  'required|string',
                'duree' =>  'required|min:1|max:24',
                'telephone' =>  'required|string|max:9|min:9',
                'password'  =>  'required|string'
            ],[
                'required'  =>  '`:attribute` requis.',
                'duree.min'   =>  '`:attribute` minimum requis : 1',
                'duree.max'   =>  '`:attribute` maximum requis : 24',
                'materiel.max'   =>  '14 chiffres requis pour le Numero materiel',
                'materiel.min'   =>  '14 chiffres requis pour le Numero materiel',
                'telephone.min'   =>  '9 chiffres requis pour le Numero de telephone',
                'telephone.max'   =>  '9 chiffres requis pour le Numero de telephone',
            ]);

            // VALIDATION DU MOT DE PASSE

            if(!Hash::check(request()->password,request()->user()->password)) {
                throw new AppException("Mot de invalide.");
            }

            $materiel = Exemplaire::find(request()->materiel);

            if($materiel && is_null($materiel->rapports)) {
                throw new AppException("Materiel vierge.");
            }

            $vente = new Ventes;
            $vente->nom = request()->nom;
            $vente->prenom = request()->prenom;
            $vente->quartier = "";
            $vente->materiel = request()->materiel;
            $vente->formule = request()->formule;
            $vente->duree = request()->duree;
            $vente->telephone = request()->telephone;
            $vente->montant = request()->montant;
            $vente->id_user = request()->user()->username;
            $vente->type = 'reabonnement';
            $vente->option = "";

            foreach(request()->options as $value) {
                $vente->option .= $value."-";
            }

            $userAfrocash = request()->user()->afroCash()->first();
            $centralAfrocash = Credit::find('afrocash');

            $centralAfrocash->solde -= request()->montant;
            $userAfrocash->solde += request()->montant;

            $trans = new TransactionAfrocash;
            $trans->compte_credite = $userAfrocash->numero_compte;
            $trans->montant = request()->montant;
            $trans->motif = "Vente_Reabonnement";

            if($vente->save()) {
                if($trans->save()) {
                    if($userAfrocash->update() && $centralAfrocash->update()) {
                        return response()->json('done');
                    }
                }
            }

            throw new AppException("Erreur de traitement,ressayez.");
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
