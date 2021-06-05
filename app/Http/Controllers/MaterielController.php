<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use App\User;
use App\Articles;
use App\StockPrime;
use App\TransactionAfrocash;
class MaterielController extends Controller
{
    /**
     * Retour Materiel
     */
    public function retourMateriel()
    {
        try
        {
            $validation = request()->validate([
                'materiels.*'   =>  'required|string|exists:exemplaire,serial_number',
                'distributeur'  =>  'required|string|exists:users,username',
                // 'depot' =>  'required|string|exists:depots,localisation',
                'quantite'  =>  'required'
            ]);

            $distributeur = User::where('username',request()->distributeur)->first();

            // verifier si les materiels choisi appartiennent au distributeur
            foreach(request()->materiels as $mat)
            {
                $serial = $distributeur->exemplaire()->where('serial_number',$mat)->first();
                if(!$serial)
                {
                    throw new AppException("Ce materiel n'appartient pas au distributeur choisi : ".$mat);
                }

                if(!($serial->status == 'inactif' && is_null($serial->rapports)))
                {
                    throw new AppException("Ce materiel est deja actif : ".$mat);
                }
            }

            $materiels = $distributeur->exemplaire()->whereIn('serial_number',request()->materiels)->get();
            $depots = [];

            foreach($materiels as $mat)
            {
                // desaffectation du vendeur
                $mat->vendeurs = null;
                array_push($depots,$mat->depot()->depot);
            }

            $produit = $materiels->first()->produit();
            $kitSlug = $produit->articles()->first()->kit_slug;
            $articles = Articles::select('produit')->where('kit_slug',$kitSlug)->groupBy('produit')->get();
            $stockVendeur = $distributeur->stockVendeurs()->whereIn('produit',$articles)->get();

            foreach($stockVendeur as $s)
            {
                $s->quantite -= request()->quantite;
            }

            $stockDepot = StockPrime::whereIn('produit',$articles)->whereIn('depot',$depots)->get();

            if(count($stockDepot) > 2)
            {
                throw new AppException("Les depots sont differents.");
            }

            foreach($stockDepot as $sd)
            {
                $sd->quantite += request()->quantite;
            }

            // calcul du prix total de(s) materiel(s)

            $totalAmount = $produit->prix_vente * request()->quantite;
            $sender_user = User::where('type','logistique')->first();
            $sender_account = $sender_user->afroCash()->first();

            $receiver_account = $distributeur->afroCash()->first();

            $sender_account->solde -= $totalAmount;
            $receiver_account->solde += $totalAmount;

            // enregistrement de la transaction

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $totalAmount;
            $trans->motif = "RETOUR_MATERIEL";

            if($sender_account->update() && $receiver_account->update())
            {
                foreach($stockDepot as $sd)
                {
                    $sd->update();
                }
                foreach($materiels as $m)
                {
                    $m->update();
                }
                foreach($stockVendeur as $s)
                {
                    $s->update();
                }
                if($trans->save())
                {
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
}
