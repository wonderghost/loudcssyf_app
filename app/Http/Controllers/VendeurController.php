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

    public function __construct() {
        //
    }

    public function addClient() {
    	return view('simple-users.add-client');
    }

    public function makeAddClient(ClientRequest $request) {
        if($client = $this->isExistClientInSystem($request->input('email'))) {
            if($rep = $this->isExistClient($client->email,Auth::user()->username)) {
                // dd($client);
                return redirect('/user/add-client')->with('_errors',"Cet Client Existe deja!");

            } else {
                $repertoire = new Repertoire;
                $repertoire->client = $client->email;
                $repertoire->vendeur = Auth::user()->username;
                $repertoire->save();
            }
        } else {
            $client = new Client;
            $client->nom = $request->input('nom');
            $client->prenom = $request->input('prenom');
            $client->email = $request->input('email');
            $client->phone = $request->input('phone');
            $client->adresse = $request->input('adresse');

            $repertoire = new Repertoire;
            $repertoire->client = $request->input('email');
            $repertoire->vendeur = Auth::user()->username;
            // dd($repertoire);
            $client->save();
            $repertoire->save();
        }
        	return redirect('/user/add-client')->with('success',"Nouveau client ajouté!");

    	// dd($client);
    }

    public function listClient() {

        return view('simple-users.list-client');
    }

    public function getListClient(Request $request) {
        $all = Client::select()->whereIn('email',Repertoire::select('client')->where('vendeur',Auth::user()->username))->get();
        return response()->json($all);
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

            foreach($promos as $key => $value) {
                $data [$key] = $rp->where('vendeurs',$request->user()->username)
                    ->where('promo_id',$value->id)
                    ->first();
            }

            foreach($data as $value) {
                $thePromo = $value->promos();
                if(is_null($value->pay_at) && $value->montant !== 0 && $thePromo->status_promo == 'inactif') {
                    $flag = false;
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

}
