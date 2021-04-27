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
use Illuminate\Support\Str;
use App\User;

class RecrutementController extends Controller
{
    use SendSms;

    protected $easyReceiverPhone ='629000099';

    public function getFormule(Request $request) {
    	return response()->json();
    }

    /**
     * Creation d'un recrutement EASY
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

            $stock = request()->user()->stockVendeurs()
                ->select('quantite')
                ->where('produit',$materiel->produit()->reference)
                ->groupBy('quantite')
                ->first();
            
            if($stock->quantite <= 0) {
                throw new ErrorException("Quantite indisponible !");
            }

            $serialIntervalData = Str::substr(request()->materiel,0,3);
            $produit = $materiel->produit();
            $intervals = $produit->intervals()
                ->first()
                ->intervalData()           
                ->first();
            $formuleInterval = $intervals->formule()
                ->where('id_formule',request()->formule)
                ->first();
            
            if(!$formuleInterval) {
                throw new AppException("Erreur sur la formule choisie ... Verifiez le numero materiel !");
            }

            # DEBIT DU STOCK DU VENDEUR

            $article = $produit->articles()
                ->first()
                ->kits()
                ->first()
                ->articles()
                ->select('produit')
                ->groupBy('produit')
                ->get();

            $stock_vendeur = request()->user()->stockVendeurs()
                ->whereIn('produit',$article)
                ->get();

            foreach($stock_vendeur as $value) {
                $value->quantite --;
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

            # TRANSACTION MARGE MATERIEL

            // $logistiqueUser = User::where('type','logistique')
            //     ->first();
            // $logistiqueAccount = $logistiqueUser->afrocash()->where('type','courant')
            //     ->first();

            // $montantMargeMateriel = ceil($produit->marge / 1.18);

            // $userAfrocash->solde += $montantMargeMateriel;
            // $logistiqueAccount->solde -= $montantMargeMateriel;

            // $trans = new TransactionAfrocash;
            // $trans->compte_credite = $userAfrocash->numero_compte;
            // $trans->compte_debite = $logistiqueAccount->numero_compte;
            // $trans->montant = $montantMargeMateriel;
            // $trans->motif = "Paiement_Marge_Materiel";

            // Changement  de status du materiel EASY
            $materiel->status = 'actif';
            $msg = "Bonjour, votre abonnement est activé pour ".request()->duree." mois , à la formule ".request()->formule." , par ".request()->user()->localisation."\nMerci pour votre fidelite.";
            $msgEasy = "E#";
            $msgEasy .= request()->nom.request()->prenom."#";
            $msgEasy .= request()->telephone."#";
            $msgEasy .= request()->materiel."#TV1";

            if($vente->save()) {
                if(request()->user()->type == 'v_standart')
                {
                    // Vendeurs Standart

                    $userAfrocashGrossiste = request()->user()->afroCash('semi_grossiste')->first();

                    // Verifier la disponibilite du montant
                    if($userAfrocashGrossiste->solde < request()->montant) {
                        throw new AppException("Montant indisponibile.");
                    }
                    
                    $userAfrocashGrossiste->solde -= request()->montant;
                    $userAfrocash->solde += request()->montant;

                    // Transaction Vente Recrutement
                    $transVenteRecrutement = new TransactionAfrocash;
                    $transVenteRecrutement->compte_credite = $userAfrocash->numero_compte;
                    $transVenteRecrutement->compte_debite = $userAfrocashGrossiste->numero_compte;
                    $transVenteRecrutement->montant = request()->montant;
                    $transVenteRecrutement->motif = "Vente_Recrutement";

                    if($transVenteRecrutement->save() && $materiel->update()) {
                        // debit de la quantite de stock
                        foreach($stock_vendeur as $value) {
                            $value->update();
                        }

                        if($userAfrocash->update() && $userAfrocashGrossiste->update()) {
                           // recrutement easy tv
                           return response()->json('done',200);
                        //    if($this->sendSmsToNumber($this->easyReceiverPhone,$msgEasy))
                        //    {
                        //         return response()->json('done',200);
                        //    }
                        //    else
                        //    {
                        //         return response()->json('done',200);
                        //    }
                        }
                    }
                    
                }
                // else if(request()->user()->type == 'v_da')
                // {
                //     // Distributeur Agree
                    
                //     foreach($stock_vendeur as $value) {
                //         $value->update();
                //     }
    
                //     if($trans->save() && $materiel->update()) {
                //         if($logistiqueAccount->update() && $userAfrocash->update()) {
                //             // recrutement easy tv
                //             if($this->sendSmsToNumber($this->easyReceiverPhone,$msgEasy))
                //             {
                //                 return response()->json('done',200);
                //             }
                //             else
                //             {
                //                 return response()->json('done',200);
                //             }
                //         }
                //     }
                // }
            }
            throw new AppException("Erreur de traitement , ressayez.");
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    /**
     * Recrutement canal+
     */
    public function _create()
    {
        try
        {
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

            $serialIntervalData = Str::substr(request()->materiel,0,3);
            $produit = $materiel->produit();
            $intervals = $produit->intervals()
                ->first()
                ->intervalData()           
                ->first();
            $formuleInterval = $intervals->formule()
                ->where('id_formule',request()->formule)
                ->first();
            
            if(!$formuleInterval) {
                throw new AppException("Erreur sur la formule choisie ... Verifiez le numero materiel !");
            }

            $vente = new Ventes;
            $vente->nom = request()->nom;
            $vente->prenom = request()->prenom;
            $vente->quartier = "";
            $vente->materiel = request()->materiel;
            $vente->formule = request()->formule;
            $vente->duree = request()->duree;
            $vente->telephone = request()->telephone;
            $vente->montant = request()->montant + $produit->prix_vente;
            $vente->id_user = request()->user()->username;
            $vente->type = 'recrutement';
            $vente->option = "";

            foreach(request()->options as $value) {
                $vente->option .= $value."-";
            }

            $userAfrocash = request()->user()->afroCash()->first();
            $userAfrocashGrossiste = request()->user()->afroCash('semi_grossiste')->first();

            // Verifier la disponibilite du montant
            if($userAfrocashGrossiste->solde < request()->montant + $produit->prix_vente) {
                throw new AppException("Montant indisponibile.");
            }
            
            $userAfrocashGrossiste->solde -= request()->montant + $produit->prix_vente;
            $userAfrocash->solde += request()->montant + $produit->prix_vente;

            $trans = new TransactionAfrocash;
            $trans->compte_credite = $userAfrocash->numero_compte;
            $trans->compte_debite = $userAfrocashGrossiste->numero_compte;
            $trans->montant = request()->montant + $produit->prix_vente;
            $trans->motif = "Vente_Recrutement";

            if($vente->save()) {
                if($trans->save()) {
                    if($userAfrocash->update() && $userAfrocashGrossiste->update()) {
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

            throw new AppException("Erreur de traitement... ressayez.");
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }

}
