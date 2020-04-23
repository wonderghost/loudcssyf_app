<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserEditRequest;
use App\Traits\Similarity;
use App\Traits\Afrocashes;
use App\Traits\Livraisons;
use App\Traits\Rapports;
use App\Traits\Cga;
use App\User;
use App\Produits;
use App\Agence;
use App\Depots;
use App\Afrocash;
use App\RavitaillementDepot;
use App\CgaAccount;
use App\RexAccount;
use App\Formule;
use App\Option;
use App\Http\Requests\FormuleRequest;
use App\Http\Requests\OptionRequest;
use App\Http\Requests\RapportRequest;
use App\RapportVente;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\StockVendeur;
use App\StockPrime;
use App\Exemplaire;
use App\Credit;
use App\TransactionCreditCentral;
use App\TransactionAfrocash;
use App\Exceptions\AppException;
use App\Promo;
use App\CommandMaterial;
use App\Livraison;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Events\AfrocashNotification;
use App\Notifications;
use App\TransfertMateriel;
use App\TransfertSerial;
use App\DeficientMaterial;

class AdminController extends Controller
{
    //
    use Similarity;
    use Afrocashes;
    use Rapports;
    use Cga;
    use Livraisons;

    public function emailTest() {
      return view('emails.annulation-saisie');
    }

    // TABLEAU DE BORD
    public function dashboard(Request $request) {
      // $n = new Notifications;
      // $n->makeId();
      // $n->titre = "Notification de Connection";
      // $n->description = "Bonjour ".$request->user()->localisation;
      // $n->vendeurs = $request->user()->username;
      // $n->save();
      // broadcast(new AfrocashNotification($n ,$request->user()));
      // event(new AfrocashNotification("bonjour"));
      return view('admin.dashboard');
    }
    // RECOUVREMENT 
    public function recourvementIndex() {
      return view('recouvrement.operations');
    }
    // etat du depot central
    public function etatDepotCentral() {
      return view('admin.depot-central');
    }
    // recuperation etat du depot Central
    public function getEtatDepotCentral(Request $request , Produits $p) {
      //
      return response()->json($p->all());
    }
    // VERIFIER SI LE NUMEROD N'EXISTE PAS EN DB
    public function isExistNumeroCga($numero) {
        $temp = CgaAccount::select()->where('numero',$numero)->first();

        if($temp) {
            return $temp;
        }

        return false;
    }
    public function isExistNumeroRex($numero) {
        $temp = RexAccount::select()->where('numero',$numero)->first();

        if($temp) {
            return $temp;
        }

        return false;
    }

    // CREATION DE COMPTE
    public function createAccountCredit($vendeur=null,$account='cga') {
        if($account!=='' && $account =='cga' ) {
            // COMPTE CGA
            $cga = new CgaAccount;
            $cga->vendeur = $vendeur;
            do {
                $cga->numero = mt_rand(0000001,9999999);
            } while ($this->isExistNumeroCga($cga->numero));
            $cga->save();

        } else if($account!=='' && $account =='rex') {
            // COMPTE REX
            $rex = new RexAccount;
            do {
                $rex->numero = mt_rand(0000001,9999999);
            } while ($this->isExistNumeroRex($rex->numero));
            $rex->save();
            return $rex->numero;
        } else {
            return redirect('/admin/add-user')->with('_errors',"Erreur,Ressayez!");
        }
    }

    // AJOUTER UN USER
    public function getFormUser() {
    	return view('admin.add-user');
    }
    // LIST DES USERS
    public function listUser() {
    	return view('admin.list-users');
    }

    public function getListUsers(Request $request) {
      try {
        $users = User::where('type','<>','admin')->orderBy('localisation','desc')->get();
        $userCollection = collect([]);
        foreach ($users as $key => $element) {
          $userCollection->prepend($element->only(['username','type','email','phone','localisation','status']));
        }
        return response()->json($userCollection);
      } catch (AppException $e) {
        header("Unprocessable entity",true,422);
        die(json_encode($e->getMessage()));
      }

    }

    public function addUser(UserRequest $request) {
      try {

        // return response()->json($request);
        // die();
        $user = new User;
      	$agence = new Agence;
      	$user->email = $request->input('email');
      	$user->phone = $request->input('phone');
      	$user->type = $request->input('type');
      	$user->password = bcrypt("Loudcssyf");
      	$user->localisation = $request->input('localisation');

      	if($request->input('type') == 'v_da') {
      		//ajout d'un distributeur agree

      		do {
  	    		$user->username = 'DA-'.mt_rand(1000,9999);
  	    	} while ($this->isExistUsername ($user->username));

      		if($temp = $this->isExistAgence($request->input('societe'))) {
      			// agence existante
      			$user->agence = $temp->reference;
      		} else {
      			// une nouvelle agence a enregistree
      			do{
      				$agence->reference = 'AG-'.mt_rand(1000,9999);
      			}while($this->isExistAgenceRef($agence->reference));

      			$agence->societe = $request->input('societe');
      			$agence->rccm = $request->input('rccm');
      			$agence->adresse = $request->input('adresse');
      			$agence->ville = $request->input('ville');
                  $agence->num_dist = $request->input('num_dist');
      			$user->agence = $agence->reference;
                  // dd($user->username);
      			$agence->save();
      		}
                  // die();
      			$user->save();
            $this->createAccountCredit($user->username,'cga');
            $this->newAccount($user->username);
      			return response()->json('done');
      	} else {
      		// vendeurs standart
              if($user->type !== 'v_standart') {
                  $user->localisation = 'local'.time();
              }

      		do {
  	    		$user->username = 'LS-'.mt_rand(1000,9999);
  	    	} while ($this->isExistUsername ($user->username));

      		if(!$temp = $this->isExistAgence($request->input('societe'))) {
      			// On creer pour la premiere fois
      			$agence->societe = $request->input('societe');
      			$agence->rccm = $request->input('rccm');
      			$agence->adresse = $request->input('adresse');
      			$agence->ville= $request->input('ville');

      			do{
      				$agence->reference = 'AG-'.mt_rand(1000,9999);
      			} while($this->isExistAgenceRef($agence->reference));

      			$user->agence = $agence->reference;
      			$agence->save();
      		} else {
      			// elle existe deja
      			$user->agence = $temp->reference;
      		}
              if($user->type == 'v_standart') {
                  $user->rex = $this->createAccountCredit(NULL,'rex');
                  $user->save();
                  $this->createAccountCredit($user->username,'cga');
                  $this->newAccount($user->username,'semi_grossiste');
                  $this->newAccount($user->username);
              } else if($user->type == 'logistique') {
                $user->save();
                $this->newAccount($user->username);
              } else {
                  $user->save();
              }
          //
    	}
      return response()->json('done');
    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }
  }

    public function isExistUsername ($temp) {
    	$users = User::select()->where('username',$temp)->first();
    	if($users) {
    		return $users;
    	}
    	return false;
    }

    public function isExistAgence($temp) {
    	$agence = Agence::select()->where('societe',$temp)->first();
    	if($agence) {
    		return $agence;
    	}
    	return false;
    }

    public function isExistAgenceRef($ref) {
    	$temp = Agence::select()->where('reference',$ref)->first();
    	if($temp) {
    		return $temp;
    	}
    	return false;
    }

    // Edit users
    public function editUser($username) {
        $user = User::select()->where('username',$username)->first();
        $agence = Agence::select()->where('reference',$user->agence)->first();
        return view('admin.edit-user')->withUtilisateur($user)->withAgence($agence);
    }
    // envoi du formulaire de modification
    public function makeEditUser($username,UserEditRequest $request) {
        $user = User::select()->where('username',$username)->first();
        // verifier si l'email n'est pas repete
        if(!User::where("email",$request->input('email'))->where('username',$username)->first() && User::where('email',$request->input("email"))->first()) {
          return back()->with("_errors","Adresse email existante!");
        }
        // verifier si la localisation exist deja
        if(!User::where('localisation',$request->input("localisation"))->where('username',$username)->first() && User::where("localisation",$request->input('localisation'))->first()) {
          return back()->with("_errors","Localisation existante!");
        }
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->localisation = $request->input('localisation');

        Agence::select()->where('reference',$user->agence)->update([
            'adresse' => $request->input('adresse'),
            'ville' => $request->input('ville'),
            'rccm' => $request->input('rccm'),
            'societe' => $request->input('societe'),
            'num_dist' => $request->input('num_dist')
        ]);

        $user->save();
        return redirect('/admin/edit-users/'.$username)->with('success',"Modification reussi!");
    }

    public function blockUser(Request $request) {
      try {
          $user = User::select()->where('username',$request->input('ref'))->first();
          $user->status = 'blocked';
          $user->save();
          return response()->json('done');
      } catch (AppException $e) {
          header("Erreur!",true,422);
          die(jsone_encode($e->getMessage()));
      }
    }
    public function unblockUser(Request $request) {
      try {
        $user = User::select()->where('username',$request->input('ref'))->first();
        $user->status = 'unblocked';
        $user->save();
        return response()->json('done');
      }
      catch (AppException $e) {
        header("Erreur!",true,422);
        die(json_encode($e->getMessage()));
      }
    }
    // liste de tous les depots
    public function listDepot() {
        return view('logistique.list-depot');
    }
    //
    public function formule() {
        $all = Formule::all();
        $options = Option::all();
        return view('admin.formule')->withFormule($all)->withOptions($options);
    }
    //
    public function addFormule(FormuleRequest $request) {
        $formule = new Formule;
        $formule->nom = $request->input('nom');
        $formule->prix = $request->input('prix');
        $formule->save();
        return redirect('/admin/formule')->with('success',"Formule ajouté!");
    }

    //
    public function addOptionFormule(OptionRequest $request) {
        $options = new Option;
        $options->nom = $request->input('nom');
        $options->prix = $request->input('prix');
        $options->save();
        return redirect('/admin/formule')->with('success',"Option ajoutée!");
    }

    //
    public function operationAfrocash() {
      return view('admin.afrocash-credit');
    }

    public function historiqueApport() {
      try {
          $apports = TransactionCreditCentral::where('type','apport')->orderBy('created_at','desc')->get();
          return response()
            ->json($apports);
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
    }

    public function historiqueDepenses() {
      try{
          $depenses = TransactionCreditCentral::where('type','depense')->orderBy('created_at','desc')->get();
          return response()
            ->json($depenses);
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
    }

    //
    public function apportCapital(Request $request) {
      try {
        $validation = $request->validate([
          'montant' =>  'required|numeric|min:1000000',
          'description' =>  'string',
        ],[
          'required'  =>  'Veuillez remplir le champ :attribute',
          'string'  =>  ':attribute doit etre une chaine de caractere'
        ]);
  
        

        $new_solde = Credit::where('designation','afrocash')->first()->solde + $request->input('montant');
        // ajout de la transaction dans l'historique
  
        $transaction = new TransactionCreditCentral;
        $transaction->destinataire  = 'afrocash';
        $transaction->montant   = $request->input('montant');
        $transaction->solde_anterieur = Credit::where('designation','afrocash')->first()->solde;
        $transaction->nouveau_solde = $new_solde;
        $transaction->motif = 'autres';
        $transaction->type = 'apport';
        $transaction->description = $request->input('description');
        $transaction->save();
          
        Credit::where('designation','afrocash')->update([
          'solde' =>  $new_solde
        ]);
        return response()
          ->json('done');
          
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
    }

    // AJOUTER UNE DEPENSES

    public function addDepenses(Request $request) {
      try {
        $validation = $request->validate([
          'motif' =>  'required',
          'montant' =>  'required|numeric|min:10000',
          'description' =>  'string'
        ],[
          'required'  =>  'Veuillez remplir le champ :attribute',
          'numeric' =>  'Le champs :attribute doit etre une valeur numerique',
          'string'  =>  'Le champs :attribute doit etre une chaine de caractere'
        ]);
  
        $new_solde_afrocash = Credit::where('designation','afrocash')->first()->solde - $request->input('montant');
  
  
        $depenses = new TransactionCreditCentral;
        $depenses->motif  = $request->input('motif');
        $depenses->description  = $request->input('description');
        $depenses->montant  = $request->input('montant');
        $depenses->solde_anterieur = Credit::where('designation','afrocash')->first()->solde;
        $depenses->nouveau_solde = $new_solde_afrocash;
        $depenses->expediteur =  'afrocash';
        $depenses->type   = 'depense';
        $depenses->save();
        // debiter le solde afrocash central
        Credit::where('designation','afrocash')->update([
          'solde' =>  $new_solde_afrocash
        ]);
        
          return response()
            ->json('done');
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
    }

    // Apercu de transaction sur le tableau de bord
    public function getTransactionForDashboardView(Request $request) {
      try {
        return response()->json($request);
      } catch (AppException $e) {
        return response()->json($e->getMessage());
      }

    }

    //DISPONIBILITE DU CREDIT REX

    public function isRexDisponible($vendeur,$montant) {
  		$temp = RexAccount::where('numero',User::select('rex')->where('username',$vendeur))->first();
  		if($temp && ($temp->solde >= $montant)) {
  			return $temp;
  		}
  		return false;
  	}
// AJOUTER UNE PROMO
  public function addPromo(Request $request) {
    $validation = $request->validate([
      'intitule' =>  'required|string',
      'description'  =>  'string',
      'debut' =>  'required|date|before:fin',
      'fin' =>  'required|date|after_or_equal:'.(date("Y/m/d",strtotime("now")))
    ],[
      'required'  =>  'Champ(s) `:attribute` ne peut etre vide',
      'string'  =>  'Champ(s) `:attribute` est une chaine de caractere',
      'date'  =>  'Champ(s) `:attribute` doit etre une date',
      'before'  =>  'Champ(s) date de `:attribute` invalide',
      'after_or_equal'  =>  'date de `:attribute` invalide'
    ]);
    try {
      if(!$this->isExistPromo()) {
        if(Produits::where('with_serial',1)->first()) {
          $promo = new Promo;
          $promo->intitule = $request->input('intitule');
          $promo->debut = $request->input('debut');
          $promo->fin = $request->input('fin');
          $promo->subvention = $request->input('subvention');
          $promo->description = $request->input('description');
          $prix_vente_normal = Produits::where('with_serial',1)->first() ? Produits::where('with_serial',1)->first()->prix_vente : 0;
          $promo->prix_vente = $prix_vente_normal - $request->input('subvention');
          if($promo->prix_vente <= 0) {
            throw new AppException("Valeur de la subvention invalide!");
          }
          $promo->save();
          return response()->json('done');
        } else {
          throw new AppException("Erreur ! Contatez l'administrateur");
        }
      } else {
        throw new AppException("Une Promo est deja en cours!");
      }
    } catch (AppException $e) {
      header("Unprocessable entity",true,422);
      die(json_encode($e->getMessage()));
    }
  }

 

  // editer une promo
  public function editPromo(Request $request) {
    $validation = $request->validate([
      'intitule' =>  'required|string',
      'description'  =>  'string',
      'debut' =>  'required|date|before:fin',
      'fin' =>  'required|date|after_or_equal:'.(date("Y/m/d",strtotime("now")))
    ],[
      'required'  =>  '`:attribute` ne peut etre vide',
      'string'  =>  '`:attribute` est une chaine de caractere',
      'date'  =>  '`:attribute` doit etre une date',
      'before'  =>  'date de `:attribute` invalide',
      'after_or_equal'  =>  'date de `:attribute` invalide'
    ]);

    try {
      $promo = Promo::find($request->input('id_promo'));
      if($promo) {
        $promo->intitule = $request->input('intitule');
        $promo->debut = $request->input('debut');
        $promo->fin = $request->input('fin');
        $promo->description = $request->input('description');
        $promo->subvention = $request->input('subvention');
        $prix_vente_normal = Produits::where('with_serial',1)->first() ? Produits::where('with_serial',1)->first()->prix_vente : 0;
        $promo->prix_vente = $prix_vente_normal - $request->input('subvention');
        if($promo->prix_vente <= 0) {
          throw new AppException("Valeur de la subvention invalide!");
        }

        $promo->save();
        return response()->json('done');
      } else {
        throw new AppException("Error!");
      }
    } catch (AppException $e) {
        header("Erreur!",true,422);
        die(json_encode($e->getMessage()));
    }
  }
  // Interruption Promo

  public function interruptionPromo(Request $request) {
    $validation = $request->validate([
      'id_promo' =>  'required|exists:promos,id'
    ]);
    try {
      $promo = Promo::find($request->input('id_promo'));
      if($promo) {
        $promo->status_promo = 'inactif';
        $promo->save();
        return response()->json('done');
      } else {
        throw new AppException("Erreur!");
      }
    } catch (AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
  // Commandes
  public function allCommandes() {
    return view('admin.all-commandes');
  }

  public function getAllCommandes(Request $request , CommandMaterial $c) {
    try {
      $commands= $c->select()->orderBy('created_at','desc')->get();
      $all =  $this->organizeCommandList($commands);

      return response()->json($all);
    } catch (AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  public function getAllLivraison(Request $request) {
    try {
      return response()
        ->json($this->organizeLivraison($this->livraisonRequest(new Livraison)));
    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }
  }
#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
  // REINITIALISER UN UTILISATEUR
  public function resetUser(Request $request) {
    try {
      if(Hash::check($request->input('admin_password'),Auth::user()->password)) {
        User::where('username',$request->input('user'))->update([
          'password'  =>  bcrypt("loudcssyf")
        ]);
        return response()->json('done');
      } else {
        throw new AppException("Mot de passe incorrect!");
      }
    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }
  }
#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
  public function abortCommandMaterial(Request $request ,CommandMaterial $cm , Afrocash $a , TransactionAfrocash $ta) {
    try {
      $command = $cm->where('id',request()->id)->first();
      if($command->status == 'confirmed' || $command->status == 'aborted') {
        throw new AppException("Action Indisponible!");
      }
      
      // get account afrocash for user(da|vendeur)
      $accountAfrocashVendeur = $a->where('vendeurs',$command->vendeurs)
        ->where('type','courant')
        ->first();

      // get account afrocash for logistique
      $accountAfrocashLogistique = $a->where('vendeurs',$request->user()->username)
        ->where('type','courant')
        ->first();
      
        // get transactionInfos
      $transactionCommand = $ta->where('command_material_id',$command->id_commande)
        ->where('compte_debite',$accountAfrocashVendeur->numero_compte)
        ->first();

      // debit du compte afrocash logistique et credit du compte afrocash vendeur
      $accountAfrocashVendeur->solde += $transactionCommand->montant;
      $accountAfrocashLogistique->solde -= $transactionCommand->montant;

      $transactionAborted = new $ta;
      $transactionAborted->compte_debite = $accountAfrocashLogistique->numero_compte;
      $transactionAborted->compte_credite = $accountAfrocashVendeur->numero_compte;
      $transactionAborted->montant = $transactionCommand->montant;
      $transactionAborted->motif = "Annulation de commande materiel";
      $transactionAborted->command_material_id = $command->id_commande;

      $n = $this->sendNotification("Annulation de commande" , 
        "Vous avez annuler une commande Materiel pour : ".$accountAfrocashVendeur->vendeurs()->localisation,
        $request->user()->username);
      $n->save();

      $n = $this->sendNotification("Annulation de commande",
        "Une Commande materiel a ete annule pour :".$accountAfrocashVendeur->vendeurs()->localisation,
        'admin');
      $n->save();

      $n = $this->sendNotification("Annulation de commande",
        "Une Commande materiel a ete annule pour :".$accountAfrocashVendeur->vendeurs()->localisation,
        'root');
      $n->save();

      $n = $this->sendNotification("Annulation de commande",
        "Votre commande materiel a ete annule",
        $accountAfrocashVendeur->vendeurs);
      $n->save();

      $command->status = 'aborted';

      $command->save();
      $accountAfrocashVendeur->save();
      $accountAfrocashLogistique->save();
      $transactionAborted->save();
      
      return response()
        ->json('done');
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }

  // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

  public function transfertMaterialToOtherUser(Request $request,Exemplaire $e , User $u , Produits $p) {
    try {
        $validation = $request->validate([
          'quantite'  =>  'required|min:1|max:10',
          'expediteur' =>  'required|exists:users,username',
          'destinataire'  =>  'required|exists:users,username',
          'password'  =>  'required|string',
          'serialsNumber.*' =>  'required|exists:exemplaire,serial_number',
        ],[
          'required'  =>  'Champ(s) :attribute requis !',
          'min' =>  'La Quantite minimale requise est de 1',
          'max' =>  'La Quantite maximale requise est de 10',
          'exists'  =>  'Champ(s) :attribute n\'existe pas en base de donne'
        ]);
        // VERIFIER LA VALIDITE DU MOT DE PASSE 
        if(Hash::check($request->input('password'),$request->user()->password)) {
          # MOT DE PASSE VALIDE :)
            // VERIFIER SI LE NUMERO EST ATTRIBUE A UN VENDEUR
            $compteur = 0;
            foreach($request->input('serialsNumber') as $serial) {
              if($serialNumber = $e->where('serial_number',$serial)
                ->where('vendeurs',$request->input('expediteur'))->first()) {
                  #tous les numeros on ete verifie ils sont bien attribue a ce vendeur
                  #changement de vendeurs pour les numeros de series selectionnees :)
                    $compteur++;
                } else {
                    $exp = $u->where('username',$request->input('expediteur'))->first();
                    throw new AppException("Le terminal suivant : '".$serial."' n'est pas attribue au vendeur choisis ('".$exp->localisation."')");
                }
            }
            $_compteur = 0;
            if($compteur == $request->input('quantite')) {

              $transMateriel = new TransfertMateriel;
              $transMateriel->code = Str::random().'_'.time();
              $transMateriel->expediteur = $request->input('expediteur');
              $transMateriel->destinataire = $request->input('destinataire');
              $transMateriel->quantite = $request->input('quantite');
              $tmp = $transMateriel->code;
              $transMateriel->save();

              $stockExpediteur = StockVendeur::where('vendeurs',$request->input('expediteur'))
                ->where('produit',$p->where('with_serial',1)->first()->reference)->first();

              $stockDestinataire = StockVendeur::where('vendeurs',$request->input('destinataire'))
                ->where('produit',$p->where('with_serial',1)->first()->reference)->first();
              // DEBIT DANS LE STOCK DE L'EXPEDITEUR
              $_qt = $stockExpediteur->quantite - $request->input('quantite');
              StockVendeur::where('vendeurs',$request->input('expediteur'))
                ->where('produit',$p->where('with_serial',1)->first()->reference)
                ->update([
                  'quantite'  =>  $_qt
                ]);
              if(!$stockDestinataire){
                  $_stock = new StockVendeur;
                  $_stock->vendeurs = $request->input('destinataire');
                  $_stock->produit = $p->where('with_serial',1)->first()->reference;
                  $_stock->quantite = $request->input('quantite');
                  $_stock->save();
              } else {
                  $qt = $stockDestinataire->quantite + $request->input('quantite');
                  StockVendeur::where('vendeurs',$request->input('destinataire'))
                    ->where('produit',$p->where('with_serial',1)->first()->reference)
                    ->update([
                      'quantite' => $qt
                    ]);
              }

              foreach($request->input('serialsNumber') as $serial) {
                // on effectue le changement de vendeur sur tous les materiels 
                $serialNumber = $e->find($serial);
                if($serialNumber) {

                    $transSerial = new TransfertSerial;
                    $transSerial->id_transfert = $tmp;
                    $transSerial->serial = $serialNumber->serial_number;
                    $transSerial->save();

                    $serialNumber->vendeurs = $request->input('destinataire');
                    $serialNumber->save();
                    
                    $_compteur++;
                } else {
                    throw new AppException("Erreur , Veuillez ressayez !");
                }
              }
              if($_compteur == $request->input('quantite')) {

                $n = $this->sendNotification(
                  "Transfert Materiel",
                  "Un materiel est transfere au compte de : ".$u->where('username',$request->input('destinataire'))->first()->localisation,
                  'admin'
                );
                $n->save();

                $n->sendNotification(
                  'Transfert Materiel',
                  "Vous avez recu un materiel de la part de :".$u->where('username',$request->input('expediteur')->first()->localisation,
                  $request->input('destinataire'))
                );
                $n->save();

                $n->sendNotification(
                  "Transfert Materiel",
                  "Materiel sortant a destination de :".$u->where("username",$request->input('destinataire'))->first()->localisation,
                  $request->input('expediteur')
                );
                $n->save();

                return response()->json('done');
              } else {
                  throw new AppException("Erreur , Veuillez ressayez !");
              }
            } else {
                throw new AppException("Erreur , veuillez ressayer !");
            }
        } else {
            throw new AppException("Mot de passe Invalide ! Veuillez ressayez...");
        }
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }

  ## REMPLACER UN MATERIEL DEFECTUEUX CHEZ LE VENDEUR
  public function replaceMaterialDefectuous(Request $request , Exemplaire $e , DeficientMaterial $df) {
    try {
        $validation = $request->validate([
          'vendeur' =>  'required|exists:users,username',
          'defectuous'  =>  'required|exists:exemplaire,serial_number',
          'replacement' =>  'required|exists:exemplaire,serial_number',
          'password'  =>  'required'
        ]);
        // VERIFIER LA VALIDITE DU MOT DE PASSE
        if(Hash::check($request->input('password'),$request->user()->password)) {
            // verifier si le numero existe pour ce vendeurs
            $deficientSerial = $e->find($request->input('defectuous'));
            if($deficientSerial->vendeurs == $request->input('vendeur')) {
                // verifier si le materiel est inactif
                if($deficientSerial->status == 'inactif') {
                    // verifier si le numero n'est pas attribuer
                    $replacementSerial = $e->find($request->input("replacement"));
                    if(is_null($replacementSerial->vendeurs)) {
                      // verifier que le numero existe dans le meme depot d'origine
                      if($replacementSerial->depot()->depot == $deficientSerial->depot()->depot) {

                          $df->serial_to_replace = $deficientSerial->serial_number;
                          $df->serial_replacement = $replacementSerial->serial_number;
                          $df->vendeurs = $request->input('vendeur');
                          $df->motif = $request->input('motif');
                          $df->save();

                          $deficientSerial->vendeurs = NULL;
                          $deficientSerial->save();

                          $replacementSerial->vendeurs = $request->input('vendeur');
                          $replacementSerial->save();

                          return response()->json('done');
                      } else {
                          throw new AppException("Veuillez choisir le meme depot d'origine!");
                      }
                    } else {
                        throw new AppException("Le materiel de remplacement est deja attribue ,Veuillez choisir un numero non attribue !");
                    }
                } else {
                    throw new AppException("Materiel deja actif!");
                }
            } else {
                throw new AppException("Ce Materiel n'existe pas pour ce vendeur");
            }
        } else {
            throw new AppException("Mot de passe invalide !");
        }
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }
}
