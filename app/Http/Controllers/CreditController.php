<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\CreditRequest;
use App\Http\Requests\CgaRequest;
use App\Http\Requests\RexRequest;
use App\Credit;
use App\Traits\Similarity;
use App\Traits\Afrocashes;
use App\Traits\Cga;
use App\Traits\Rex;

use App\User;
use App\CgaAccount;
use App\Afrocash;
use App\RexAccount;
use App\TransactionCga;
use App\TransactionRex;
use App\TransactionCredit;
use App\Agence;
use App\CommandCredit;
use App\Exceptions\AppException;

use App\Notifications;
use App\Alert;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
	use Similarity;
	use Afrocashes;
	use Cga;
	use Rex;

// 	INVENTAIRE EN CREDIT CHEZ LES VENDEURS

		public function getCreditForVendeurs(Request $request) {
			try {

				$all = [
					'cga'	=>	$request->user()->cgaAccount()->solde,
					'Afrocash Courant'	=>	$request->user()->afroCash('courant')->first()->solde,
					'Afrocash Grossiste'	=>	$request->user()->afroCash('semi_grossiste')->first() ? $request->user()->afroCash('semi_grossiste')->first()->solde : 'N/A',
					'rex'	=>	$request->user()->rexAccount()->first() ? $request->user()->rexAccount()->first()->solde : 'N/A'
				];

				return response()
					->json($all);
			} catch (AppExceptin $e) {
				header("Erreur",true,422);
				die(json_encode($e->getMessage()));
			}
		}
		// INVENTAIRE DE TOUS LE RESEAUX CHEZ L'ADMIN
		public function getCreditForAllVendeurs() {

		}
    //SOLDE DES VENDEURS
		public function soldeVendeur() {
			return view('credit.solde-vendeur');
		}
		// TOUTES LES COMMANDES
		public function commandCredit() {
			$users = User::whereIn('type',['v_da','v_standart'])->orderBy('localisation','asc')->get();
			return view('credit.commandes')->withUsers($users);
		}
		//
		public function getSoldeVendeur(Request $request) {
			$temp = $this->getSoldesVendeurs($request);
			return response()->json($temp->original);
		}

     // AJOUTER UN COMPTE
    public function addAccount() {
      return view('admin.add-account-credit');
    }

		public function getGlobalSolde(Credit $credit) {
			return $credit->all();
		}
    //
    public function makeAddAccount(CreditRequest $request) {
			try {
				// dd($request);
				$credit = new Credit;
				$credit->designation = $request->input('compte');
				$credit->solde = $request->input('montant');

				$afrocash = Credit::where('designation','afrocash')->first();
				if($afrocash) {
					if($afrocash->solde < $request->input('montant')) {
						throw new AppException("Fond Insuffisant!");
					}
				} else {
					throw new AppException("Error!");
				}

				//
				$new_solde_afrocash = Credit::where('designation','afrocash')->first()->solde - $request->input('montant');

				Credit::where('designation','afrocash')->update([
					'solde'	=>	$new_solde_afrocash
				]);
				//
				if($temp = $this->isExistCredit($credit->designation)) {
					$solde = $temp->solde + $credit->solde;
					Credit::select()->where('designation',$credit->designation)->update(
						[
							'solde' => $solde
						]);
				} else {
					$credit->save();
				}
				
				return response()
					->json('done');
			} catch (AppException $e) {
					header("Erreur!",true,422);
					die(json_encode($e->getMessage()));
			}
    }

    // CREDITER UN VENDEUR
    public function crediterVendeur() {
        return view('credit.crediter-vendeur');
    }

    public function isValidMontant($montant,$account = 'cga') {
        $temp = Credit::select()->where('designation',$account)->first();
        if($temp->solde >= $montant) {
            return $temp;
        }
        return false;
    }
		public function organizeCommandGcga($list) {
			$temp = [];
			foreach($list as $key => $value) {
				$date = new \Carbon\Carbon($value->created_at);
				$date->locale('fr_FR');
				$temp[$key] = [
					'id'	=>	$value->id,
					'date'	=>	$date->toFormattedDateString()." (".$date-> diffForHumans()." )",
					'vendeurs'	=>	$value->vendeurs()->localisation,
					'type'	=>	$value->type,
					'montant'	=>	number_format($value->montant),
					'status'	=>	$value->status,
					'numero_recu'	=>	$value->numero_recu ? $value->numero_recu : 'undefined',
					'recu'	=>	$value->recu ? $value->recu : 'undefined'
				];
			}
			return $temp;
		}

	// RECUPERATION DE LA LISTE DE TOUTES LES COMMANES , POUR L'ADMINISTRATEUR
	public function getAllCommandes(Request $request , CommandCredit $c) {
		$all = $c->select()->orderBy('created_at','desc')->get();
		return response()->json($this->organizeCommandGcga($all));
	}

	// ANNULATION D'UN COMMANDE CHEZ LA GESTIONNAIRE DE CREDIT CGA

	public function abortCommande(Request $request) {
		try {
			$commande = CommandCredit::find($request->input('command'));

			if($commande) {
				if($commande->status == "unvalidated") {
					$central = Credit::find('afrocash');
					$afrocash_courant = Afrocash::where([
						'vendeurs'	=>	$commande->vendeurs,
						'type'	=>	'courant'
					])->first();
					if($commande->type == 'cga' || $commande->type == 'rex') {

					$central->solde-=$commande->montant;
					$afrocash_courant->solde+=$commande->montant;
					$commande->status = "aborted";
					$central->save();
					$afrocash_courant->save();
					$commande->save();
					// ENREGISTREMENT DE LA NOTIFICATION
					$this->sendNotification("Commande Credit","Vous avez annul?? une commande pour : ".$commande->vendeurs()->localisation,Auth::user()->username);
					$this->sendNotification("Commande Credit","Votre commande a ??t?? annul??",$commande->vendeurs);
					return response()->json('done');
				} else {
					$commande->status = 'aborted';
					$commande->save();
					// ENREGISTREMENT DE LA NOTIFICATION
					$gcga = User::where("type",'gcga')->get();
					foreach($gcga as $key => $value) {
						$this->sendNotification("Commande Credit","Vous avez annul?? une commande pour : ".$commande->vendeurs()->localisation,$value->username);
					}
					$this->sendNotification("Commande Credit","Votre commande a ??t?? annul??",$commande->vendeurs);
					$this->sendNotification("Commande credit","Une commande a ete annulee pour : ".$commande->vendeurs()->localisation,'admin');
					$this->sendNotification("Commande credit","Une commande a ete annulee pour : ".$commande->vendeurs()->localisation,'root');
					return response()->json('done');
				}
				} else {
					throw new AppException("Commande deja validee , vous ne pouvez pas l'annuler!");
				}
			} else {
					throw new AppException("ERREUR!");
			}
		} catch (AppException $e) {
			header("unprocessible entity",true,422);
			die(json_encode($e->getMessage()));
		}

	}

}
