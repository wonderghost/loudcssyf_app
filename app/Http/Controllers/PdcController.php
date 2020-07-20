<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AppException;
// use Illuminate\Support\Str;

use App\User;
use App\Agence;
use App\Afrocash;
use App\TransactionAfrocash;

use App\Traits\Similarity;
use App\Traits\Afrocashes;



class PdcController extends Controller
{
    use Similarity;
    use Afrocashes;

    public function depotDepot(Request $request , Afrocash $a) {
        try {
            $validation = $request->validate([
                'pdc'   =>  'required|string|exists:users,username',
                'montant'   =>  'required|numeric|min:1000000',
                'password_confirmation' =>  'required|string'
            ],[
                'required'  =>  '`:attribute` requis !',
                'min'   =>  'le montant minimum requis est de : 1,000,000'
            ]);

            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }
            // recuperation des informations du compte a crediter
            $pdc_user = User::where('username',$request->input('pdc'))
                ->where('type','pdc')->first();
            $pdc_account = $pdc_user ? $pdc_user->afrocashAccount() : null;

            if(is_null($pdc_account)) {
                throw new AppException("Processing request !");
            }

            // recuperation des informations du compte a debiter
            $sender_account = $request->user()->afroCash('semi_grossiste')->first();

            $pdc_account->solde += $request->input('montant');
            $sender_account->solde -= $request->input('montant');

            // enregistrement de l'historique des transactions

            $t = new TransactionAfrocash;
            $t->compte_debite = $sender_account->numero_compte;
            $t->compte_credite = $pdc_account->numero_compte;
            $t->montant = $request->input('montant');
            $t->motif = "Depot Afrocash Grossiste";

            $sender_account->save();
            $pdc_account->save();
            $t->save();

            $n = $this->sendNotification(
                "Depot Afrocash" ,
                "Depot de  ".number_format($request->input('montant'))." GNF effectué par :".$request->user()->localisation." sur le compte de :".$pdc_user->localisation,
                'admin'
            );

            $n->save();

            $n = $this->sendNotification(
                "Depot Afrocash" ,
                "Reception de ".number_format($request->input('montant'))." GNF de la part de ".$request->user()->localisation,
                $pdc_user->username);

            $n->save();

            $n = $this->sendNotification(
                "Depot Afrocash",
                "Vous avez effectue un depot de ".number_format($request->input('montant'))." GNF pour ".$pdc_user->localisation,
                $request->user()->username
            );

            $n->save();


            return response()
                ->json('done');                
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getList(User $u) {
        try {
            return response()
                ->json($u->where('type','pdc')->get());
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function generateId() {
        do {
            $_id = "PDC-".mt_rand(1000,9999);
        }while($this->isExistUsername($_id));

        return $_id;
    }
    //

    public function addNewPdc(Request $request) {
        try {
            $validation = $request->validate([
                'email' =>  'required|email|unique:users,email',
                'phone' =>  'required|string|max:9|min:9',
                'agence' => 'required|string',
                'access'    =>  'required|string',
                'password_confirmation' =>  'required|string',
                'societe'   =>  'required|string'
            ],[
                'required'  =>  '`:attribute` requis !',
                'email' =>  '`:attribute` doit une adresse email valide !',
                'max'   =>  '9 caracteres maximum pour le `:attribute` !',
                'min'   =>  '9 caracteres minimum pour le `:attribute` !'
            ]);

            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            $a = new Agence;
            $u = new User;
            $account = new Afrocash;

            $u->username = $this->generateId();
            $u->email = $request->input("email");
            $u->phone = $request->input('phone');
            $u->type = $request->input('access');
            $u->localisation = $request->input('agence');
            $u->password = bcrypt("loudcssyf");



            // verifier si l'agence existe deja
            $ag_flag = false;
            
            if($temp = $this->isExistAgence($request->input('societe'))) {
                // agence existante
                $u->agence = $temp->reference;
                $ag_flag = false;
            }
            else {
                // agence inexistante
                $ag_flag = true;
                do{
                    $a->reference = 'AG-'.mt_rand(1000,9999);
                }while($this->isExistAgenceRef($a->reference));

                $u->agence = $a->reference;
                $a->societe = $request->input('societe');
                $a->rccm = $request->input('rccm');
                $a->ville = $request->input('ville');
                $a->adresse = $request->input('adresse');
                
            }
            
            if($ag_flag) {
                // agence a enregistrer
                $a->save();
            }

            $u->save();           
            // creation du compte grossiste
            $this->newAccount($u->username,'semi_grossiste');

            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
