<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AppException;
// use Illuminate\Support\Str;

use App\User;
use App\Agence;
use App\Traits\Similarity;
use App\ReseauxPdc;
use App\Traits\Afrocashes;
use App\TransactionAfrocash;
use App\ReaboAfrocash;
use App\ReaboAfrocashSetting;
use App\OptionReaboAfrocash;


class PdrafController extends Controller
{
    //

    use Similarity;
    use Afrocashes;

    public function generateId() {
        do {
            $_id = "PDRAF-".mt_rand(1000,9999);
        } while($this->isExistUsername($_id));

        return $_id;
    }

    public function addNewPdraf(Request $request) {
        try {
            $validation = $request->validate([
                'email' =>  'required|email|unique:users,email',
                'phone' =>  'required|string',
                'agence'    =>  'required|string',
                'access'    =>  'required|string',
                'password_confirmation' =>  'required|string',
                'pdc'   =>  'required|string|exists:users,username'
            ],[
                'required'  =>  '`:attribute` requis !',
                'email' =>  '`:attribute` doit une adresse email valide !',
                'max'   =>  '9 caracteres maximum pour le `:attribute` !',
                'min'   =>  '9 caracteres minimum pour le `:attribute` !'
            ]);

            // confirmation du mot de passe utilisateur
            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            $u = new User;
            $a = new Agence;
            $r = new ReseauxPdc;



            $u->username = $this->generateId();
            $u->email = $request->input("email");
            $u->phone = $request->input('phone');
            $u->type = $request->input('access');
            $u->localisation = $request->input('agence');
            $u->password = bcrypt("loudcssyf");

            // agence inexistante
            
            do{
                $a->reference = 'AG-'.mt_rand(1000,9999);
            }while($this->isExistAgenceRef($a->reference));

            $u->agence = $a->reference;
            $a->societe = $request->input('societe');
            $a->rccm = $request->input('rccm');
            $a->ville = $request->input('ville');
            $a->adresse = $request->input('adresse');

            $r->id_pdc = $request->input('pdc');
            $r->id_pdraf = $u->username;

            $a->save();
            $u->save();
            $this->newAccount($u->username);
            $r->save();

            
            return response()
                ->json('done');

        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function index() {
        return view('pdraf.pdraf-home');
    }

    public function sendTransaction(Request $request) {
        try {

            $validation = $request->validate([
                'destinataire'  =>  'required|string|exists:users,username',
                'montant'   =>  'required|numeric|min:100000|max:15000000',
                'password_confirmation' =>  'required|string'
            ],[
                'required'  =>  '`:attribute` requis !',
                'exists'    =>  'Utilisateur inexistant!',
                'min'   =>  'Montant minimum requis est de 100,000',
                'max'   =>  'Montant maximum requis est de 15,000,000'
            ]);

            // validation du password
            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            // 
            $sender_account = $request->user()->afroCash()->first();

            $receiver_user = User::where('username',$request->input('destinataire'))->first();
            $receiver_account = $receiver_user->afroCash()->first();

            // disponibilite du montant 
            if($request->input('montant') > $sender_account->solde) {
                throw new AppException("Montant invalide !");
            }

            $sender_account->solde -= $request->input('montant');
            $receiver_account->solde += $request->input('montant');

            // enregistrement de la transaction 

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $request->input('montant');
            $trans->motif = "Transfert Courant Afrocash!";


            $sender_account->save();
            $receiver_account->save();
            $trans->save();

            $n = $this->sendNotification(
                "Transfert Courant Afrocash" ,
                "Reception de ".number_format($request->input('montant'))." GNF de la part de ".$request->user()->localisation,
                $receiver_user->username
                );
            $n->save();

            $n = $this->sendNotification(
                "Transfert Courant Afrocash" ,
                "Envoi de  ".number_format($request->input('montant'))." GNF a : ".$receiver_user->localisation,
                $request->user()->username
                );
            $n->save();            

            $n = $this->sendNotification(
                "Transfert Courant Afrocash" ,
                "Envoi de : ".number_format($request->input('montant'))." GNF de la part de ".$request->user()->localisation.", a : ".$receiver_user->localisation,
                'admin'
                );
            $n->save();            

            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getOtherUsers(Request $request) {
        try {
            $pdc_user = $request->user()->pdcUser()->usersPdc();
            $others = $pdc_user->pdrafUsers();

            $data = [];

            foreach($others as $key => $value) {
                $data[$key] = [
                    'user'  =>  $value->usersPdraf()
                ];
            }
            
            return response()
                ->json($data);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getSolde(Request $request) {
        try {
            return response()
                ->json($request->user()->afroCash()->first());
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function infosRetourAfrocash(Request $request) {
        try {
            $pdc_user = $request->user()->pdcUser()->usersPdc();
            return response()
                ->json($pdc_user);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    public function sendRetourAfrocash(Request $request) {
        try {
            $validation = $request->validate([
                'pdc_id'    =>  'required|string|exists:users,username',
                'montant'   =>  'required|numeric|min:1000000',
                'password_confirmation' =>  'required|string'
            ],[
                'min'   =>  'Le montant minimum est de 1,000,000'
            ]);

            // password validation 
            
            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            // disponibilite du montant
            
            $sender_account = $request->user()->afroCash()->first();

            $receiver_user = $request->user()->pdcUser()->usersPdc();
            $receiver_account = $receiver_user->afroCash('semi_grossiste')->first();

            if($request->input('montant') > $sender_account->solde) {
                throw new AppException("Montant invalide !");
            }

            $sender_account->solde -= $request->input('montant');
            $receiver_account->solde += $request->input('montant');

            
            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $request->input('montant');
            $trans->motif = "Retour Afrocash";
            
            $sender_account->save();
            $receiver_account->save();
            $trans->save();


            $n = $this->sendNotification(
                "Retour Afrocash" ,
                "Reception de ".number_format($request->input('montant'))." GNF de la part de ".$request->user()->localisation,
                $receiver_user->username
                );
            $n->save();

            $n = $this->sendNotification(
                "Retour Afrocash" ,
                "Envoi de  ".number_format($request->input('montant'))." GNF a : ".$receiver_user->localisation,
                $request->user()->username
                );
            $n->save();            

            $n = $this->sendNotification(
                "Retour Afrocash" ,
                "Envoi de : ".number_format($request->input('montant'))." GNF de la part de ".$request->user()->localisation.", a : ".$receiver_user->localisation,
                'admin'
                );
            $n->save();        

            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    ################################## SEND REABO AFROCASH #############################################

    public function sendReaboAfrocash(Request $request) {
        try {
            $validation = $request->validate([
                'serial_number' =>  'required|string|max:14|min:14|regex : /(^([0-9]+)?$)/',
                'formule'   =>  'required|string|exists:formule,nom',
                'duree' =>  'required|numeric|max:24|min:1',
                'telephone_client'  =>  'required|string|max:9|min:9',
                'montant_ttc'   =>  'required|numeric',
                'comission' =>  'required|numeric',
                'password_confirmation' =>  'required|string'
            ],[
                'required'  =>  '`:attribute` requis !',
            ]);

            // validation password

            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            // verifier la disponibilite du montant
            $sender_account = $request->user()->afroCash()->first();
            if($request->input('montant_ttc') > $sender_account->solde) {
                throw new AppException("Montant indisponible!");
            }

            $reabo = new ReaboAfrocash;
            $reabo->generateId();
            $reabo->serial_number = $request->input('serial_number');
            $reabo->formule_name = $request->input('formule');
            $reabo->duree = $request->input('duree');
            $reabo->telephone_client = $request->input('telephone_client');
            $reabo->montant_ttc = $request->input('montant_ttc');
            $reabo->comission = $request->input('comission');
            $reabo->pdraf_id = $request->user()->username;       

            // 

            $data_options = [];

            foreach($request->input('options') as $key => $value) {
                if($value && $value != "") {
                    $data_options[$key] = new OptionReaboAfrocash;
                    $data_options[$key]->id_reabo_afrocash = $reabo->id;
                    $data_options[$key]->id_option = $value && $value != "" ? $value : null;
                }
            }
            
            // get receiver
            $reabo_afrocash_setting = ReaboAfrocashSetting::all()->first();

            if(!$reabo_afrocash_setting) {
                throw new AppException("Parametre Reabo non defini , contactez l'administrateur");
            }

            $receiver_user = User::where('username',$reabo_afrocash_setting->user_to_receive)->first();
            $receiver_account = $receiver_user->afroCash()->first();


            $sender_account->solde -= $request->input('montant_ttc');
            $receiver_account->solde += $request->input('montant_ttc');

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $request->input('montant_ttc');
            $trans->motif = "Reabo_Afrocash";

            $reabo->save();
            
            if(count($data_options) > 0) {
                foreach($data_options as $value) {
                    $value->save();
                }
            }
            $sender_account->save();
            $receiver_account->save();
            $trans->save();


            
            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    public function setUserStandartForReabo(Request $request) {
        try {
            $validation = $request->validate([
                'username'  =>  'required|string|exists:users,username',
                'password_confirmation' =>  'required|string'
            ]);
            
            // validation du mot de passe 
            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide!");
            }

            // 
            $tmp = ReaboAfrocashSetting::all()->first();

            if($tmp) {
                $tmp->user_to_receive = $request->input('username');
                $tmp->save();
            }
            else {

                $reabo_setting = new ReaboAfrocashSetting;
                $reabo_setting->user_to_receive = $request->input('username');
                $reabo_setting->save();
            }
            
            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
