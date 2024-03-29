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
use App\Traits\Technicien;

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
use App\Stock;
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
use App\Kits;
use App\Articles;
use App\Interval;
use App\IntervalProduit;
use App\FormuleInterval;

use Illuminate\Support\Facades\DB;
use App\PromoFormule;


class AdminController extends Controller
{
    //
    use Similarity;
    use Afrocashes;
    use Rapports;
    use Cga;
    use Livraisons;
    use Technicien;

    public function emailTest() {
      return view('emails.annulation-saisie');
    }

    // recuperation etat du depot Central
    public function getEtatDepotCentral(Request $request , Produits $p) {
      //
      $data = [];
      foreach($p->all() as $key => $value) {
        $data[$key] = [
          'libelle' =>  $value->libelle,
          'quantite_centrale' =>  $value->quantite_centrale,
          'prix_initial'  =>  $value->prix_initial,
          'prix_vente'  =>  $value->prix_vente,
          'marge' =>  $value->marge,
          'marge_pdc' => $value->marge_pdc,
          'marge_pdraf' =>  $value->marge_pdraf,
          'with_serial' =>  $value->with_serial,
          'reference' =>  Crypt::encryptString($value->reference),
          '_reference'  =>  $value->reference
        ];
      }
      return response()->json($data);
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

    public function getListUsers(Request $request) {
      try {
        $users = User::where('type','<>','admin')->orderBy('localisation','desc')->get();
        $userCollection = collect([]);
        foreach ($users as $key => $element) {
          $_tmp = $element->only(['username','type','email','phone','localisation','status','nom','prenom']);
          $_tmp['username_encrypted'] = Crypt::encryptString($element->username);
          $userCollection->prepend($_tmp);
        }
        return response()->json($userCollection);
      } catch (AppException $e) {
        header("Unprocessable entity",true,422);
        die(json_encode($e->getMessage()));
      }

    }

    public function addUser(UserRequest $request) {
      try {
        $user = new User;
      	$agence = new Agence;
      	$user->email = $request->input('email');
      	$user->phone = $request->input('phone');
      	$user->type = $request->input('access');
      	$user->password = bcrypt("Loudcssyf");
      	$user->localisation = $request->input('agence');

      	if($request->input('access') == 'v_da') {
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
              if($user->type !== 'v_standart' && $user->localisation == "") {
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


    // Edit users
    public function editUser($slug) {
      try {
        $user = User::select()->where('username',Crypt::decryptString($slug))->first();

        $agence = $user->agence();

        if($agence) {

          $user->numdist = $agence->num_dist;
          $user->societe = $agence->societe;
          $user->rccm = $agence->rccm;
          $user->ville = $agence->ville;
          $user->adresse = $agence->adresse;
        }
        

        return response()
          ->json($user);          
        }
        catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
        }
    }
    
    // envoi du formulaire de modification
    public function editUserRequest(Request $request) {
      try {
        $validation = $request->validate([
          'email' =>  'required|email',
          'phone' =>  'required',
          'password_confirmation' =>  'required'
        ]);

        if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
          throw new AppException("Mot de passe invalide!");
        }
        $user = User::select()->where('username',$request->input('username'))->first();

        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->localisation = $request->input('localisation');
        $user->service_plus_id = request()->service_plus_id;

        $agence = $user->agence() ? $user->agence() : null;
        if(!is_null($agence)) {
          $agence->societe = $request->input('societe');
          $agence->num_dist = $request->input('numdist');
          $agence->rccm = $request->input('rccm');
          $agence->ville = $request->input('ville');
          $agence->adresse = $request->input('adresse');
        }

        if($user->update()) {
          if($user->type != 'technicien') {
            if($agence->update()) {
              return response()
                ->json('done');
            }
          }
          return response()
            ->json('done');
        }
      }
      catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
      }
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
    
    public function listFormule(Formule $f,Option $op) {
      try {
          $formule = $f->all();
          $options = $op->all();

          $data_formule = [];
          $data_option = [];

          foreach($formule as $key => $value) {
            $data_formule[$key] = [
              'nom' =>  $value->nom,
              'title' =>  $value->title,
              'created_at'  =>  $value->created_at,
              'prix'  =>  $value->prix,
              'encrypted_name'  =>  Crypt::encryptString($value->nom)
            ];
          }

          foreach($options as $key => $value) {
            $data_option[$key] = [
              'nom'  =>  $value->nom,
              'title' =>  $value->title,
              'created_at'  =>  $value->created_at,
              'prix'  =>  $value->prix,
              'encrypted_name'  =>  Crypt::encryptString($value->nom)
            ];
          }
          return response()
            ->json([
              'formules'  =>  $data_formule,
              'options' =>  $data_option
            ],200);
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
    }
    //
    public function addFormule(Request $request) {
      try {
            $validation = $request->validate([
              'name'  => 'required|string|unique:formule,nom',
              'price' =>  'required|numeric'
            ],[
              'required'  =>  'Champs `:attribute` requis',
              'unique'  =>  '`:attribute` existe deja dans le systeme'
            ]);

            $formule = new Formule;
            $formule->nom = Str::slug(request()->name);
            $formule->title = request()->name;
            $formule->prix = request()->price;

            $formule->save();
            return response()
              ->json('done');
        } catch(AppException $e) {
            header("Erreur!",true,422);
            die(json_encode($e->getMessage()));
        }
    }
  

    //
    public function addOptionFormule(Request $request) {
      try {
            $validation = $request->validate([
              'name'  =>  'required|string|unique:options,nom',
              'price'  =>  'required|numeric'
            ],[
              'required'  =>  'Champs `:attribute` requis',
              'unique'  =>  '`:attribute` existe deja dans le systeme'
            ]);

            $option = new Option;
            $option->nom = $request->input('name');
            $option->prix = $request->input('price');
            $option->save();

            return response()
              ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    //

    public function historiqueApport() {
      try {
          $apports = TransactionCreditCentral::where('type','apport')
            ->where('destinataire','afrocash')
            ->orderBy('created_at','desc')->get();
          return response()
            ->json($apports);
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
    }

    public function historiqueDepenses() {
      try{
          $depenses = TransactionCreditCentral::where('type','depense')
            ->where('expediteur','afrocash')
            ->orderBy('created_at','desc')->get();
            
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
    public function addPromo()
    {
      try
      {
        $validation = request()->validate([
          'intitule'  =>  'required|string',
          'description' =>  'string',
          'debut' =>  'required|date|before:fin',
          'fin' =>  'required|date|after_or_equal:'.(date("Y/m/d",strtotime("now"))),
          'type'  =>  'required|string',
          'formules'  =>  'required_if:type,formule'
        ],[
          'required'  =>  'Champ(s) `:attribute` ne peut etre vide',
          'string'  =>  'Champ(s) `:attribute` est une chaine de caractere',
          'date'  =>  'Champ(s) `:attribute` doit etre une date',
          'before'  =>  'Champ(s) date de `:attribute` invalide',
          'after_or_equal'  =>  'date de `:attribute` invalide',
        ]);

        // verifier l'existence d'une promo en cours
        if($this->isExistPromo(request()->type))
        {
          throw new AppException("Une promo du même type est deja en cours.");
        }

        if(!Produits::where('with_serial',1)->first())
        {
          throw new AppException("Erreur.Contactez l'administrateur.");
        }

        $promo = new Promo;
        $promo->type = request()->type;
        $promo->intitule = request()->intitule;
        $promo->debut = request()->debut;
        $promo->fin = request()->fin;
        $promo->subvention = request()->subvention;
        $promo->description = request()->description;
        // if(request()->type == 'kit_easy')
        // {
        //   $kit = Kit::find('kit-easy-tv');
        //   $terminal = $kit->getTerminalReference();
        //   $prix_
        //   $prix_vente_normal = Produits::where('with_serial',1)->first() ? Produits::where('with_serial',1)->first()->prix_vente : 0;
        // }
        // else if(request()->type == 'on_formule')
        // {

        // }
        // else
        // {

        // }
        $prix_vente_normal = Produits::where('with_serial',1)->first() ? Produits::where('with_serial',1)->first()->prix_vente : 0;

        $promo->prix_vente = $prix_vente_normal - request()->subvention;

        if($promo->prix_vente <= 0)
        {
          throw new AppException("Valeur de la subvention invalide!");
        }
        if($promo->save())
        {
          if(request()->type == 'on_formule')
          {
            foreach(request()->formules as $value)
            {
              $pf = new PromoFormule;
              $pf->promo_id = $promo->id;
              $pf->formule = $value;
              $pf->save();
            }
          }
          return response()->json('done',200);
        }
      }
      catch(AppException $e)
      {
        header("Erreur",true,422);
        return response()->json($e->getMessage(),422);
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
        if($promo->type == 'kit_easy')
        {
          $kit = Kits::find('kit-easy-tv');
          $terminal = $kit->getTerminalReference();
        }
        else
        {
          $kit = Kits::find('kits-canal-sat');
          $terminal = $kit->getTerminalReference();
        }

        $promo->prix_vente = $terminal->prix_vente - $request->input('subvention');
        
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
###########################FILTER REQUEST COMMANDES 3#############
  public function filterRequestCommande(Request $request , $user , $state,$promo) {
    try {
      if($user && $state && $promo) {
        if($user != "all") {
          if($promo == 'en_promo') {
            // pendant la promo
            $result = CommandMaterial::where('vendeurs',$user)
              ->whereNotNull('promos_id')
              ->where('status',$state);
          }
          else if($promo == 'hors_promo') {
            // hors de la promo
            $result = CommandMaterial::where('vendeurs',$user)
              ->whereNull('promos_id')
              ->where('status',$state);
          }
          else {
            // en promo / hors promo
            $result = CommandMaterial::where('vendeurs',$user)
              ->where('status',$state);
          }
        }
        else {
          // tous les utilisateurs
          if($promo == 'en_promo') {
            // pendant la promo
            $result = CommandMaterial::whereNotNull('promos_id')
              ->where('status',$state);
          }
          else if($promo == 'hors_promo') {
            // hors de la promo
            $result = CommandMaterial::whereNull('promos_id')
              ->where('status',$state);
          }
          else {
            // en promo / hors promo
            $result = CommandMaterial::where('status',$state);
          }
        }
      }

      $all = $this->organizeCommandList($result->orderBy('created_at','desc')->paginate(100));

      return response()
        ->json($all);
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }
// 
  public function getAllCommandes(Request $request , CommandMaterial $c) {
    try {
      if($request->user()->type != 'v_da' && $request->user()->type != 'v_standart') {

        $commands= $c->where('status','unconfirmed')
          ->orderBy('created_at','desc')
          ->paginate(100);
      }
      else {

        $commands = $c->select()
          ->where('vendeurs',$request->user()->username)
          ->where('status','unconfirmed')
          ->orderBy('created_at','desc')
          ->paginate(100);

      }

      $all =  $this->organizeCommandList($commands);

      return response()->json($all);

    }
    catch (AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  public function getAllLivraison(Request $request) {
    try {
      return $this->organizeLivraison($this->livraisonRequest(new Livraison,$request));
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
      $accountAfrocashVendeur->solde += $transactionCommand ? $transactionCommand->montant : 0;
      $accountAfrocashLogistique->solde -= $transactionCommand ? $transactionCommand->montant : 0;

      $transactionAborted = new $ta;
      $transactionAborted->compte_debite = $accountAfrocashLogistique->numero_compte;
      $transactionAborted->compte_credite = $accountAfrocashVendeur->numero_compte;
      $transactionAborted->montant = $transactionCommand ? $transactionCommand->montant : 0;
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

  public function getListingPromo(Request $request , Promo $p) {
    try {

        return response()
          ->json(
            $p->select()
              ->orderBy('created_at','desc')
              ->get()
            );
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }
  // ############################# TRANSFERT MATERIEL D'UN DEPOT A UN AUTRE ##################################################################
  
  public function transfertMaterialToOtherDepot(Request $request,Produits $p , Exemplaire $e , StockPrime $sd , Stock $s) {
    try {
        $validation = $request->validate([
          'origine' =>  'required|exists:depots,localisation',
          'destination' =>  'required|exists:depots,localisation',
          'quantite'  =>  'required|min:1|max:10',
          'serials.*' =>  'required|exists:exemplaire,serial_number'
        ],[
          'required' =>  'Champ :attribute requis',
          'exists'  =>  ':attribute n\'existe pas dans le system'
        ]);

        if($request->input("destination") == $request->input('origine')) {
          throw new AppException("Operation indisponible !");
        }

        // verification du status des materiels 
        foreach($request->input('serials') as $serial) {
          $_serial = $e->find($serial);
          if(!is_null($_serial->vendeurs)) {
            throw new AppException("materiel `".$serial."` deja attribue!");
          }

          // verifier si le materiel appartien au depot choisi
          $stockDepot = $s->where('exemplaire',$serial)
            ->where('depot',$request->input('origine'))
            ->first();
          
            if(!$stockDepot) {
              throw new AppException("materiel `".$serial."` inexistant dans le depot choisi!");
            }
        }

        #traitement
        // recuperation du stock du depot origine pour  agir sur la quantite

        $originStock = $sd->where('depot',$request->input('origine'))
          ->where('produit',$p->where('with_serial',1)->first()->reference)
          ->first();

        // recuperation du stock du depot de destination pour

        $destinationStock = $sd->where('depot',$request->input('destination'))
          ->where('produit',$p->where("with_serial",1)->first()->reference)
          ->first();




        return response()
          ->json([$originStock,$destinationStock]);
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }

  // ############################### AFFECTATION DE MATERIELS DANS UN DEPOT ################################################

  public function getInfosAffectation() {
    try {

        $e = Exemplaire::whereNull('vendeurs')->where('origine',1)->get();
        $data = [];
        
        $i=0;

        foreach($e as $key => $value) {
          if(is_null($value->depot())) {
            $data[$i] = $value;
            $i++;
          }
          
        }

        return response()
          ->json($data);

    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }

  public function affectationMaterielToDepot(Request $request , Exemplaire $e) {
    try {
        $validation = $request->validate([
          'confirmation_password' =>  'required|string',
          'quantite'  =>  'required|numeric|min:1',
          'serial_number.*' =>  'required|exists:exemplaire,serial_number',
          'depots.*'    =>  'required',
        ],[
          'required'  =>  '`:attribute` requis !',
          'exists'  =>  '`attribute` n\'existe pas dans le systeme ! ',
          'min' =>  'Quantite minimum : 1 !'
        ]);
        // VERIFIER LA VALIDITE DU MOT DE PASSE 
        if(!Hash::check($request->input('confirmation_password'),$request->user()->password)) {
          throw new AppException("Mot de passe invalide !");
        }
        
        $serials = [];
        $dataStock = [];

        for($i = 0 ; $i < $request->input('quantite') ; $i++) {

          $serials[$i] = $e->find($request->input('serial_number')[$i]);

          if(is_null($serials[$i]->depot())) {
            // envoi de la requete 
            $dataStock[$i] = new Stock;
            $dataStock[$i]->exemplaire = $serials[$i]->serial_number;
            $dataStock[$i]->depot = $request->input('depots')[$i];
            $dataStock[$i]->quantite = 1;
            $dataStock[$i]->origine = "Affectation";

            
          }
        }

        foreach($dataStock as $value) {
          // enregistrement
          $value->save();
        }

        return response()
          ->json('done');
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }

  public function affectationDepotVendeur(Request $request , Exemplaire $e) {
    try {

      $validation = $request->validate([
        'depot' =>  'required|exists:depots,localisation',
        'vendeur'  =>  'required|exists:users,username',
        'serial_number' =>  'required|exists:exemplaire,serial_number',
        'password_confirmation' =>  'required|string'
      ],
    [
      'required'  =>  '`:attribute` requi(s) !',
      'exists'  =>  '`:attribute` n\'existe pas dans le systeme'
    ]);

    // password validation 

      if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
        throw new AppException("Mot de passe invalide !");
      }
      
      // verification de l 'existence du materiel dans le depot
      $serialNumber = $e->find($request->input('serial_number'));
      $depotForSerial = $serialNumber->depot() ? $serialNumber->depot()->depot : null;

      if(is_null($depotForSerial) || $depotForSerial != $request->input('depot')) {
        throw new AppException("Materiel introuvable dans le depot specifie !");
      }

      // verifier si le materiel n'est pas deja affecte a un vendeur
      if(!is_null($serialNumber->vendeurs)) {
        throw new AppException("Ce materiel est deja attribue a un vendeur !");
      }

      // verifier si le materiel n'est pas deja actif

      if($serialNumber->status == 'actif') {
        throw new AppException("Ce materiel est deja actif !");
      }

      // Traitement des operations

      $serialNumber->vendeurs = $request->input('vendeur');
      $serialNumber->save();

      return response()
        ->json('done');
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }


  // get data for edit formule

  public function getDataEditFormule($slug,Formule $f) {
    try {
      $data = $f->find(Crypt::decryptString($slug));
      return response()
        ->json($data);
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  public function getDataEditOption($slug,Option $o) {
    try {
      $data = $o->find(Crypt::decryptString($slug));
      return response()
        ->json($data);
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  // editer les infos d'une formule

  public function editFormule(Request $request , $slug , Formule $f) {
    try {

      $formule = $f->find(Crypt::decryptString($slug));
      $formule->title = request()->title;
      $formule->prix = request()->prix;
      $formule->update();

      return response()
        ->json('done');
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  // editer les infos d'une option

  public function editOption(Request $request , $slug , Option $o) {
    try {
      $option = Option::find(Crypt::decryptString($slug));
      $option->title = request()->title;
      $option->prix = request()->prix;
      $option->update();

      $option = $o->find(Crypt::decryptString($slug));
      $option->title = request()->title;
      $option->prix = request()->prix;
      $option->update();      

      return response()
        ->json('done');

    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }


  // SAVE KITS MATERIELS REQUEST 
  public function saveKitRequest(Request $request,Kits $k) {
    try {
      $validation = $request->validate([
        'intitule'  =>  'required|string',
        'materiels.*' =>  'required|string|exists:produits,reference',
        'password_confirmation' =>  'required|string'
      ],[
        'required'  =>  '`:attribute` requi(s)!'
      ]);

      // VALIDATION DU MOT DE PASSE 

      if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
        throw new AppException("Mot de passe invalide !");
      }

      // VERIFIER L'EXISTENCE DE L'ARTICLES
      
      if($data = $k->find(Str::slug(request()->intitule))) {
        throw new AppException("Article existant ! Ressayez ...");
      }

      // 
      if(!count(request()->materiels) > 0) {
        throw new AppException("Veuillez selectionner les produit(s)!");
      }


      // RECUPERATION DES INFOS PRODUITS

      $matArray = $request->input('materiels');

      $articles = [];

      foreach($matArray as $key => $value) {
        $articles[$key] = new Articles;
        $articles[$key]->produit = $value;
        $articles[$key]->kit_slug = Str::slug(request()->intitule);
      }
      

      $k->name = request()->intitule;
      $k->slug = Str::slug(request()->intitule);
      $k->description = request()->description;

      $k->save();

      foreach($articles as $value) {
        $value->save();
      }

      return response()
        ->json('done');
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  /**
   * Edit Kit Name Request
   */
  public function editKitRequest()
  {
    try
    {
      $validation = request()->validate([
        'slug'  =>  'required|exists:kits,slug',
        'new_name'  =>  'required|string'
      ]);

      $kit = Kits::find(request()->slug);
      if(!$kit)
      {
        throw new AppException("Erreur , try again later.");
      }

      $kit->name = request()->new_name;
      $kit->description = request()->new_name;

      if($kit->update())
      {
        return response()->json('done',200);
      }
    }
    catch(AppException $e)
    {
      header("Erreur",true,422);
      return response()->json($e->getMessage(),422);
    }
  }

  #  GET DATA EDIT MATERIEL

  public function getDataEditMateriel($slug) {
    try {
      $produitData = Produits::find(Crypt::decryptString($slug));
      $produitData->intervals = $produitData->intervals()->first() ? $produitData->intervals()->first()
        ->intervalData()
        ->first() : 
        null;

      return response()
        ->json($produitData);
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  # INTERVAL & FORMULES

  public function getIntervalInfos() {
    try {
      $intervals = Interval::all();
      $data = [];
      foreach($intervals as $key => $value) {
        $data[$key] = [
          'id'  =>  $value->id,
          'interval_first'  =>  $value->interval_serial_first,
          'interval_last' =>  $value->interval_serial_last,
          'formule' =>  $value->formule()->first() ? $value->formule()->get() : []
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

  # AJOUTER UNE FORMULE A L'INTERVAL

  public function addFormuleToInterval() {
    try {
      $validation = request()->validate([
        'formule_name.*'  =>  'required|string|exists:formule,nom',
        'interval_id' =>  'required|string|exists:intervals,id'
      ]);
      
      if(count(request()->formule_name) <= 0) {
        throw new AppException("Selectionnez au moins une formule !");
      }
      $interval = Interval::find(request()->interval_id);
      $formuleIntervals = [];

      foreach(request()->formule_name as $key => $value) {
        $data = $interval->formule()
          ->where('id_formule',$value)
          ->first();
        
        if(!$data) {

          $formuleIntervals[$key] = new FormuleInterval;
          $formuleIntervals[$key]->id_interval = $interval->id;
          $formuleIntervals[$key]->id_formule = $value;
          $formuleIntervals[$key]->save();
        }
      }

      return response()
        ->json('done');
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }



  /**
   * Kist list
   */
  public function listArticles() {
    try 
    {
      $kits = Kits::all();
      foreach($kits as $value)
      {
        $value->articles = $value->articles()->select('produit')->get();
        foreach($value->articles as $_value)
        {
          $_value->libelle = $_value->produits()->first()->libelle;
        }
      }
      return response()->json($kits,200);
    }
    catch(AppException $e)
    {
      header("Erreur",true,422);
      return response()->json($e->getMessage(),422);
    }
  }

}
