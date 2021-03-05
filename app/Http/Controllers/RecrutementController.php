<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Hash;
use App\Exemplaire;
use App\Ventes;
use App\Credit;
use App\TransactionAfrocash;
use App\Traits\SendSms;

class RecrutementController extends Controller
{
    use SendSms;

    public function getFormule(Request $request) {
    	return response()->json();
    }

    /**
     * Creation d'un recrutement
     */
    public function create() {
        try {
            $validator = request()->validate([
                'materiel'  =>  'required|string|exists:exemplaire,serial_number|unique:ventes,materiel|min:14|max:14',
                'prenom'    =>  'required|string',
                'nom'   =>  'required|string',
                'formule'   =>  'required|string',
                'duree' =>  'required|min:1|max:24',
                'telephone' =>  'required|string|max:9|min:9',
                'password'  =>  'required|string'
            ],[
                'required'  =>  '`:attribute` requis.',
                'exists'    =>  '`:attribute` inexistant.',
                'duree.min'   =>  '`:attribute` minimum requis : 1',
                'duree.max'   =>  '`:attribute` maximum requis : 24',
                'materiel.max'   =>  '14 chiffres requis pour le Numero materiel',
                'materiel.min'   =>  '14 chiffres requis pour le Numero materiel',
                'telephone.min'   =>  '9 chiffres requis pour le Numero de telephone',
                'telephone.max'   =>  '9 chiffres requis pour le Numero de telephone',
                'unique'    =>  '`:attribute` deja actif.'
            ]);

            // VALIDATION DU MOT DE PASSE

            if(!Hash::check(request()->password,request()->user()->password)) {
                throw new AppException("Mot de invalide.");
            }

            // verifier si le materiel est inactif

            $materiel = Exemplaire::find(request()->materiel);

            if(!is_null($materiel->rapports)) {
                throw new AppException("Materiel deja actif.");
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
            $vente->type = 'recrutement';
            $vente->option = "";
            foreach(request()->options as $value) {
                $vente->option .= $value."-";
            }

            $userAfrocash = request()->user()->afroCash()->first();
            $userAfrocashGrossiste = request()->user()->afroCash('semi_grossiste')->first();

            // verifier la dispoinibilite du montant
            if($userAfrocashGrossiste->solde < request()->montant) {
                throw new AppException("Montant indisponible.");
            }
            
            $userAfrocashGrossiste->solde -= request()->montant;
            $userAfrocash->solde += request()->montant;

            $trans = new TransactionAfrocash;
            $trans->compte_credite = $userAfrocash->numero_compte;
            $trans->compte_debite = $userAfrocashGrossiste->numero_compte;
            $trans->montant = request()->montant;
            $trans->motif = "Vente_Recrutement";
            

            if($vente->save()) {
                if($trans->save()) {
                    if($userAfrocashGrossiste->update() && $userAfrocash->update()) {
                        // Envoi du sms de confirmation
                        $msg = "Bonjour, votre abonnement est activé pour ".request()->duree." mois , à la formule ".request()->formule." , par ".request()->user()->localisation."\nMerci pour votre fidelite.";
                        if($this->sendSmsToNumber(request()->telephone,$msg)) {
                            return response()->json('done');
                        }
                        else {
                            return response()->json('done');
                        }
                    }
                }
            }
            throw new AppException("Erreur de traitement , ressayez.");
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
