<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AppException;
use Illuminate\Support\Str;

use App\User;
use App\Agence;
use App\Traits\Similarity;
use App\ReseauxPdc;
use App\Traits\Afrocashes;
use App\TransactionAfrocash;
use App\ReaboAfrocash;
use App\ReaboAfrocashSetting;
use App\OptionReaboAfrocash;
use App\UpgradeReaboAfrocash;
use App\MakePdraf;
use App\PayCommission;
use App\Credit;
use App\ReactivationMateriel;
use Carbon\Carbon;
use App\RecrutementAfrocash;
use App\RecrutementAfrocashOption;

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

    public function listPdraf(User $u) {
        try {
            $data_pdraf = $u->where('type','pdraf')
                ->get();
            
            $data_pdc = $u->where('type','pdc')
                ->get();

            $pdarf_list = [];

            foreach($data_pdraf as $key => $value) {
                $pdraf_list[$key] = [
                    'username'  =>  $value->username,
                    'localisation'  =>  $value->localisation,
                    'pdc'   =>  [
                        'username'  =>  $value->pdcUser()->usersPdc()->username,
                        'localisation'  =>  $value->pdcUser()->usersPdc()->localisation,
                    ]
                ];
            }

            return response()
                ->json([
                    'pdraf_list' => $pdraf_list,
                    'pdc_list'  =>  $data_pdc
                ]);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
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

    ####################################### SEND RECRUTEMENT AFROCASH ##################################################

    public function sendRecrutementAfrocash() {
        try {
            $validation = request()->validate([
                'serial_number' =>  'required|exists:exemplaire,serial_number|string|max:14|min:14|regex : /(^([0-9]+)?$)/',
                'formule'   =>  'required|string|exists:formule,nom',
                'duree' =>  'required|numeric|max:24|min:1',
                'telephone_client'  =>  'required|string|max:9|min:9',
                'montant_ttc'   =>  'required|numeric',
                'comission' =>  'required|numeric',
                'password_confirmation' =>  'required|string',
                'nom'   =>  'required|string',
                'prenom'    =>  'required|string',
                'ville' =>  'required|string',
            ],[
                'required'  =>  '`:attribute` requis !',
                'exists'   =>   '`:attribute` n\'existe pas dans le systeme'
            ]);

             // VALIDATION DU MOT DE PASSE

            if(!Hash::check(request()->password_confirmation,request()->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            // VERIFIER LA DISPONIBILITE DU MONTANT
            $sender_account = request()->user()->afroCash()->first();
            if(request()->montant_ttc > $sender_account->solde) {
                throw new AppException("Montant indisponible!");
            }

            # VERIFIER SI LE NUMERO APPARTIEN AU PDRAF
            $serial = request()->user()->exemplaireForPdraf()
                ->where('serial_number',request()->serial_number)
                ->whereNull('recrutement_afrocash_id')
                ->first();

            if(!$serial) {
                throw new AppException("Materiel invalide !");
            }

            # VERIFIER LA DISPONIBILITE DE LA QUANTITE
            $stockVendeur = request()->user()->stockVendeurs()
                ->select('quantite')
                ->where('produit',$serial->produit()->reference)
                ->groupBy('quantite')
                ->first();

            if($stockVendeur->quantite <= 0) {
                throw new AppException("Quantite indisponible !");
            }

            # VERIFIER LA CONFORMITE ENTRE LE NUMERO ET LA FORMULE A TRAVERS L'INTERVAL
            $serialIntervalData = Str::substr(request()->serial_number,0,3);
            $produit = $serial->produit(); 
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

            $recrutement = new RecrutementAfrocash;
            $recrutement->generateId();
            $recrutement->formule_name = request()->formule;
            $recrutement->duree = request()->duree;
            $recrutement->telephone_client = request()->telephone_client;
            $recrutement->montant_ttc = request()->montant_ttc;
            $recrutement->comission = request()->comission;
            $recrutement->pdraf_id = request()->user()->username;
            $recrutement->nom = request()->nom;
            $recrutement->prenom = request()->prenom;
            $recrutement->ville = request()->ville;
            $recrutement->adresse_postal = request()->adress_postal;
            $recrutement->email = request()->email;

            $data_options = [];

            foreach(request()->options as $key => $value) {
                if($value && $value != "") {
                    $data_options[$key] = new RecrutementAfrocashOption;
                    $data_options[$key]->id_recrutement_afrocash = $recrutement->id;
                    $data_options[$key]->id_option = $value && $value != "" ? $value : null;
                }
            }

            $serial->recrutement_afrocash_id = $recrutement->id;

            # DEBIT DU SOLDE
            $sender_account = request()->user()->afroCash()->first();
            
            $reabo_afrocash_setting = ReaboAfrocashSetting::all()->first();

            if(!$reabo_afrocash_setting) {
                throw new AppException("Parametre Reabo non defini , contactez l'administrateur");
            }

            $receiver_user = User::where('username',$reabo_afrocash_setting->user_to_receive)->first();
            $receiver_account = $receiver_user->afroCash()->first();

            $sender_account->solde -= request()->montant_ttc;
            $receiver_account->solde += request()->montant_ttc;

            # DEBIT DU STOCK DU PDRAF

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

            # TRANSACTION RECRUTEMENT MATERIEL

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = request()->montant_ttc;
            $trans->motif = "Recrutement_Afrocash";
            $trans->recrutement_afrocash_id = $recrutement->id;

            # TRANSACTION MARGE MATERIEL

            $logistiqueUser = User::where('type','logistique')
                ->first();
            $logistiqueAccount = $logistiqueUser->afroCash()->first();

            $pdcUser = request()->user()
                ->pdcUser()
                ->usersPdc();

            $pdcAccount = $pdcUser->afroCash('semi_grossiste')->first();

            $montantMargeMateriel = ceil($produit->marge_pdraf / 1.18);
            $montantMargeMaterielPdc = ceil($produit->marge_pdc / 1.18);

            $sender_account->solde += $montantMargeMateriel;
            $logistiqueAccount->solde -= $montantMargeMateriel;

            # TRANSACTION MARGE MATERIEL PDC

            $pdcAccount->solde += $montantMargeMaterielPdc;
            $logistiqueAccount->solde -= $montantMargeMaterielPdc;

            $transMarge = new TransactionAfrocash;
            $transMarge->compte_debite = $logistiqueAccount->numero_compte;
            $transMarge->compte_credite = $sender_account->numero_compte;
            $transMarge->montant = $montantMargeMateriel;
            $transMarge->motif = "Paiement_Marge_Materiel";
            $transMarge->recrutement_afrocash_id = $recrutement->id;

            $transMargePdc = new TransactionAfrocash;
            $transMargePdc->compte_debite = $logistiqueAccount->numero_compte;
            $transMargePdc->compte_credite = $pdcAccount->numero_compte;
            $transMargePdc->montant = $montantMargeMaterielPdc;
            $transMargePdc->motif = "Paiement_Marge_materiel";
            $transMargePdc->recrutement_afrocash_id = $recrutement->id;

            # 
            $recrutement->save();

            foreach(request()->options as $key => $value) {
                $data_options[$key]->save();    
            }

            $serial->update();

            foreach($stock_vendeur as $value) {
                $value->update();
            }

            $trans->save();
            $transMarge->save();
            $transMargePdc->save();
            $sender_account->update();
            $receiver_account->update();
            $logistiqueAccount->update();
            $pdcAccount->update();


            return response()
                ->json('done');
        }
        catch(AppException $e) {
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

    #FILTER RECRUTEMENT AFROCASH

    public function filterRecrutementAfrocash($pdc,$user,$payState,$state,$margeState) {
        try {

            $data = [];
            $pdraf_users = [];

            $comission = 0;
            $total_ttc = 0;
            $total_marge = 0;

            if($pdc && $user && $payState && $state && $margeState) {

                if(request()->user()->type == 'pdc') {
                    
                    $pdraf_users = request()->user()
                        ->pdrafUsersForList()
                        ->select('id_pdraf')
                        ->groupBy('id_pdraf')
                        ->get();
                }
                else if(request()->user()->type == 'pdraf') {
                    $user = request()->user()->username;
                }
                else {
                    if($pdc != 'all') {
                        $theUser = User::where('username',$pdc)->first();
                        $pdraf_users = $theUser->pdrafUsersForList()->select('id_pdraf')->groupBy('id_pdraf')->get();
                    }
                    else {
                        $pdraf_users = User::select('username')->where('type','pdraf')
                            ->groupBy('username')
                            ->get();
                    }
                }
                

                switch ($user) {
                    case 'all':
                        switch ($state) {
                            case 'all' : 
                                switch ($payState) {
                                    case 'all' : 
                                        switch ($margeState) {
                                            case 'all':

                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_comission_id')
                                                    ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_comission_id')
                                                    ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');
                                                
                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                                
                                            break;
                                            case 'payer':
    
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');
                                                
                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                    

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);


                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }  
                            break;
                            case 'confirme':
                                switch ($payState) {
                                    case 'all' : 
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);

                                            break;
                                            case 'payer':
    
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }  
                            break;
                            case 'annule' :
                                switch ($payState) {
                                    case 'all':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);


                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');
                                                
                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');


                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }
                            break;
                            case 'en_instance':
                                switch ($payState) {
                                    case 'all':
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');


                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);  
                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');


                                                $total_comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                    $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    
                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }
                            break;
                        }       
                    break;
                    default:
                        switch ($state) {
                            case 'all' : 
                                switch ($payState) {
                                    case 'all' : 
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);



                                                $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');


                                                $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);

                                            break;
                                            case 'impayer':
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }  
                            break;
                            case 'confirme':
                                switch ($payState) {
                                    case 'all' : 
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);


                                            break;
                                            case 'payer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }  
                            break;
                            case 'annule' :
                                switch ($payState) {
                                    case 'all':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':

                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }
                            break;
                            case 'en_instance':
                                switch ($payState) {
                                    case 'all':
                                        switch ($margeState) {
                                            case 'all' : 

                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comissionm = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':

                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comissionm = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':

                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comissionm = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');
                                                    
                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');
                                                    
                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = RecrutementAfrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }
                            break;
                        }
                        break;
                    }

                $data = $filterData;
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
                    'created_at'    =>  $created_at->toDateString(),
                    'hour'  =>  $created_at->toTimeString(),
                    'confirm_at'    => $confirm_at ? $confirm_at->toDateTimeString() : null,
                    'remove_at' =>  $remove_at ? $remove_at->toDateTimeString() : null,
                    'pay_at'    =>  $pay_at ? $pay_at->toDateTimeString() : null,
                    'pay_comission_id'  =>  $value->pay_comission_id
                ];

            }

            return response()
                ->json([
                    'all'   =>  $all,
                    'comission' =>  $comission,
                    'marge' =>  $total_marge,
                    'next_url'	=> $data->nextPageUrl(),
					'last_url'	=> $data->previousPageUrl(),
					'per_page'	=>	$data->perPage(),
					'current_page'	=>	$data->currentPage(),
					'first_page'	=>	$data->url(1),
					'first_item'	=>	$data->firstItem(),
					'total'	=>	$data->total()
                ]);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getAllReaboAfrocash(Request $request) {
        try {
            if($request->user()->type == 'admin' || $request->user()->type == 'gcga' || $request->user()->type == 'commercial') {

                // list reabonnement afrocash
                $data = ReaboAfrocash::select()
                    ->orderBy('created_at','desc')
                    ->paginate(100);
            }
            else if($request->user()->type == 'pdraf') {
                $data = ReaboAfrocash::where('pdraf_id',$request->user()->username)
                    ->orderBy('created_at','desc')
                    ->paginate(100);
            }
            else if($request->user()->type == 'pdc') {
                
                $pdraf_users = $request->user()
                    ->pdrafUsersForList()
                    ->select('id_pdraf')
                    ->groupBy('id_pdraf')
                    ->get();
                
                $data = ReaboAfrocash::whereIn('pdraf_id',$pdraf_users)
                    ->orderBy('created_at','desc')
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

                $upgradeState = $value->upgrade()->first() ? $value->upgrade()->first() : null;
                
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
                    'created_at'    =>  $created_at->toDateString(),
                    'hour'  =>  $created_at->toTimeString(),
                    'confirm_at'    => $confirm_at ? $confirm_at->toDateTimeString() : null,
                    'remove_at' =>  $remove_at ? $remove_at->toDateTimeString() : null,
                    'pay_at'    =>  $pay_at ? $pay_at->toDateTimeString() : null,
                    'pay_comission_id'  =>  $value->pay_comission_id,
                    'upgrade_state' =>  $upgradeState
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


        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // get Reabo Afrocash by pdc

    public function filterReaboAfrocash(Request $request ,$pdc, $user,$payState , $state,$margeState) {
        try {
            $data = [];
            $pdraf_users = [];

            $comission = 0;
            $total_ttc = 0;
            $total_marge = 0;

            if($pdc && $user && $payState && $state && $margeState) {

                if($request->user()->type == 'pdc') {
                    
                    $pdraf_users = $request->user()
                        ->pdrafUsersForList()
                        ->select('id_pdraf')
                        ->groupBy('id_pdraf')
                        ->get();
                }
                else if($request->user()->type == 'pdraf') {
                    $user = $request->user()->username;
                }
                else {
                    if($pdc != 'all') {
                        $theUser = User::where('username',$pdc)->first();
                        $pdraf_users = $theUser->pdrafUsersForList()->select('id_pdraf')->groupBy('id_pdraf')->get();
                    }
                    else {
                        $pdraf_users = User::select('username')->where('type','pdraf')
                            ->groupBy('username')
                            ->get();
                    }
                }
                

                switch ($user) {
                    case 'all':
                        switch ($state) {
                            case 'all' : 
                                switch ($payState) {
                                    case 'all' : 
                                        switch ($margeState) {
                                            case 'all':

                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->sum('comission');

                                                $total_ttc = ReaboAfrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_comission_id')
                                                    ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_comission_id')
                                                    ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');
                                                
                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                                
                                            break;
                                            case 'payer':
    
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');
                                                
                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                    

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);


                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }  
                            break;
                            case 'confirme':
                                switch ($payState) {
                                    case 'all' : 
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);

                                            break;
                                            case 'payer':
    
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }  
                            break;
                            case 'annule' :
                                switch ($payState) {
                                    case 'all':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);


                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');
                                                
                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');


                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNotNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }
                            break;
                            case 'en_instance':
                                switch ($payState) {
                                    case 'all':
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');


                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);  
                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');


                                                $total_comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                    $comission = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::whereIn('pdraf_id',$pdraf_users)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    
                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }
                            break;
                        }       
                    break;
                    default:
                        switch ($state) {
                            case 'all' : 
                                switch ($payState) {
                                    case 'all' : 
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_comission = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);



                                                $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');


                                                $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');

                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNotNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNotNull('confirm_at')
                                                ->whereNotNull('pay_at')
                                                ->whereNull('pay_comission_id')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);
                                                
                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);

                                            break;
                                            case 'impayer':
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }  
                            break;
                            case 'confirme':
                                switch ($payState) {
                                    case 'all' : 
                                        switch ($margeState) {
                                            case 'all' : 
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
    
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
    
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);


                                            break;
                                            case 'payer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('confirm_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }  
                            break;
                            case 'annule' :
                                switch ($payState) {
                                    case 'all':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':

                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNotNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }
                            break;
                            case 'en_instance':
                                switch ($payState) {
                                    case 'all':
                                        switch ($margeState) {
                                            case 'all' : 

                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comissionm = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':

                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comissionm = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':

                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comissionm = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'payer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');
                                                    
                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');
                                                    
                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNotNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                    case 'impayer':
                                        switch ($margeState) {
                                            case 'all':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('comission');

                                                $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                ->whereNull('confirm_at')
                                                ->whereNull('remove_at')
                                                ->whereNull('pay_at')
                                                ->orderBy('created_at','desc')
                                                ->sum('montant_ttc');


                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'payer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNotNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                            case 'impayer':
                                                
                                                $filterData = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->paginate(100);

                                                    $comission = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('comission');

                                                    $total_ttc = ReaboAFrocash::where('pdraf_id',$user)
                                                    ->whereNull('confirm_at')
                                                    ->whereNull('remove_at')
                                                    ->whereNull('pay_at')
                                                    ->whereNull('pay_comission_id')
                                                    ->orderBy('created_at','desc')
                                                    ->sum('montant_ttc');

                                                    $total_marge = round(($total_ttc/1.18) * (1.5/100),0);
                                            break;
                                        }
                                    break;
                                }
                            break;
                        }
                        break;
                    }

                $data = $filterData;
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
                    'created_at'    =>  $created_at->toDateString(),
                    'hour'  =>  $created_at->toTimeString(),
                    'confirm_at'    => $confirm_at ? $confirm_at->toDateTimeString() : null,
                    'remove_at' =>  $remove_at ? $remove_at->toDateTimeString() : null,
                    'pay_at'    =>  $pay_at ? $pay_at->toDateTimeString() : null,
                    'pay_comission_id'  =>  $value->pay_comission_id
                ];

            }

            return response()
                ->json([
                    'all'   =>  $all,
                    'comission' =>  $comission,
                    'marge' =>  $total_marge,
                    'next_url'	=> $data->nextPageUrl(),
					'last_url'	=> $data->previousPageUrl(),
					'per_page'	=>	$data->perPage(),
					'current_page'	=>	$data->currentPage(),
					'first_page'	=>	$data->url(1),
					'first_item'	=>	$data->firstItem(),
					'total'	=>	$data->total()
                ]);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    # TOUS LES RECRUTEMENTS AFROCASH

    public function getAllRecrutementAfrocash() {
        try {
            if(request()->user()->type == 'admin' || request()->user()->type == 'gcga') {
                $data = RecrutementAfrocash::select()
                    ->orderBy('created_at','desc')
                    ->paginate();
            }
            else if(request()->user()->type == 'pdraf') {
                
                $data = request()->user()
                    ->recrutementAfrocash()
                    ->orderBy('created_at','desc')
                    ->paginate();
            }
            else if(request()->user()->type == 'pdc') {
                $pdraf_users = request()->user()
                    ->pdrafUsersForList()
                    ->select('id_pdraf')
                    ->groupBy('id_pdraf')
                    ->get();

                $data = RecrutementAfrocash::select()
                    ->orderBy('created_at','desc')
                    ->whereIn('pdraf_id',$pdraf_users)
                    ->paginate();
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
                    'materiel'  =>  $value->serialNumber(),
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
                    'created_at'    =>  $created_at->toDateString(),
                    'hour'  =>  $created_at->toTimeString(),
                    'confirm_at'    => $confirm_at ? $confirm_at->toDateTimeString() : null,
                    'remove_at' =>  $remove_at ? $remove_at->toDateTimeString() : null,
                    'pay_at'    =>  $pay_at ? $pay_at->toDateTimeString() : null,
                    'pay_comission_id'  =>  $value->pay_comission_id,
                    'upgrade_state' =>  null
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
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    # DETAILS VENTE

    public function getVenteDetails($slug) {
        try {
            $data = RecrutementAfrocash::find($slug);
            $data->serial = $data->serialNumber();
            $date = new Carbon($data->created_at);
            $data->date = $date->toDateTimeString();

            $data->marge = round(($data->montant_ttc/1.18) * (1.5/100),0);

            return response()
                ->json($data);
        }
        catch(AppException $e) {
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

                $comission = ReaboAfrocash::whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('comission');

                $total_ttc = ReaboAfrocash::whereNotNull('confirm_at')
                ->whereNull('pay_comission_id')
                ->sum('montant_ttc');
            }
            else if($request->user()->type == 'pdraf') {

                $comission = ReaboAfrocash::whereNull('pay_at')
                    ->whereNotNull('confirm_at')
                    ->where('pdraf_id',$request->user()->username)
                    ->sum('comission');

                $total_ttc = ReaboAfrocash::whereNull('pay_at')
                ->whereNotNull('confirm_at')
                ->where('pdraf_id',$request->user()->username)
                ->sum('montant_ttc');

                
            }
            else if($request->user()->type == 'pdc') {
                
                $pdraf_users = $request->user()->pdrafUsersForList()->select('id_pdraf')->groupBy('id_pdraf')->get();
                
                $comission = ReaboAfrocash::whereIn('pdraf_id',$pdraf_users)
                    ->whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('comission');

                $total_ttc = ReaboAfrocash::whereIn('pdraf_id',$pdraf_users)
                    ->whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('montant_ttc');
                
            }

            $marge = round(($total_ttc/1.18) * (1.5/100),0);

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
    
    # PAIEMENT COMISSION POUR L'HISTORIQUE DE RECRUTEMENT
    public function getComissionToPayRecrutement() {
        try {
            $comission = 0;
            $marge = 0;
            $total = 0;

            $data_comission = [];
            $data_marge = [];

            if(request()->user()->type == 'admin' || request()->user()->type == 'gcga' || request()->user()->type == 'commercial') {
                // list reabonnement afrocash

                $comission = RecrutementAfrocash::whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('comission');

                $total_ttc = RecrutementAfrocash::whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('montant_ttc');
            }
            else if(request()->user()->type == 'pdraf') {

                $comission = RecrutementAfrocash::whereNull('pay_at')
                    ->whereNotNull('confirm_at')
                    ->where('pdraf_id',request()->user()->username)
                    ->sum('comission');

                $total_ttc = RecrutementAfrocash::whereNull('pay_at')
                    ->whereNotNull('confirm_at')
                    ->where('pdraf_id',request()->user()->username)
                    ->sum('montant_ttc');

                
            }
            else if(request()->user()->type == 'pdc') {
                
                $pdraf_users = request()->user()->pdrafUsersForList()->select('id_pdraf')->groupBy('id_pdraf')->get();
                
                $comission = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                    ->whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('comission');

                $total_ttc = RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                    ->whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('montant_ttc');
                
            }

            $marge = round(($total_ttc/1.18) * (1.5/100),0);

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

            // VERIFIER SI CE N'EST PAS DEJA CONFIRME

            if(!is_null($reabo_afrocash->confirm_at)) {
                throw new AppException("Deja Confirmee !");
            }


            $reabo_afrocash->confirm_at = Carbon::now();
            $reabo_afrocash->update();

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

    public function sendPayComissionRequest() {
        try {
            $validation = request()->validate([
                'montant'   =>  'required|numeric|min : 10000',
                'password_confirmation'  => 'required|string',
            ],[
                'required'  =>  '`:attribute` requis !',
                'min'   =>  'Le montant minimum requis est de : 100,000 GNF'
            ]);
                // verification de la validite du mot de passe
            if(!Hash::check(request()->password_confirmation,request()->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }


            $comission = 0;

            $comission += request()->user()->reaboAfrocash()
                ->whereNull('remove_at')
                ->whereNull('pay_at')
                ->whereNotNull('confirm_at')
                ->sum('comission');

            $comission += request()->user()->recrutementAfrocash()
                ->whereNull('remove_at')
                ->whereNull('pay_at')
                ->whereNotNull('confirm_at')
                ->sum('comission');

            $reabo_afrocash = request()->user()->reaboAfrocash()
                ->whereNull('remove_at')
                ->whereNull('pay_at')
                ->whereNotNull('confirm_at')
                ->get();

            $recrutementAfrocash = request()->user()->recrutementAfrocash()
                ->whereNull('remove_at')
                ->whereNull('pay_at')
                ->whereNotNull('confirm_at')
                ->get();

            if($comission <= 0) {
                throw new AppException("Vous n'avez pas de comission !");
            }

            if($comission != request()->input('montant')) {
                throw new AppException("Erreur ! Ressayez");
            }

            $receiver_account = request()->user()->afroCash()->first();

            $sender_user = request()->user()->pdcUser()->usersPdc();
            $sender_account = $sender_user->afroCash('semi_grossiste')->first();

            $receiver_account->solde += $comission;
            $sender_account->solde -= $comission;

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $sender_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $comission;
            $trans->motif = "Paiement_Comission";
            

            

            $sender_account->update();
            $receiver_account->update();
            $trans->save();

            foreach($reabo_afrocash as $value) {
                $value->pay_at = Carbon::now();
                $value->update();
            }

            foreach($recrutementAfrocash as $value) {
                $value->pay_at = Carbon::now();
                $value->update();
            }

            $n = $this->sendNotification(
                "Paiement Comission" ,
                "Reception de ".number_format($comission)." GNF de la part de ".$sender_user->localisation,
                request()->user()->username
                );
            $n->save();

            $n = $this->sendNotification(
                "Paiement Comission" ,
                "Envoi de ".number_format($comission)." GNF a :".request()->user()->localisation,
                $sender_user->username
                );
            $n->save();

            $n = $this->sendNotification(
                "Paiement Comission" ,
                "Envoi de : ".number_format($comission)." GNF de la part de ".$sender_user->localisation.", a : ".request()->user()->localisation,
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

    public function sendRequestPdrafAffectation(Request $request,User $u,ReseauxPdc $rpdc , PayCommission $pay) {
        try {
            $validation = $request->validate([
                'pdraf_id'  =>  'required|exists:users,username',
                'pdc_origine'   =>  'required|exists:users,username',
                'pdc_destination'   =>  'required|exists:users,username',
                'password'  =>  'required|string'
            ],[
                'required'  =>  'champ(s) `:attribute` requis!'
            ]);

            // validite du mot de passe
            if(!Hash::check($request->input('password'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            $pdraf_user = $u->where('username',$request->input('pdraf_id'))->first();
            $pdc_destination = $u->where('username',$request->input('pdc_destination'))->first();

            $reabo_afrocash = $pdraf_user->reaboAfrocash()
                ->whereNull('remove_at')
                ->whereNull('pay_at')
                ->whereNotNull('confirm_at');


            $reseaux_pdc = $rpdc->where('id_pdc',$request->input('pdc_origine'))
                ->where('id_pdraf',$request->input('pdraf_id'))
                ->first();

            if(!$reseaux_pdc) {
                // le pdraf n'appartient pas au pdc
                throw new AppException("Erreur de correspondance !");
            }
            
            $reseaux_pdc->id_pdc = $request->input('pdc_destination');

            if($reabo_afrocash->get()->count() > 0) {
                
                // il y a des commission a se faire payer pour le pdraf

                $comission = $reabo_afrocash->sum('comission');

                $receiver_account = $pdraf_user->afroCash()->first();

                $sender_user = $pdraf_user->pdcUser()->usersPdc();
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

                foreach($reabo_afrocash->get() as $value) {
                    $value->pay_at = Carbon::now();
                    $value->save();
                }

                $n = $this->sendNotification(
                    "Paiement Comission" ,
                    "Reception de ".number_format($comission)." GNF de la part de ".$sender_user->localisation,
                    $pdraf_user->username
                    );
                $n->save();

                $n = $this->sendNotification(
                    "Paiement Comission" ,
                    "Envoi de ".number_format($comission)." GNF a :".$pdraf_user->localisation,
                    $sender_user->username
                    );
                $n->save();

                $n = $this->sendNotification(
                    "Paiement Comission" ,
                    "Envoi de : ".number_format($comission)." GNF de la part de ".$sender_user->localisation.", a : ".$pdraf_user->localisation,
                    'admin'
                    );
                $n->save();
            }

            // get all reabo afrocash
            $pdc_origine = $u->where('username',$request->input('pdc_origine'))->first();

            $reaboAfrocash = ReaboAfrocash::where('pdraf_id',$pdraf_user->username)
                ->whereNotNull('confirm_at')
                ->whereNull('pay_comission_id');
            
            if($reaboAfrocash->get()->count() > 0) {
                // il ya des comission a se faire payer par le pdc

                $pay->id = "pay_comission_".$pdc_origine->username.time();
                $tmp = $pay->id;
                
                $pay->montant = 0;

                $marge = round(($reaboAfrocash->sum('montant_ttc')/1.18) * (1.5/100),0);
                $pay->montant = $reaboAfrocash->sum('comission') + $marge;

                // EFFECTUER LA TRANSACTION
                $_receiver_account = $pdc_origine->afroCash('semi_grossiste')->first();
                $_sender_account = Credit::find('afrocash');
    
                $_receiver_account->solde += $pay->montant;
                $_sender_account->solde -= $pay->montant;
    
    
                $_trans = new TransactionAfrocash;
                $_trans->compte_credite = $_receiver_account->numero_compte;
                $_trans->montant = $pay->montant;
                $_trans->motif = "Comission_Pdc_Afrocash";
    
                // 
    
                $pay->pay_at = Carbon::now();
                $pay->status = 'validated';
    
                $amount = $pay->montant;
    
                $_receiver_account->save();
                $_sender_account->save();
                $_trans->save();
    
                $pay->save();
    
                foreach($reaboAfrocash->get() as $value) {
                    $value->pay_comission_id = $tmp;
                    $value->save();
                }

                $_user = User::where('type','gcga')->get();
                foreach($_user as $value) {
                    $n = $this->sendNotification("Paiement Commission","Paiement d'un montant de : ".$amount." effectue pour :".$pdc_origine->localisation,$value->username);
                    $n->save();
                }
    
                $n = $this->sendNotification("Paiement Commission","Paiement d'un montant de : ".$amount." effectue pour :".$pdc_origine->localisation,$pdc_origine->username);
                $n->save();
    
                $n = $this->sendNotification("Paiement Commission","Paiement d'un montant de : ".$amount." effectue pour :".$pdc_origine->localisation,'admin');
                $n->save();
    
                $n = $this->sendNotification("Paiement Commission","Paiement d'un montant de : ".$amount." effectue pour :".$pdc_origine->localisation,'root');
                $n->save();
            }
            
            ReseauxPdc::where('id_pdc',$request->input('pdc_origine'))
                ->where('id_pdraf',$request->input('pdraf_id'))
                ->update([
                    'id_pdc'    =>  $request->input('pdc_destination')
                ]);
            
            return response()
                ->json('done');
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function sendUpgradeAfrocash(Request $request) {
        try {
            $validation = $request->validate([
                'serial_number' =>  'required|string|max:14|min:14|regex : /(^([0-9]+)?$)/',
                'new.formule'   =>  'required|string|exists:formule,nom',
                'old.formule'   =>  'required|string|exists:formule,nom',
                'telephone_client'  =>  'required|string|max:9|min:9',
                'montant_ttc'   =>  'required|numeric',
                'comission' =>  'required|numeric',
                'password_confirmation' =>  'required|string',
                'duree' =>  'required|numeric|max:24|min:1'
            ],[
                'required'  =>  '`:attribute` requis !',
            ]);

            // validation du mot de passe

            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            // verifier la disponibilite du montant

            $sender_account = $request->user()->afroCash()->first();

            if($request->input('montant_ttc') >  $sender_account->solde) {
                throw new AppException("Montant Indisponible !");
            }

            $reabo = new ReaboAfrocash;
            $reabo->generateId();
            $reabo->serial_number = $request->input('serial_number');
            $reabo->formule_name = $request->input('new.formule');
            $reabo->duree = $request->input('duree');
            $reabo->telephone_client = $request->input('telephone_client');
            $reabo->montant_ttc = $request->input('montant_ttc');
            $reabo->comission = $request->input('comission');
            $reabo->pdraf_id = $request->user()->username;

            // enregistrement de upgrade

            $upgrade_reabo = new UpgradeReaboAfrocash;
            $upgrade_reabo->from_formule = $request->input('old.formule');
            $upgrade_reabo->to_formule = $request->input('new.formule');
            $upgrade_reabo->id_reabo_afrocash = $reabo->id;

            // 

            $data_options = [];

            if($request->input('new.option')) {

                foreach($request->input('new.option') as $key => $value) {
                    if($value && $value != "") {
                        $data_options[$key] = new OptionReaboAfrocash;
                        $data_options[$key]->id_reabo_afrocash = $reabo->id;
                        $data_options[$key]->id_option = $value && $value != "" ? $value : null;
                    }
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
            $trans->motif = "Upgrade_Afrocash";

            $reabo->save();
            
            if(count($data_options) > 0) {
                foreach($data_options as $value) {
                    $value->save();
                }
            }
            

            $upgrade_reabo->save();
            $sender_account->save();
            $receiver_account->save();
            $trans->save();

            return response()
                ->json('done');
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // REACTIVATION MATERIEL REQUEST

    public function reactivationMaterielRequest(Request $request,ReactivationMateriel $rm) {
        try {
            $validation = $request->validate([
                'serial_number' =>  'required|string|min:14|max:14',
                'password_confirmation' =>  'required|string'
            ],
            [
                'required'  =>  '`:attribute` requis !'
            ]);

            // validation du mot de passe

            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            // constraint on materiel number

            $test = $rm->whereDay('created_at',Date('d'))
                ->where('serial_number',$request->input('serial_number'))
                ->first();
            
            if($test) {
                throw new AppException("Cette requete a deja ete envoye! Veuillez ressayer ulterieurement");
            }

            // traitement
            $rm->serial_number = $request->input('serial_number');
            $rm->pdraf_id = $request->user()->username;
            
            $rm->save();

            return response()
                ->json('done');
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // HISTORIQUE DE REACTIVATION MATERIELS
    public function getReactivationList(ReactivationMateriel $rm) {
        try {
            if(request()->user()->type != 'admin') {
                $data = request()->user()->reactivationMateriel()
                    ->orderBy('created_at','desc')
                    ->paginate();
            }
            else {

                $data = $rm->orderBy('created_at','desc')
                    ->paginate();
            }

            $count = $rm->whereNull('confirm_at')
                ->whereNull('remove_at')
                ->count('serial_number');

            $all = [];
            foreach($data as $key => $value) {
                $all[$key] = [
                    'id'    =>  $value->id,
                    'materiel'  =>  $value->serial_number,
                    'pdraf' =>  $value->pdrafUser()->localisation,
                    'confirm_at'    =>  $value->confirm_at,
                    'remove_at' =>  $value->remove_at,
                    'created_at'    =>  $value->created_at
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
                    'total'	=>	$data->total(),
                    'count' =>  $count
                ]);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function changeStatusReactivationMateriel(Request $request) {
        try {
            $validation = $request->validate([
                'id'    =>  'required|exists:reactivation_materiels,id',
                'state' =>  'required|string'
            ]);

            $rm = ReactivationMateriel::find($request->input('id'));

            if($request->input('state') == 'confirm') {
                // 
                $rm->confirm_at = Carbon::now();
            }
            else if($request->input('state') == 'delete') {
                $rm->remove_at = Carbon::now();
            }
            else {
                throw new AppException("Erreur");
            }

            $rm->save();

            return response()
                ->json('done');
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    # CONFIRM RECRUTEMENT AFROCASH

    public function confirmRecrutementAfrocash() {
        try {
            $validation = request()->validate([
                'id'    =>  'required|exists:recrutement_afrocashes,id',
            ]);

            $recrutement = RecrutementAfrocash::findOrFail(request()->id);
            if(!is_null($recrutement->remove_at) && !is_null($recrutement->confirm_at)) {
                throw new AppException("Confirmation Impossible !");
            }
            // // VERIFIER SI CE N'EST PAS DEJA CONFIRME

            if(!is_null($recrutement->confirm_at)) {
                throw new AppException("Deja confirmee !");
            }

            $recrutement->confirm_at = Carbon::now();
            $recrutement->update();

            return response()
                ->json('done');
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    # GET AFROCASH COMISSION 
    public function getAfrocashComission() {
        try {

            $comission = 0;

            if(request()->user()->type == 'pdraf') {

                $comission = request()->user()->reaboAfrocash()
                    ->whereNull('remove_at')
                    ->whereNull('pay_at')
                    ->whereNotNull('confirm_at')
                    ->sum('comission');

                $comission += request()->user()
                    ->recrutementAfrocash()
                    ->whereNull('remove_at')
                    ->whereNull('pay_at')
                    ->whereNotNull('confirm_at')
                    ->sum('comission');
            }
            else if(request()->user()->type == 'pdc') {
                
                $pdraf_users = request()->user()
                    ->pdrafUsersForList()
                    ->select('id_pdraf')
                    ->groupBy('id_pdraf')
                    ->get();
                
                $ttc = ReaboAfrocash::whereIn('pdraf_id',$pdraf_users)
                    ->whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('montant_ttc');

                $ttc += RecrutementAfrocash::whereIn('pdraf_id',$pdraf_users)
                    ->whereNotNull('confirm_at')
                    ->whereNull('pay_comission_id')
                    ->sum('montant_ttc');

                $comission = round(($ttc/1.18) * (1.5/100),0);

            }
            
            return response()
                ->json($comission);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
