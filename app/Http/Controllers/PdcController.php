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
use App\MakePdraf;
use App\PayCommission;
use App\ReaboAfrocash;
use App\Credit;
use App\Kits;
use App\CommandAfrocash;
use App\LivraisonAfrocash;
use App\ReaboAfrocashSetting;

use App\Traits\Similarity;
use App\Traits\Afrocashes;


use Carbon\Carbon;


use Illuminate\Support\Str;


class PdcController extends Controller {

    use Similarity;
    use Afrocashes;

public function getCreateRequest(Request $request , MakePdraf $m) {
    try {
        return response()
            ->json($m->where('pdc_user_id',$request->user()->username)
            ->orderBy('created_at','desc')
            ->get());
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
}

public function getListPdraf(Request $request) {
    try {
        $tmp = $request->user()->pdrafUsers();
        
        return response()
            ->json($tmp);
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
}



public function SendPdrafAddRequest(Request $request) {
    try {

        $validation = $request->validate([
            'email' =>  'unique:make_pdraf,email',
            'agence'    =>  'required|string|unique:make_pdraf,agence',
            'telephone' =>  'required|string|unique:make_pdraf,telephone|min:9|max:9',
            'adresse'   =>  'required|string|',
            'password_confirmation' => 'required'
        ],[
            'required'  =>  '`:attribute` requis !',
            'unique'    =>  '`:attribute` existe deja dans le systeme',
        ]);
            
        // validation du password

        if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
            throw new AppException("Mot de passe invalide !");
        }

        $req = new MakePdraf;
        $req->email = $request->input('email');
        $req->telephone = $request->input('telephone');
        $req->agence = $request->input('agence');
        $req->adresse = $request->input('adresse');
        $req->pdc_user_id = $request->user()->username;

        $req->save();

        $n = $this->sendNotification(
            "Creation Pdraf",
            "Vous avez envoye une demande de creation de pdraf !",
            $request->user()->username
        );

        $n->save();

        $n = $this->sendNotification(
            "Creation Pdraf",
            "Une demande de creation de Pdraf en attente de traitement !",
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

public function getPdrafSoldes(Request $request) {
    try {
        $pdc_user = $request->user();
        $pdrafs = $pdc_user->pdrafUsers();

        $data = [];
        foreach($pdrafs as $key => $value) {
            $_tmp = $value->usersPdraf();
            $data[$key] = [
                'utilisateur'   =>  $_tmp->localisation,
                'solde' =>  $_tmp->afroCash()->first()->solde
            ];
        }

        return response()
            ->json($data);
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
}

public function sendTransaction(Request $request) {
    try {
        $validation = $request->validate([
            'pdraf_id'  =>  'required|string|exists:users,username',
            'montant'   =>  'required|numeric|min:100000',
            'password_confirmation' =>  'required|string'
        ],[
            'required'  =>  '`:attribute` requis!',
            'exists'    =>  '`:attribute` n\'existe pas dans le systeme!',
            'min'   =>  'Le `:attribute` minimum est de : 100,000 !'
        ]);

        // verification de la validite du mot de passe
        if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
            throw new AppException("Mot de passe invalide !");
        }

        // traitement de la transaction 
        #recuperation des infos de compte du pdraf
        $pdraf_user = User::where('username',$request->input('pdraf_id'))
            ->where('type','pdraf')
            ->first();

        $pdraf_account = $pdraf_user->afroCash()->first();

        #infos compte du pdc
        $pdc_account = $request->user()->afroCash('semi_grossiste')->first();

        // verification de la disponibilite du montant chez le pdc

        if($request->input('montant') > $pdc_account->solde) {
            throw new AppException("Montant indisponible !");
        }

        #debit et credit des comptes
        $pdraf_account->solde += $request->input('montant');
        $pdc_account->solde -= $request->input('montant');

        // enregistrement de la transaction
        $trans = new TransactionAfrocash;
        $trans->compte_debite = $pdc_account->numero_compte;
        $trans->compte_credite = $pdraf_account->numero_compte;
        $trans->montant = $request->input('montant');
        $trans->motif = "Depot Afrocash";

        $pdraf_account->save();
        $pdc_account->save();
        $trans->save();

        #envoi des notifications

        $n = $this->sendNotification(
            "Depot Afrocash" ,
            "Depot de  ".number_format($request->input('montant'))." GNF effectué par :".$request->user()->localisation." sur le compte de :".$pdraf_user->localisation,
            'admin'
        );

        $n->save();

        $n = $this->sendNotification(
            "Depot Afrocash" ,
            "Reception de ".number_format($request->input('montant'))." GNF de la part de ".$request->user()->localisation,
            $pdraf_user->username);

        $n->save();

        $n = $this->sendNotification(
            "Depot Afrocash",
            "Vous avez effectue un depot de ".number_format($request->input('montant'))." GNF pour ".$pdraf_user->localisation,
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

        if($request->input('montant') > $sender_account->solde) {
            throw new AppException("Montant indisponible !");
        }

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

public function getSoldePdc(Request $request) {
    try {
        return response()
            ->json($request->user()->afroCash('semi_grossiste')->first());
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
// SEND PAY COMISSION REQUEST

    public function sendPayComissionRequest(Request $request  , PayCommission $pay) {
        try {
            $validation = $request->validate([
                'montant'   =>  'required|numeric|min : 10000',
                'password_confirmation'  => 'required|string',
            ],[
                'required'  =>  '`:attribute` requis !',
                'min'   =>  'Le montant minimum requis est de : 1,000,000 GNF'
            ]);

            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            // get all reabo afrocash
            $data_pdraf = $request->user()->pdrafUsersForList()->select('id_pdraf')->groupBy('id_pdraf')->get();

            $reaboAfrocash = ReaboAfrocash::whereIn('pdraf_id',$data_pdraf)
                ->whereNotNull('confirm_at')
                ->whereNull('pay_comission_id')
                ->get();
            
            
            if($reaboAfrocash->count() <= 0 ) {
                throw new AppException("Vous n'avez pas de comission !");
            }
            
            $pay->id = "pay_comission_".$request->user()->username.time();
            $tmp = $pay->id;
            
            $pay->montant = 0;

            foreach($reaboAfrocash as $key => $value) {
                $marge = round(($value->montant_ttc/1.18) * (1.5/100),0);
                $pay->montant += ($value->comission + $marge);
            }

            if($pay->montant < 10000 ) {
                throw new AppException("Vous devez avoir au moins 10,000 GNF !");
            }

            // EFFECTUER LA TRANSACTION
            $receiver_account = $request->user()->afroCash('semi_grossiste')->first();
            $sender_account = Credit::find('afrocash');

            $receiver_account->solde += $pay->montant;
            $sender_account->solde -= $pay->montant;


            $trans = new TransactionAfrocash;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = $pay->montant;
            $trans->motif = "Comission_Pdc_Afrocash";

            // 

            $pay->pay_at = Carbon::now();
            $pay->status = 'validated';


            $amount = $pay->montant;

            $receiver_account->save();
            $sender_account->save();
            $trans->save();

            $pay->save();

            foreach($reaboAfrocash as $value) {
                $value->pay_comission_id = $tmp;
                $value->save();
            }



            $_user = User::where('type','gcga')->get();
            foreach($_user as $value) {
                $n = $this->sendNotification("Paiement Commission","Paiement d'un montant de : ".$amount." effectue pour :".$request->user()->localisation,$value->username);
                $n->save();
            }

            $n = $this->sendNotification("Paiement Commission","Paiement d'un montant de : ".$amount." effectue pour :".$request->user()->localisation,$request->user()->username);
            $n->save();

            $n = $this->sendNotification("Paiement Commission","Paiement d'un montant de : ".$amount." effectue pour :".$request->user()->localisation,'admin');
            $n->save();

            $n = $this->sendNotification("Paiement Commission","Paiement d'un montant de : ".$amount." effectue pour :".$request->user()->localisation,'root');
            $n->save();
            
            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getAllPdc() {
        try {
            $pdc_users = User::where('type','pdc')->get();
            $data = [];
            foreach($pdc_users as $key => $value) {
                $data[$key] = [
                    'user'  =>  $value
                ];
            }
            return response()
                ->json($data);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // COMMAND GET DATA

    public function newCommandGetData(Kits $kit) {
        try {

            return response()
                ->json($kit->all());
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // ENVOI DE LA COMMANDE MATERIEL POUR PDC

    // UNICITE D'UNE COMMANDE NON CONFIRMEE
    public function isExistCommandAfrocash() {
        try {
            $response = request()->user()->commandAfrocash()
                ->where('state',false)
                ->first();

                if($response) {
                    return $response;
                }
                return false;
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    # GENERER UN ID DE LA COMMANDE 

    public function generateIdCommandAfrocash() {
        do {
            $id_commande = mt_rand(1000,9999).Str::upper(Str::random(10));

            $response = request()->user()->commandAfrocash()
                    ->where('id_commande',$id_commande)
                    ->first();
        }
        while($response);

        return $id_commande;
    }

    public function sendCommandAfrocash() {
        try {
            $validation = request()->validate([
                'prix_achat'    =>  'required',
                'reference_material'    =>  'required|exists:kits,slug',
                'quantite'  =>  'required|min:1'
            ],[
                'required'  =>  '`:attribute` requi(s) !'
            ]);

            // SOLDE PDC
            $pdc_account = request()->user()->afroCash('semi_grossiste')->first();

            // VERIFICATION DE LA DISPONIBILITE DU MONTANT DANS LE COMPTE PDC
            if($pdc_account->solde < request()->prix_achat) {
                throw new AppException("Solde indisponible !");
            }

            // VERIFIER L'EXISTENCE D'UNE COMMAND NON CONFIRMEE

            if($this->isExistCommandAfrocash()) {
                throw new AppException("Une commande existe deja ! Ressayez plus tard ...");
            }

            // DETAILS DE L'ARTICLE

            $kit = Kits::find(request()->reference_material);
            $terminal = $kit->getTerminalReference();
            $accessoire = $kit->getAccessoryReference();

            // CREATION DE LA COMMANDE AFROCASH

            $idCommande = $this->generateIdCommandAfrocash();

            $commandAfrocash = [];

            $commandAfrocash[0] = new CommandAfrocash;
            $commandAfrocash[0]->id_commande = $idCommande;
            $commandAfrocash[0]->user_id = request()->user()->username;
            $commandAfrocash[0]->produit_id = $terminal->reference;
            $commandAfrocash[0]->quantite = request()->quantite;
            $commandAfrocash[0]->quantite_a_livrer = request()->quantite;

            foreach($accessoire as $key => $value) {
                $tmp = new CommandAfrocash;
                $tmp->user_id = request()->user()->username;
                $tmp->produit_id = $value->reference;
                $tmp->quantite = request()->quantite;
                $tmp->quantite_a_livrer = request()->quantite;
                $tmp->id_commande = $idCommande;

                array_push($commandAfrocash,$tmp);
            }

            // CREATION DE LA LIVRAISON
            $livraisonAfrocash = new LivraisonAfrocash;
            $livraisonAfrocash->command_afrocash = $idCommande;
            $livraisonAfrocash->generateConfirmCode();

            // TRANSACTION AFROCASH

            $reabo_afrocash_setting = ReaboAfrocashSetting::all()->first();

            if(!$reabo_afrocash_setting) {
                throw new AppException("Parametre Reabo non defini , contactez l'administrateur");
            }

            $receiver_user = User::where('username',$reabo_afrocash_setting->user_to_receive)->first();
            $receiver_account = $receiver_user->afroCash()->first();

            $pdc_account->solde -= request()->prix_achat;
            $receiver_account->solde += request()->prix_achat;

            

            // ENREGISTREMENT DE LA TRANSACTION

            $trans = new TransactionAfrocash;
            $trans->compte_debite = $pdc_account->numero_compte;
            $trans->compte_credite = $receiver_account->numero_compte;
            $trans->montant = request()->prix_achat;
            $trans->motif = "Command_Materiel_Afrocash";

            // VALIDATION DE LA REQUETE

            foreach($commandAfrocash as $value) {
                $value->save();
            }

            $livraisonAfrocash->save();

            $pdc_account->update();
            $receiver_account->update();
            $trans->save();


            // ENREGISTREMENT DES NOTIFICATIONS


            $n = $this->sendNotification(
                "Command Materiel Afrocash",
                "Vous avez envoye une commande Materiel !",
                request()->user()->username
            );

            $n->save();
            
            $n = $this->sendNotification(
                "Command Materiel Afrocash",
                "Une commande Materiel en attente de validation !",
                $receiver_user->username
            );

            $n->save();

            $n = $this->sendNotification(
                "Command Materiel Afrocash",
                "Une commande Materiel en attente de validation !",
                'admin'
            );

            $n->save();
            
            $n = $this->sendNotification(
                "Command Materiel Afrocash",
                "Une commande Materiel en attente de validation !",
                'root'
            );
    
            $n->save();

            return response()
                ->json('done');
        }
        catch(AppException  $e){
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

   
}
