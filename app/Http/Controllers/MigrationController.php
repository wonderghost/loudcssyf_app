<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AppException;
use App\Exemplaire;
use App\Ventes;
use App\TransactionAfrocash;
use App\Kits;

class MigrationController extends Controller
{
    /**
     * migration en vente chez les vendeurs standarts
     */
    public function create()
    {
        try
        {
            $validation = request()->validate([
                'materiel'  =>  'required|string|exists:exemplaire,serial_number|unique:ventes,materiel|min:14|max:14',
                'nom'   =>  'required|string',
                'prenom'   =>  'required|string',
                'telephone'   =>  'required|string|max:9|min:9',
            ],[
                'required'  =>  '`:attribute` requis.',
                'exists'    =>  '`:attribute` inexistant.',
                'materiel.max'   =>  '14 chiffres requis pour le Numero materiel',
                'materiel.min'   =>  '14 chiffres requis pour le Numero materiel',
                'telephone.min'   =>  '9 chiffres requis pour le Numero de telephone',
                'telephone.max'   =>  '9 chiffres requis pour le Numero de telephone',
                'unique'    =>  '`:attribute` deja actif.'
            ]);

            // validation du mot de passe
            if(!Hash::check(request()->password,request()->user()->password))
            {
                throw new AppException("Mot de passe invalide.");
            }

            $materiel = Exemplaire::find(request()->materiel);

            if(!is_null($materiel->rapports))
            {
                throw new AppException("Materiel deja actif.");
            }

            $stock = request()->user()->stockVendeurs()
                ->select('quantite')
                ->where('produit',$materiel->produit()->reference)
                ->groupBy('quantite')
                ->first();

            if($stock->quantite <= 0) {
                throw new ErrorException("Quantite indisponible.");
            }

            $vente = new Ventes;
            $vente->nom = request()->nom;
            $vente->prenom = request()->prenom;
            $vente->quartier = "";
            $vente->materiel = request()->materiel;
            // $vente->formule = request()->formule;
            $vente->telephone = request()->telephone;
            $vente->montant = request()->montant;
            $vente->duree = 0;
            $vente->id_user = request()->user()->username;
            $vente->type = 'migration';
            $vente->option = "";

            $userAfrocash = request()->user()->afroCash()->first();
            $userAfrocashGrossiste = request()->user()->afroCash('semi_grossiste')->first();

            if($userAfrocashGrossiste->solde < request()->montant) {
                throw new AppException("Montant indisponibile.");
            }
            
            $userAfrocashGrossiste->solde -= request()->montant;
            $userAfrocash->solde += request()->montant;

            // enregistrement de la transaction

            $transVenteMigration = new TransactionAfrocash;
            $transVenteMigration->compte_credite = $userAfrocash->numero_compte;
            $transVenteMigration->compte_debite = $userAfrocashGrossiste->numero_compte;
            $transVenteMigration->montant = request()->montant;
            $transVenteMigration->motif = "VENTE_MIGRATION";

            if($vente->save())
            {
                if($userAfrocash->update() && $userAfrocashGrossiste->update())
                {
                    if($transVenteMigration->save())
                    {
                        return response()->json('done',200);
                    }
                }
            }
            throw new AppException("Erreur de traitement.Contactez l'administrateur");
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }

    /**
     * Initialisation des donnees
     */
    public function index()
    {
        try
        {
            $kit = Kits::find('kits-canal-sat');
            $mat = $kit->getTerminalReference();
            return response()->json($mat->prix_vente,200);
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }
}
