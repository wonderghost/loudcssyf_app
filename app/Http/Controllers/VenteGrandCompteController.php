<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VenteGrandCompte;
use Carbon\Carbon;
use App\Traits\SendSms;
use App\Credit;
use App\ReaboAfrocashSetting;
use App\User;
use App\TransactionAfrocash;

class VenteGrandCompteController extends Controller
{
    use SendSms;
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

    /**
     * Confirmation de vente grand compte
     */
    public function confirm()
    {
        try
        {
            $validation = request()->validate([
                'id'    =>  'required|exists:vente_grand_compte,id'
            ]);

            $vente = VenteGrandCompte::find(request()->id);
            // verifier si la vente n'est confirmer
            
            if(!is_null($vente->confirmed_at))
            {
                throw new AppException("Instance déjà confirmée");
            }

            $vente->confirmed_at = Carbon::now();

            $abonne = $vente->abonne();
            $msg = "Bonjour Mr ".$abonne->nom.' '.$abonne->prenom." votre abonnement ".$abonne->formule;
            $msg .= " a été reconduit pour ".$abonne->duree." mois , ".number_format($vente->montant,0,'','.')." GNF,";
            $msg .= " Prière de disponibiliser le montant lors du virement.\nAFROCASH vous remercie pour la fidélit&eacute;.";    
            
            if($vente->update())
            {
                if($this->sendSmsToNumber($abonne->telephone,$msg)) {
                    return response()->json('done',200);
                }
            }
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }


    /**
     * 
     */
    public function delete($id)
    {
        try
        {
            $vente = VenteGrandCompte::find($id);
            if(!$vente)
            {
                throw new AppException("Vente inexistante.");
            }

            // verifier si la vente n'est pas confirmer

            if(!is_null($vente->confirmed_at) || !is_null($vente->removed_at))
            {
                throw new AppException("Cette ne peut etre effectue");
            }

            $vente->removed_at = Carbon::now();

            $reabo_afrocash_setting = ReaboAfrocashSetting::all()->first();
            if(!$reabo_afrocash_setting)
            {
                throw new AppException("Parametre Reabo non defini , contactez l'administrateur !");
            }

            $sender_user = User::where('username',$reabo_afrocash_setting->user_to_receive)->first();

            $sender_account = $sender_user->afroCash()->first();
            $receiver_account = Credit::find('afrocash');

            $sender_account->solde -= $vente->montant;
            $receiver_account->solde += $vente->montant;

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $vente->montant;
            $trans->motif = "ANNUL_VENTE";
            
            if($sender_account->update() && $receiver_account->update())
            {
                if($trans->save())
                {
                    if($vente->update())
                    {
                        return response()->json('done',200);
                    }
                }
            }

            throw new AppException("Erreur_Network.");
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }
}
