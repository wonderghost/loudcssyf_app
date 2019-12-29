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
use App\Exceptions\AppException;
use App\Promo;
use App\CommandMaterial;

class AdminController extends Controller
{
    //
    use Similarity;
    use Afrocashes;
    use Rapports;
    use Cga;
    use Livraisons;

    // etat du depot central
    public function etatDepotCentral() {

      return view('admin.depot-central');
    }
    // recuperation etat du depot Central
    public function getEtatDepotCentral(Request $request) {
      //
      $produits = Produits::all();
      return response()->json($produits);
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

    // TABLEAU DE BORD
    public function dashboard() {
      // recapitulatif solde cga
      $cga = [
        'solde_central' =>  Credit::where('designation','cga')->first()->solde,
        'solde_vstandart' => CgaAccount::whereIn('vendeur',User::select('username')->where('type','v_standart')->get())->sum('solde'),
        'solde_reseau'  =>  CgaAccount::whereIn('vendeur',User::select("username")->where("type",'v_da')->get())->sum('solde')
        ] ;
        // recapitulatif solde afrocash
        $afrocash = [
          'solde_central' =>  Credit::where('designation','afrocash')->first()->solde,
          'solde_semigrossiste' =>  Afrocash::whereIn('vendeurs',User::select('username')->where('type','v_standart'))->where('type','semi_grossiste')->sum('solde'),
          'solde_courant_reseau' =>  Afrocash::whereIn('vendeurs',User::select('username')->where('type','v_da'))->where('type','courant')->sum('solde'),
          'solde_courant_vstandart' =>  Afrocash::whereIn('vendeurs',User::select('username')->where('type','v_standart'))->where('type','courant')->sum('solde')
        ];
        // Utilisateurs
        $users = [
          'da'  =>  User::where('type','v_da')->count(),
          'v_standart'  =>  User::where('type','v_standart')->count()
        ];
        // MATERIELS

        $materiel = [
          'entrepot'  =>  Produits::all(),
          'depot' =>  StockPrime::select()->sum('quantite')
        ];
        return view('admin.dashboard')
          ->withCga($cga)
          ->withAfrocash($afrocash)
          ->withUsers($users)
          ->withMateriels($materiel);
    }
    // AJOUTER UN USER
    public function getFormUser() {
    	return view('admin.add-user');
    }
    // LIST DES USERS
    public function listUser() {
    	$users = User::select()->where('type','<>','admin')->orderBy('created_at','desc')->get();
    	return view('admin.list-users')->withUsers($users);
    }

    public function addUser(UserRequest $request) {
    	$user = new User;
    	$agence = new Agence;
    	$user->email = $request->input('email');
    	$user->phone = $request->input('phone');
    	$user->type = $request->input('type');
    	$user->password = bcrypt($request->input('password'));
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
    			return redirect("/admin/add-user")->with('success',"Nouvel utilisateur ajouté!");
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

    		return redirect("/admin/add-user")->with('success',"Nouvel utilisateur ajouté!");
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
        $user = User::select()->where('username',$request->input('ref'))->first();
        $user->status = 'blocked';
        $user->save();
        return response()->json('done');
    }
    public function unblockUser(Request $request) {
        $user = User::select()->where('username',$request->input('ref'))->first();
        $user->status = 'unblocked';
        $user->save();
        return response()->json('done');
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
      $depenses = TransactionCreditCentral::where('type','depense')->orderBy('created_at','desc')->get();
      $apports = TransactionCreditCentral::where('type','apport')->orderBy('created_at','desc')->get();
      return view('admin.afrocash-credit')->withDepenses($depenses)->withApports($apports);
    }

    //
    public function apportCapital(Request $request) {

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

      return redirect('/admin/afrocash')->withSuccess("Success!");
    }

    // AJOUTER UNE DEPENSES

    public function addDepenses(Request $request) {
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

      return redirect('/admin/afrocash')->withSuccess("Success!");
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
      'required'  =>  '`:attribute` ne peut etre vide',
      'string'  =>  '`:attribute` est une chaine de caractere',
      'date'  =>  '`:attribute` doit etre une date',
      'before'  =>  'date de `:attribute` invalide',
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

  // VERIFIER SI UNE PROMO N'EST PAS DEJA ACTIVE
  public function isExistPromo() {
    $temp = Promo::where('status_promo','actif')->first();
    if($temp) {
      return $temp;
    }
    return false;
  }

  // /// recuperation de la promo active
  public function getPromo(Request $request) {
    $temp = $this->isExistPromo();
    if($temp) {
      $all = [];
      $debut = new Carbon($temp->debut);
      $fin = new Carbon($temp->fin);
      $debut->setLocale('fr_FR');
      $fin->setLocale('fr_FR');
      $all = [
        'id'  =>  $temp->id,
        'intitule'  =>  $temp->intitule,
        'debut' =>  $temp->debut,
        'fin' =>  $temp->fin,
        'subvention'  =>  $temp->subvention,
        'prix_materiel' =>  $temp->prix_vente,
        'description' =>  $temp->description
      ];

      return response()->json($all);
    }
    else {
      return response()->json('fail');
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
        header("Unprocessable entity",true,422);
        die(json_encode($e->getMessage()));
    }
  }
  // Interruption Promo

  public function interruptionPromo(Request $request) {
    $validation = $request->validate([
      'ref-0' =>  'required|exists:promos,id'
    ]);
    try {
      $promo = Promo::find($request->input('ref-0'));
      if($promo) {
        $promo->status_promo = 'inactif';
        $promo->save();
        return response()->json('done');
      } else {
        throw new AppException("Erreur!");
      }
    } catch (AppException $e) {
      header("Unprocessable entity",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  // DASHBOARD CONFIGURATION
  public function dataForUserChart(Request $request) {
    try {
      $v_standart = User::where('type','v_standart')->get();
      $da = User::where('type','v_da')->get();
      return response()->json([
        'v_standart'  =>  $v_standart->count(),
        'v_da'  =>  $da->count(),
      ]);
    } catch (AppException $e) {
      header("Unprocessable entity",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  //
  public function dataForDepotChart(Request $request) {
    try {
      $depots = Depots::all();
      $all = [];
      foreach($depots as $key => $value) {
        $tmp = StockPrime::where('depot',$value->localisation)->where('produit',Produits::where('with_serial',1)->first() ? Produits::where('with_serial',1)->first()->reference : null)->first();
        $qte = $tmp ? $tmp->quantite : 0 ;
        $all[$key] = [
          'depot' =>  $value->localisation,
          'quantite_materiel' =>  $qte
        ];
      }
      return response()->json($all);
    } catch (AppException $e) {
      header("Unprocessable entity",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  // Commandes
  public function allCommandes() {
    return view('admin.all-commandes');
  }

  public function getAllCommandes(Request $request) {
    try {
      $commands= CommandMaterial::where('status','unconfirmed')->orderBy('created_at','desc')->get();
      $_commands = CommandMaterial::where("status",'confirmed')->orderBy('created_at','desc')->get();

      $all =  $this->organizeCommandList($commands);
      $_all = $this->organizeCommandList($_commands);
      // recuperation des livraison
      $livraison = $this->livraisonStateRequest('unvalidate');
      $livraison = $this->organizeLivraison($livraison);

      $_livraison = $this->livraisonStateRequest('validate');
      $_livraison = $this->organizeLivraison($_livraison);
      
      return response()->json([
        'unconfirmed' =>  $all,
        'confirmed' =>  $_all,
        'livraison_unvalidate'  =>  $livraison,
        'livraison_validate'  =>  $_livraison
      ]);
    } catch (AppException $e) {
      header("Unprocessable entity",true,422);
      die(json_encode($e->getMessage()));
    }

  }
}
