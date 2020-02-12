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
				// return redirect('/admin/add-account-credit')->with('success',"Compte $credit->designation Credité de ".number_format($credit->solde)." GNF !");
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

    // public function getListVendeur(Request $request) {
    //     $all = User::select()->where('type','v_da')->orWhere('type','v_standart')->get();
    //     if($all) {
    //         return response()->json($all);
    //     }
    //     return response()->json('fail');
    // }

    public function isValidMontant($montant,$account = 'cga') {
        $temp = Credit::select()->where('designation',$account)->first();
        if($temp->solde >= $montant) {
            return $temp;
        }
        return false;
    }

    // // ENVOI DE CREDIT CGA
    // public function sendCga(CgaRequest $request) {
    //     $cga = CgaAccount::select()->where('vendeur',$request->input('vendeur'))->first();
    //     // dd($cga);
    //     // die();
    //     // VERIFIER SI LE MONTANT EST VALIDE
    //     if($request->input('montant') && $request->input('montant') > 0) {
    //         // VERIFIER SI LE MONTANT EST DISPONIBLE
    //         if($temp = $this->isValidMontant($request->input('montant'))) {
    //             // DEBIT DANS LE SOLDE PRINCIPALE
    //             $soldeNow = $temp->solde - $request->input('montant');
    //             Credit::select()->where('designation','cga')->update([
    //                 'solde' => $soldeNow
    //             ]);
    //             //CREDIT DANS LE SOLDE VENDEUR
    //             $cga->solde = $cga->solde + $request->input('montant');
    //             CgaAccount::select()->where('vendeur',$request->input('vendeur'))->update([
    //                 'solde' => $cga->solde
    //             ]);
    //             // ENREGISTREMENT DE L'HISTORIQUE
    //             $histo = new TransactionCga;
    //             $histo->cga = $cga->numero;
    //             $histo->montant = $request->input('montant');
    //             $histo->save();
		// 						// ENVOI DE LA NOTIFICATION
		// 						// $this->sendNotificationForGestionnaire("Commande Credit Cga" , "Command Cga Validée!",$request->input('vendeur'));
    //             return redirect('/user/cga-credit')->with('success',"Transaction effectuée !");
    //         } else {
    //             // MONTANT INDISPONIBLE
    //             return redirect('/user/cga-credit')->with('_errors',"Montant indisponible!");
    //         }
		//
    //     } else {
    //         // MONTANT INVALIDE
    //         return redirect('/user/cga-credit')->with('_errors',"Montant invalide!");
    //     }
    // }

    //  ENVOI DE CREDIT REX
    // public function sendRex(RexRequest $request) {
    //     // dd(Auth::user());
    //     $rex = RexAccount::select()->where('numero',User::select()->where('username',$request->input('vendeur'))->first()->rex)->first();
    //     // VERIFIER SI LE MONTANT EST VALIDES
    //     if($request->input('montant') && $request->input('montant') > 0) {
    //         // VERIFIER SI LE MONTANT EST DISPONIBLE
    //         if($temp = $this->isValidMontant($request->input('montant'),'rex')) {
    //             // DEBIT DANS LE SOLDE PRINCIPALE
    //             $soldeNow = $temp->solde - $request->input('montant');
    //             Credit::select()->where('designation','rex')->update([
    //                 'solde' => $soldeNow
    //             ]);
    //             // CREDITER LE SOLDE REX
    //             $rex->solde = $rex->solde + $request->input('montant');
    //             RexAccount::select()->where('numero',$rex->numero)->update([
    //                 'solde' => $rex->solde
    //             ]);
    //             //
    //             $histo = new TransactionRex;
    //             $histo->rex = $rex->numero;
    //             $histo->montant = $request->input('montant');
    //             $histo->save();
    //             return redirect('/user/rex-credit')->with('success',"Transaction effectuée!");
    //         } else {
    //             return redirect('/user/rex-credit')->with('_errors',"Montant indisponible!");
    //         }
    //     } else {
    //         // MONTANT INVALIDE
    //         return redirect('/user/rex-credit')->with('_errors',"Montant invalide!");
    //     }
    // }

		// public function getListCommandGcga(Request $request) {
		// 	$commands_unvalidated = CommandCredit::whereIn('type',['cga','afro_cash_sg'])->where('status','unvalidated')->orderBy('created_at','desc')->limit(30)->get();
		// 	$commands_validated = CommandCredit::whereIn('type',['cga','afro_cash_sg'])->where('status','validated')->orderBy('created_at','desc')->limit(30)->get();
		// 	$commands_aborted = CommandCredit::whereIn('type',['cga','afro_cash_sg'])->where('status','aborted')->orderBy('created_at','desc')->limit(30)->get();
		//
		// 	$all_unvalidated = $this->organizeCommandGcga($commands_unvalidated);
		//
		// 	$all_validated = $this->organizeCommandGcga($commands_validated);
		//
		// 	$all_aborted = $this->organizeCommandGcga($commands_aborted);
		//
		// 	return response()->json([
		// 		'unvalidated'	=>	$all_unvalidated,
		// 		'validated'	=>	$all_validated,
		// 		'aborted'	=>	$all_aborted
		// 	]);
		// }

		// public function getListCommandGrex(Request $request) {
		// 	$commands_unvalidated = CommandCredit::whereIn('type',['rex'])->where('status','unvalidated')->orderBy('created_at','desc')->get();
		// 	$commands_validated = CommandCredit::whereIn('type',['rex'])->where('status','validated')->orderBy('created_at','desc')->get();
		// 	$all_unvalidated = $this->organizeCommandGcga($commands_unvalidated);
		//
		// 	$all_validated = $this->organizeCommandGcga($commands_validated);
		//
		// 	return response()->json([
		// 		'unvalidated'	=>	$all_unvalidated,
		// 		'validated'	=>	$all_validated
		// 	]);
		// }

		public function organizeCommandGcga($list) {
			$temp = [];
			foreach($list as $key => $value) {
				$date = new \Carbon\Carbon($value->created_at);
				$date->locale('fr_FR');
				$temp[$key] = [
					'id'	=>	$value->id,
					'date'	=>	$date->toFormattedDateString()." (".$date-> diffForHumans()." )",
					'vendeurs'	=>	$value->vendeurs." (".$value->vendeurs()->localisation." )",
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
			$commande = CommandCredit::find($request->input('ref-0'));
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
					$this->sendNotification("Commande Credit","Vous avez annulé une commande pour : ".$commande->vendeurs()->localisation,Auth::user()->username);
					$this->sendNotification("Commande Credit","Votre commande a été annulé",$commande->vendeurs);
					return response()->json('done');
				} else {
					$commande->status = 'aborted';
					$commande->save();
					// ENREGISTREMENT DE LA NOTIFICATION
					$gcga = User::where("type",'gcga')->get();
					foreach($gcga as $key => $value) {
						$this->sendNotification("Commande Credit","Vous avez annulé une commande pour : ".$commande->vendeurs()->localisation,$value->username);
					}
					$this->sendNotification("Commande Credit","Votre commande a été annulé",$commande->vendeurs);
					$this->sendNotification("Commande credit","Une commande a ete annulee pour : ".$commande->vendeurs()->localisation,User::where('type','admin')->first()->username);
					return response()->json('done');
				}
				} else {
					throw new AppException("Commande deja validee!");
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
