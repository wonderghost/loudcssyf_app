<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserEditRequest;
use App\Traits\Similarity;
use App\Traits\Afrocashes;
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
use App\StockVendeur;
use App\Exemplaire;
use App\Credit;
use App\TransactionCreditCentral;


class AdminController extends Controller
{
    //
    use Similarity;
    use Afrocashes;

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
        return view('admin.dashboard');
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
            } else {
                $user->save();
            }
            // dd($user);
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

    // AJOUTER UN RAPPORT

    public function addRapport() {
        return view('admin.add-rapport-vente');
    }

    public function  forRapportgetVendeur(Request $request) {
        $vendeurs = User::select()->where('type','v_standart')->orWhere('type','v_da')->get();
        $all =[];
        foreach ($vendeurs as $key => $value) {
            $agence = Agence::select()->where('reference',$value->agence)->first();
            $all[$key] = [
                'username'  =>  $value->username,
                'agence'    =>  $agence->societe
            ];

        }
        return response()->json($all);
    }

    // ENREGISTREMENT D'UN RAPPORT
    public function sendRapport(RapportRequest $request) {

        $rapport = new RapportVente;
        $rapport->id_rapport = "RP-".time();
        $rapport->vendeurs  = $request->input('vendeurs');
        $rapport->quantite_recrutement  =   $request->input('quantite_recrutement');
        $rapport->quantite_migration    =   $request->input('quantite_migration');
        $rapport->ttc_recrutement   =   $request->input('ttc_recrutement');
        $rapport->ttc_reabonnement  =   $request->input('ttc_reabonnement');
        $temp = new Carbon($request->input('date'));
        $rapport->date_rapport = $temp->toDateTimeString();

        // VERIFIER SI LE RAPPORT N'EXISTE PAS DEJA A CETTE DATE LA
        if($this->isExistRapportOnThisDate(new Carbon($request->input('date')),$request->input("vendeurs"))) {
          return redirect('admin/add-rapport')->with('_errors',"Un rapport existe deja a cette date !");
        }
        // DEBITER DU STOCK VENDEURS
        $stock_vendeur = StockVendeur::where([
            'vendeurs'  =>  $request->input('vendeurs'),
            'produit'   =>  Exemplaire::where('status','inactif')->first()->produit
        ])->first();
        // VERIFICATION DE LA QUANTITE DE MATERIEL DISPONIBLE EN STOCK CHEZ LE VENDEURS
        if($stock_vendeur) {
          if($stock_vendeur->quantite < ($request->input('quantite_recrutement') - $request->input('quantite_migrateion'))) {
              return redirect('/admin/add-rapport')->with('_errors',"Quantité Materiel indisponible!");
          }
      } else {
        echo 'll';
      }

        // VERIFICATION DE LA QUANTITE DE PARABOLE DISPONIBLE EN STOCK CHEZ LE VENDEURS
         $stock_parabole_vendeur = StockVendeur::where([
            'vendeurs'  =>  $request->input('vendeurs'),
            'produit'   =>  Produits::where('libelle','parabole')->first()->reference
        ])->first();




        // VERIFICATION VALABLE SI LA QUANTITE DE RECRUTEMENT EST SUP A 0
        if($request->input('quantite_recrutement') > 0) {
         if($stock_parabole_vendeur) {
            if($stock_parabole_vendeur->quantite < $request->input('quantite_recrutement')) {
                return redirect('/admin/add-rapport')->with('_errors',"Quantité Parabole indisponible!");
            } else {
              // DEBITER LE STOCK DE PARABOLE DU VENDEUR
              $new_quantite_parabole = $stock_parabole_vendeur->quantite - $request->input('quantite_recrutement');
              StockVendeur::where([
                'vendeurs'  =>  $request->input('vendeurs'),
                'produit'   =>  Produits::where('libelle','parabole')->first()->reference
                ])->update([
                  'quantite'  =>  $new_quantite_parabole
                ]);
            }
         } else {
            return redirect('/admin/add-rapport')->with('_errors',"Aucune parabole en stock !");
         }
       }


        $new_quantite_material = $stock_vendeur->quantite - ($request->input('quantite_recrutement')+$request->input('quantite_migration'));
        if($new_quantite_material >= 0) {
          StockVendeur::where([
            'vendeurs'  =>  $request->input('vendeurs'),
            'produit'   =>  Exemplaire::where('status','inactif')->first()->produit
            ])->update([
              'quantite'  =>  $new_quantite_material
            ]);
        } else {
          return redirect('/admin/add-rapport')->with('_errors',"Quantite Indisponible!");
        }
        // //
        //VERIFICATION DE LA DISPONIBILITE DU SOLDE
        $account = CgaAccount::where('vendeur',$request->input('vendeurs'))->first();
        if($account->solde > ($request->input('ttc_recrutement')+$request->input('ttc_reabonnement'))) {
          // LE SOLDE EST DISPONIBLE / MISE A JOUR DU SOLDE DU VENDEUR
          $newSolde = $account->solde - ( $request->input('ttc_reabonnement') + $request->input('ttc_recrutement') );
          // dd($account->solde - (real)($request->input('ttc_reabonnement')+$request->input('ttc_recrutement')));
          CgaAccount::where('vendeur',$request->input('vendeurs'))->update([
            'solde' => $newSolde
          ]);

        } else {
          return redirect('/admin/add-rapport')->with('_errors',"Le Solde est Indisponible!");
        }
        // .///
        $rapport->save();
        return redirect('/admin/add-rapport')->with('success',"Rapport ajouté !");
    }

    public function listRapport() {
        return view('admin.list-rapport-vente');
    }
// RECUPERATION DE L'HISTORIQUE DES RAPPORTS
    public function getRapport(Request $request) {
        $allRapport =   RapportVente::all();
        $all = [];
        foreach($allRapport as $key =>  $value) {
            $temp = User::where('username',$value->vendeurs)->first()->username;
            $users = User::where('username',$value->vendeurs)->first();
            $agence = Agence::where('reference',$users->agence)->first();
            $date = new Carbon($value->created_at);
            // calcul des commisisions
            $commissionRecrutement  =   (12/100) * ($value->ttc_recrutement/1.18);
            $commissionReabonnement =   (5.5/100) * ($value->ttc_reabonnement/1.18);
            $commissionMigration    =   0;
            $commissionTotal    =   $commissionRecrutement + $commissionReabonnement + $commissionMigration;
            // ==+===
            $all[$key] = [
                'date'  =>  $date->toFormattedDateString().' | '.$date->toTimeString(),
                'recrutement'   =>  number_format($value->ttc_recrutement).' ( '.$value->quantite_recrutement.' ) ',
                'migration' =>  number_format($value->quantite_migration),
                'reabonnement'  =>  number_format($value->ttc_reabonnement),
                'vendeurs'  =>  $value->vendeurs.' ( '.$agence->societe.' ) ',
                'agence'    =>  $users->localisation,
                'comission' =>  number_format($commissionTotal)
            ];
        }
        return response()->json($all);
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
}
