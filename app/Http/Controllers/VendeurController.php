<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\ClientRequest;
use App\Traits\Similarity;
use App\Traits\Abonnements;
use App\Traits\Recrutement;
use App\Traits\Cga;
use App\Traits\Afrocashes;
use App\Traits\Livraisons;
use App\Client;
use App\Repertoire;
use App\Exemplaire;
use App\RapportVente;
use App\Promo;
use App\RemboursementPromo;
use App\Afrocash;
use App\User;
use Carbon\Carbon;
use App\TransactionAfrocash;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AppException;
use App\Produits;
use App\Ventes;


class VendeurController extends Controller
{
    //
    use Cga;
    use Similarity;
    use Recrutement;
    use Abonnements;
    use Livraisons;
    use Afrocashes;


    protected $materialPrice    =   0;

    public function addClient() {
    	return view('simple-users.add-client');
    }

    public function makeAddClient(Request $request , Client $c , Produits $produit , Repertoire $r) {
        try {

            $validation = $request->validate([
                'nom'   =>  'required',
                'prenom'    =>  'required',
                'serial.*'  =>  'string|distinct',
                'quantite_materiel' =>  'numeric|min:0'
            ]);

            $c->nom = $request->input('nom');
            $c->prenom = $request->input('prenom');
            $c->email = $request->input('email');
            $c->phone = $request->input('phone');
            $c->adresse = $request->input('adresse');
            $c->makeClientId();
            $tmp = $c->client_slug;
            
            $serials = [];

            $r->vendeurs = $request->user()->username;
            $r->id_clients = $tmp;
            
            $c->save();
            
            if($request->input('quantite_materiel') > 0) {
                // les numeros de materiel existent
                for($i = 0 ; $i < $request->input('quantite_materiel') ; $i++) {
                    $serials[$i] = Exemplaire::find($request->input('serial')[$i]);
                    if($serials[$i]) {
                        // si le numero existe deja en base de donnees
                        $serials[$i]->client_id = $tmp;
                    }
                    else {
                        // le numero n'existe pas en base de donnees
                        $serials[$i] = new Exemplaire;
                        $serials[$i]->serial_number = $request->input('serial')[$i];
                        $serials[$i]->produit = $produit->where('with_serial',1)->first()->reference;
                        $serials[$i]->status = 'actif';
                        $serials[$i]->origine = false;
                        $serials[$i]->client_id = $tmp;
                    }
                }

                for($i = 0 ; $i < $request->input('quantite_materiel') ; $i++) {
                    $serials[$i]->save();
                }

            }

            $r->save();

            return response()
                ->json('done');

        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function listClient(Request $request) {
        try {
            $repertoire = $request->user()->repertoire();
            $data = [];

            foreach($repertoire as $key => $value) {
                $data[$key] = [
                    'nom'   =>  $value->clients()->nom,
                    'prenom'    =>  $value->clients()->prenom,
                    'email' =>  $value->clients()->email,
                    'phone' =>  $value->clients()->phone,
                    'materiel'  =>  $value->clients()->materiel(),
                    'id'    =>  $value->clients()->client_slug
                ];
            }

            return response()
                ->json($data);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
    public function allVentes() {
        return view('ventes.toutes-ventes');
    }

    public function abonnement() {
        return view('ventes.abonnement');
    }

    // liste des numero de serie de materiel par vendeur
    public function SerialForVendeur(Request $request) {
      $serials = Exemplaire::where('vendeurs',$request->input('ref'))->get();
      $all =[];
      foreach ($serials as $key => $value) {
        $all[$key]= [
          'serial'  =>  $value->serial_number,
          'article' =>  'Terminal Hd z4',
          'vendeur' =>  $value->vendeurs()->first()->localisation,
          'status'  =>  $value->status,
          'origin'  =>  $value->depot()->depot
        ];
      }
      return response()->json($all);
    }

    // infos remboursement apres la promo

    public function infosRemboursement(Request $request , Promo $p  , RemboursementPromo $rp) {
        try {
            $promos = $p->select()->orderBy('created_at','desc')->get();
            $data = [];

            $flag = true;

            if($promos) {
                
                foreach($promos as $key => $value) {
                    $data [$key] = $rp->where('vendeurs',$request->user()->username)
                        ->where('promo_id',$value->id)
                        ->first();
                }
            }


            foreach($data as $key => $value) {
                if(!is_null($value)) {
                    $thePromo = $value->promos();
                    if((is_null($value->pay_at) && $value->montant != 0) && $thePromo->status_promo == 'inactif') {
                        $flag = false;
                    }
                }
            }

            return response()
                ->json($flag);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // envoi de la compense promo 
    public function sendCompensePromo(Request $request , Promo $p , RemboursementPromo $rp , Afrocash $a , User $u , TransactionAfrocash $ta) {
        try {
            $validation = $request->validate([
                'id_promo'  =>  'required',
                'password'  =>  'required'
            ],[
                'required'  =>  'le champ :attribute requis!'
            ]);
            
            // validation du mot de passe 
            if(!Hash::check($request->input('password'),$request->user()->password)) {
                throw new AppException("Mot de passe Invalide!");
            }


            $_promo = $p->find($request->input('id_promo'));
            // recuperation des info de la compense
            $remboursementPromo = $rp->where('vendeurs',$request->user()->username)
                ->where('promo_id',$request->input('id_promo'))
                ->first();

            $afrocash_account = $request->user()
                ->afroCash()
                ->first();

            $logistique = $u->where('type','logistique')
                ->first();

            $logistique_afrocash_account = $a->where('type','courant')
                ->where('vendeurs',$logistique->username)
                ->first();

            if($remboursementPromo->montant > 0) {
                // remboursement
                $afrocash_account->solde -= $remboursementPromo->montant;
                $logistique_afrocash_account->solde += $remboursementPromo->montant;

                $ta->compte_debite = $afrocash_account->numero_compte;
                $ta->compte_credite = $logistique_afrocash_account->numero_compte;
                $ta->montant = $remboursementPromo->montant;
                $ta->motif = "Remboursement Promo";
                $ta->save();

            } else {
                // compense
                $afrocash_account->solde -= $remboursementPromo->montant;
                $logistique_afrocash_account->solde += $remboursementPromo->montant;
                
                $ta->compte_debite = $logistique_afrocash_account->numero_compte;
                $ta->compte_credite = $afrocash_account->numero_compte;
                $ta->montant = $remboursementPromo->montant;
                $ta->motif = "Compense Promo";
                $ta->save();
            }

            $remboursementPromo->pay_at = Carbon::now();

            

            $afrocash_account->save();
            $logistique_afrocash_account->save();
            $remboursementPromo->save();

            return response()->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    /**
     * Historique de vente chez les vendeurs standarts
     */
    public function historiqueVentes() {
        try {
            $ventes = request()->user()->ventes()
                ->orderBy('created_at','desc')
                ->get();

            foreach($ventes as $value) {
                $date = new Carbon($value->created_at);
                $value->date = $date->toDateTimeString();
                $value->user = $value->user()->localisation;
            }

            return response()->json($ventes);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

}
