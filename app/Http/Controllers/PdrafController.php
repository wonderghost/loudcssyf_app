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
use App\MakePdraf;
use App\PayCommission;

use Carbon\Carbon;

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
                // 'email' =>  'required|email|unique:users,email',
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
            // $u->email = $request->input("email");
            $u->randomEmailGenerate();
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

            // 
            
            if($request->input('tag') == 'by_confirm') {
                $actifDemand = MakePdraf::find($request->input('by_confirm_id'));
                $actifDemand->confirmed_at = Carbon::now();
                $actifDemand->save();
            }

            
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

    ################################ ALL REABO AFROCASH ###########################

    // get Reabo Afrocash by pdc

    public function getAllReaboAfrocash(Request $request) {
        try {
            if($request->user()->type == 'admin' || $request->user()->type == 'gcga' || $request->user()->type == 'commercial') {

                // list reabonnement afrocash
                $data = ReaboAfrocash::select()
                    ->orderBy('created_at','desc')
                    ->paginate(100);
            }
            else if($request->user()->type == 'pdraf') {
                $data = ReaboAfrocash::select()
                    ->where('pdraf_id',$request->user()->username)
                    ->paginate(100);
            }
            else if($request->user()->type == 'pdc') {
                
                $pdraf_users = $request->user()->pdrafUsersForList()->select('id_pdraf')->groupBy('id_pdraf')->get();
                $data = ReaboAfrocash::select()
                    ->whereIn('pdraf_id',$pdraf_users)
                    ->paginate(100);
            }
            $all = [];
            

            foreach($data as $key => $value) {
                $marge = round(($value->montant_ttc/1.18) * (1.5/100),0);
                $options = "";
                foreach($value->options() as $_value) {
                    $options .= $_value->id_option.",";
                }

                $created_at = new Carbon($value->created_at);
                $confirm_at = $value->confirm_at ? new Carbon($value->confirm_at) : null;
                $remove_at = $value->remove_at ? new Carbon($value->remove_at) : null;
                $pay_at = $value->pay_at ? new Carbon($value->pay_at) : null;
                
                $all[$key] = [
                    'id'    =>  $value->id,
                    'materiel'  =>  $value->serial_number,
                    'formule'   =>  $value->formule_name,
                    'duree' =>  $value->duree,
                    'option'    =>  $options,
                    'montant'   =>  $value->montant_ttc,
                    'comission' =>  $value->comission,
                    'telephone_client'  =>  $value->telephone_client,
                    'pdraf' =>  $value->pdrafUser()->only('localisation','username'),
                    'pdc_hote'  =>  $value->pdrafUser()->pdcUser()->usersPdc()->only('localisation','username'),
                    'marge' =>  $marge,
                    'total' =>  $marge + $value->comission,
                    'created_at'    =>  $created_at->toDateTimeString(),
                    'confirm_at'    => $confirm_at ? $confirm_at->toDateTimeString() : null,
                    'remove_at' =>  $remove_at ? $remove_at->toDateTimeString() : null,
                    'pay_at'    =>  $pay_at ? $pay_at->toDateTimeString() : null,
                    'pay_comission_id'  =>  $value->pay_comission_id
                ];

            }

            return response()
                ->json([
                    'all'   =>  $all,
                    'next_url'	=> $data->nextPageUrl(),
					'last_url'	=> $data->previousPageUrl(),
					'per_page'	=>	$data->perPage(),
					'current_page'	=>	$data->currentPage(),
					'first_page'	=>	$data->url(1),
					'first_item'	=>	$data->firstItem(),
					'total'	=>	$data->total()
                ]);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getComissionToPay(Request $request) {
        try {
            $comission = 0;
            $marge = 0;
            $total = 0;

            $data_comission = [];
            $data_marge = [];

            if($request->user()->type == 'admin' || $request->user()->type == 'gcga' || $request->user()->type == 'commercial') {
                // list reabonnement afrocash
                $data_comission = ReaboAfrocash::whereNull('pay_at')
                    ->whereNotNull('confirm_at')
                    ->whereNull('remove_at')
                    ->get();

                $data_marge = ReaboAfrocash::whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->get();

                
            }
            else if($request->user()->type == 'pdraf') {
                $data_comission = ReaboAfrocash::whereNull('pay_at')
                    ->whereNotNull('confirm_at')
                    ->whereNull('remove_at')
                    ->where('pdraf_id',$request->user()->username)
                    ->get();
            }
            else if($request->user()->type == 'pdc') {
                
                $pdraf_users = $request->user()->pdrafUsersForList()->select('id_pdraf')->groupBy('id_pdraf')->get();

                $data_comission = ReaboAfrocash::whereNull('pay_at')
                    ->whereNotNull('confirm_at')
                    ->whereNull('remove_at')
                    ->whereIn('pdraf_id',$pdraf_users)
                    ->get();
                
                $data_marge = ReaboAfrocash::whereNotNull('confirm_at')
                ->whereNull('pay_comission_id')
                ->whereIn('pdraf_id',$pdraf_users)
                ->get();
                
            }

            foreach($data_comission as $value) {
                $comission += $value->comission;
            }

            foreach($data_marge as $value) {
                $tmp = round(($value->montant_ttc/1.18) * (1.5/100),0);
                $marge += $tmp;
            }

            return response()
                ->json([
                    'comission' =>  $comission,
                    'marge' =>  $marge,
                    'total' =>  $total
                ]);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    // HISTORIQUE DE PAIEMENT DE COMISSION POUR PDRAF

    public function getAllPayComission(Request $request , ReaboAfrocash $ra) {
        try {
            $reaboAfrocashGroupByPayDate = $ra->whereNotNull('pay_at')->get();
            
            return response()
                ->json($reaboAfrocashGroupByPayDate);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // ##############

    public function getAllPdraf() {
        try {
            $pdraf_users = User::where('type','pdraf')->get();
            $data = [];
            foreach($pdraf_users as $key => $value) {
                $data[$key] = [
                    'user'  =>  $value
                ];
            }
            return response()
                ->json($data);

        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getList(Request $request) {
        try {
            $pdraf_users = $request->user()->pdrafUsers();
            $all = [];
            foreach($pdraf_users as $key => $value) {
                $all[$key] = [
                    'user'  =>  $value->usersPdraf()
                ];
            }
            return response()
                ->json($all);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getListCreationPdraf() {
        try {
            $data = MakePdraf::select()->orderBy('created_at','desc')->get();
            $all = [];

            foreach($data as $key => $value) {
                $all[$key] = [
                    'id'    =>  $value->id,
                    'email' =>  $value->email,
                    'telephone' =>  $value->telephone,
                    'agence'    =>  $value->agence,
                    'adresse'   =>  $value->adresse,
                    'pdc'   =>  $value->pdcUser(),
                    'confirmed_at'  =>  $value->confirmed_at,
                    'remove_at'    =>  $value->removed_at
                ];
            }

            return response()
                ->json($all);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // CONFIRM REABO AFROCASH
    public function confirmReaboAfrocash(Request $request) {
        try {
            $validation = $request->validate([
                'id'    =>  'required|exists:reabo_afrocash,id',
            ]);

            $reabo_afrocash = ReaboAfrocash::find($request->input('id'));
            if(!is_null($reabo_afrocash->remove_at) && !is_null($reabo_afrocash->confirm_at)) {
                throw new AppException("Confirmation impossible pour les reabonnements deja annule !");
            }

            $reabo_afrocash->confirm_at = Carbon::now();
            $reabo_afrocash->save();
            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // REMOVE REABO AFROCASH

    public function removeReaboAfrocash(Request $request) {
        try {
            $validation = $request->validate([
                'id'    =>  'required|exists:reabo_afrocash,id'
            ]);

            $reabo_afrocash = ReaboAfrocash::find($request->input('id'));
            // verifier s'il n'a pas ete confirmer
            if(!is_null($reabo_afrocash->confirm_at) && !is_null($reabo_afrocash->remove_at)) {
                throw new AppException("Annulation impossible !");
            }
            $reabo_afrocash->remove_at = Carbon::now();

            // account pdraf
            $receiver_user = $reabo_afrocash->pdrafUser();
            $receiver_account = $receiver_user->afroCash()->first();

            $reabo_afrocash_setting = ReaboAfrocashSetting::all()->first();

            if(!$reabo_afrocash_setting) {
                throw new AppException("Parametre Reabo non defini , contactez l'administrateur !");
            }

            $sender_user = User::where('username',$reabo_afrocash_setting->user_to_receive)->first();
            $sender_account = $sender_user->afroCash()->first();

            $receiver_account->solde += $reabo_afrocash->montant_ttc;
            $sender_account->solde -= $reabo_afrocash->montant_ttc;

            // enregistrement de la transaction

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $reabo_afrocash->montant_ttc;
            $trans->motif = "Annul_Reabo_Afrocash";


            $sender_account->save();
            $receiver_account->save();
            $reabo_afrocash->save();
            $trans->save();

            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // DEMANDE DE PAIEMENT DE COMISSION POUR PDRAF

    public function sendPayComissionRequest(Request $request) {
        try {
            $validation = $request->validate([
                'montant'   =>  'required|numeric|min : 10000',
                'password_confirmation'  => 'required|string',
            ],[
                'required'  =>  '`:attribute` requis !',
                'min'   =>  'Le montant minimum requis est de : 100,000 GNF'
            ]);
                // verification de la validite du mot de passe
            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            $reabo_afrocash = $request->user()->reaboAfrocash()
                ->whereNull('remove_at')
                ->whereNull('pay_at')
                ->whereNotNull('confirm_at')
                ->get();

            if($reabo_afrocash->count() <= 0) {
                throw new AppException("Vous n'avez pas de comission !");
            }

            $comission = 0;

            foreach($reabo_afrocash as $value) {
                $comission += $value->comission;
                $value->pay_at = Carbon::now();
            }

            if($comission != $request->input('montant')) {
                throw new AppException("Erreur ! Ressayez");
            }

            $receiver_account = $request->user()->afroCash()->first();

            $sender_user = $request->user()->pdcUser()->usersPdc();
            $sender_account = $sender_user->afroCash('semi_grossiste')->first();

            $receiver_account->solde += $comission;
            $sender_account->solde -= $comission;

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $comission;
            $trans->motif = "Paiement_Comission";
            

            

            $sender_account->save();
            $receiver_account->save();
            $trans->save();

            foreach($reabo_afrocash as $value) {
                $value->save();
            }

            $n = $this->sendNotification(
                "Paiement Comission" ,
                "Reception de ".number_format($comission)." GNF de la part de ".$sender_user->localisation,
                $request->user()->username
                );
            $n->save();

            $n = $this->sendNotification(
                "Paiement Comission" ,
                "Envoi de ".number_format($comission)." GNF a :".$request->user()->localisation,
                $sender_user->username
                );
            $n->save();

            $n = $this->sendNotification(
                "Paiement Comission" ,
                "Envoi de : ".number_format($comission)." GNF de la part de ".$sender_user->localisation.", a : ".$request->user()->localisation,
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
}
