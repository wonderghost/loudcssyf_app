<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Afrocash;
use App\User;
use App\CommandCredit;
use App\Exceptions\AppException;
use App\Credit;
use App\TransactionCredit;
use App\TransactionCga;
use App\TransactionRex;
use App\TransactionAfrocash;
use App\CgaAccount;
use App\RexAccount;
use Carbon\Carbon;
use App\Notifications;
use App\Alert;
use Illuminate\Support\Facades\DB;
use App\Events\AfrocashNotification;
use App\Promo;
use App\CommandMaterial;
use App\CommandProduit;
use App\Exemplaire;
use Illuminate\Support\Arr;
use App\RavitaillementVendeur;
use App\Livraison;
use App\Produits;
use App\LivraisonSerialFile;
use App\RapportVente;
use App\RemboursementPromo;

Trait Afrocashes {

	public function  newAccount($username,$type = 'courant') {
		$account = new Afrocash;
		do {
			$account->numero_compte = mt_rand(000000000001,999999999999);
		} while($this->isExistAccount($account->numero_compte));
		$account->type = $type ;
		$account->vendeurs = $username ;
		$account->save();
	}

	public function isExistAccount($accountNumber) {
		$temp = Afrocash::where('numero_compte')->first();
		if($temp) {
			return $temp;
		}
		return false;
	}

	// recuperation des soldes vendeurs
	public function getSoldesVendeurs(Request $request) {
		// recuperation de la liste des vendeurs

		$vendeurs =	User::whereIn('type',['v_standart','v_da','logistique','pdc','pdraf'])->orderBy('localisation','asc')->get();
		$all = [];
		foreach($vendeurs as $key => $value) {
			$agence = $value->agence();
			$afrocash_sg =  $value->afroCash('semi_grossiste')->first() ;
			$solde_ac_sm = 0;
			if($afrocash_sg) {
				$solde_ac_sm = number_format($afrocash_sg->solde);
			} else {
				$solde_ac_sm = 'inexistant';
			}
			$solde_rex = 0;

			if($value->rexAccount()->first()) {
				$solde_rex = number_format($value->rexAccount()->first()->solde);
			} else {
				$solde_rex = 'inexistant';
			}

			$all[$key]	=	[
				'vendeurs'	=>	$value->localisation,
				'type'	=>	$value->type,
				'afrocash_courant'=>	$value->afroCash()->first() ? number_format($value->afroCash()->first()->solde) : 'null',
				'afrocash_semi_grossiste'	=>	$solde_ac_sm,
				'cga'	=>	$value->cgaAccount() ?	number_format($value->cgaAccount()->solde) : 'null',
				'rex'	=> $solde_rex
			];
		}
		return response()->json($all);
	}

	// envoi de la commande semi grossiste
	public function sendCommandSemiGrossiste(Request $request , CommandCredit $cc) {

		try {

			if($cc->where('vendeurs',$request->user()->username)
				->where('status','unvalidated')
				->where('type','afro_cash_sg')
				->first()) {
				throw new AppException("Une commande est deja en attente de validation !");
			}

			if(Auth::user()->type == 'v_standart') {
				$validation = $request->validate([
					'montant'	=>	'required',
					'numero_recu'	=>	'required|string|unique:command_credits,numero_recu',
					// 'piece_jointe'	=>	'required|image'
				]);
				$credit = new	CommandCredit;
				$credit->montant = $request->input('montant');
				$credit->type = 'afro_cash_sg';
				$credit->numero_recu = $request->input('numero_recu');
				$credit->vendeurs = Auth::user()->username;
				// if($request->hasFile('piece_jointe')) {
				// 	$extension = $request->file('piece_jointe')->getClientOriginalExtension();
				// 	$credit->recu =	Str::random()."_recu_".time().'.'.$extension;
				// 	if($request->file('piece_jointe')->move(config('image.path'),$credit->recu)) {
					
				$credit->save();
				// CREATION DE LA NOTIFICATION
				$n = $this->sendNotification("Commande Afrocash" , "Vous avez envoyer une command Afrocash Grossiste",Auth::user()->username);
				$n->save();
				$n = $this->sendNotification("Commande Afrocash" , "Vous avez une commande Afrocash en attente de confirmation!",User::where('type','gcga')->first()->username);
				$n->save();
				$n = $this->sendNotification("Commande Afrocash" , "Il y a une commande Afrocash en attente de confirmation pour : ".Auth::user()->localisation,'admin');
				$n->save();
				return response()
					->json('done');
					// } else {
					// 	throw new AppException("Erreur de telechargement !");
					// }
				// } else {
				// 	throw new AppException("Erreur de telechargement !");
				// }
			} else {
				throw new AppException("Action non autorisee!");
			}
		} catch (AppException $e) {
			header("Erreur!",true,422);
			die(json_encode($e->getMessage()));
		}
	}

	// ENVOI DE CREDIT AFROCASH ,CGA ET REX
	public function sendAfrocash(Request $request) {
		$validation = $request->validate([
			'commande'	=> 'required|exists:command_credits,id',
			'montant'	=>	'required',
			'password_confirmed'	=>	'required'
		],[
			'required'	=> ':attribute ne peut etre vide!'
		]);
		try {
			// validation du mote de passe
			if(!Hash::check($request->input("password_confirmed"),Auth::user()->password)) {
				throw new AppException("Mauvais mot de passe !");
			}
			$commande = CommandCredit::where('id',$request->input('commande'))->first();
			// tester l'invalidite de la commande
			if($this->commandCreditState($commande->id) == 'unvalidated') {
				// tester la conformite du montant demande au montant saisi
				if($commande->montant == $request->input('montant')) {
					switch ($request->input('type_commande')) {
						case 'cga':
						// tester la disponibilite du montant
						if($this->getSoldeGlobal("cga") >= $commande->montant) {
							$cga_account = CgaAccount::where('vendeur',$commande->vendeurs)->first();
							// crediter le compte cga du vendeur
							$new_solde_cga_vendeur = $cga_account->solde + $request->input('montant');
							CgaAccount::where('numero',$cga_account->numero)->update([
								'solde'	=>	$new_solde_cga_vendeur
							]);
							// debiter le compte central cga
							$new_solde_cga_central	=	Credit::where('designation','cga')->first()->solde - $request->input('montant');
							Credit::where('designation','cga')->update([
								'solde'	=>	$new_solde_cga_central
							]);

							$transaction_cga	= new	TransactionCga;

							$transaction_cga->cga	=	$cga_account->numero;
							$transaction_cga->montant	=	$request->input('montant');

							$transaction_cga->save();
							CommandCredit::where("id",$commande->id)->update([
								'status'	=>	'validated'
							]);
							// ENVOI DE LA NOTIFICATION
							$n = $this->sendNotification("Commande Credit cga" ,"Une commande cga a ete valide pour : ".$commande->vendeurs()->localisation,User::where('type','admin')->first()->username);
							$n->save();
							$n = $this->sendNotification("Commande Credit Cga" , "Votre Commande cga a ete valide",$commande->vendeurs);
							$n->save();
							$n = $this->sendNotification("Commande Credit Cga" , "Vous avez valide une commande cga pour : ".$commande->vendeurs()->localisation,Auth::user()->username);
							$n->save();

							return response()
								->json('done');

						} else {
							throw new AppException("Montant Indisponible!");
						}
							break;
						case 'afro_cash_sg':
						//tester la disponibilite du montant
						if($this->getSoldeGlobal("afrocash") >= $commande->montant) {
							$afrocashAccount = Afrocash::where([
								'vendeurs'	=>	$commande->vendeurs,
								'type'	=>	'semi_grossiste'
							])->first();

							$transaction = new TransactionAfrocash;
							$transaction->compte_credite = $afrocashAccount->numero_compte;
							$transaction->montant = $commande->montant;

							$transaction_credit = new TransactionCredit;
							$transaction_credit->credits = 'afrocash';
							$transaction_credit->montant = $commande->montant;

							$new_solde = Credit::where('designation','afrocash')->first()->solde -  $commande->montant ;

							Credit::where('designation','afrocash')->update([
								'solde'	=>	$new_solde
							]);

							$new_solde_vendeurs = Afrocash::where(['vendeurs'	=>	$commande->vendeurs,'type'	=>	'semi_grossiste'])->first()->solde + $request->input('montant');

							Afrocash::where([
								'vendeurs'	=>	$commande->vendeurs,
								'type'	=>	'semi_grossiste'
							])->update([
								'solde'	=>	$new_solde_vendeurs
							]);

							$transaction->save();
							$transaction_credit->save();
							CommandCredit::where('id',$commande->id)->update([
								'status'	=>	'validated'
							]);

							$n = $this->sendNotification("Commande Afrocash" ,"Une Commande Afrocash a ete valide pour :".$commande->vendeurs()->localisation,User::where('type','admin')->first()->username);
							$n->save();
							$n = $this->sendNotification("Commande Afrocash" , "Votre Commande Afrocash a ete valide!",$commande->vendeurs);
							$n->save();
							$n = $this->sendNotification("Commande Afrocash" , "Vous avez valide une commande Afrocash! pour : ".$commande->vendeurs()->localisation,Auth::user()->username);
							$n->save();

							return response()
								->json('done');
							// ENVOI DE LA NOTIFICATION
						} else {
							throw new AppException("Montant Indisponible!");
						}
							break;
						case 'rex':
						// tester la disponibilite du montant
						if($this->getSoldeGlobal("rex") >= $commande->montant) {

							// dd(User::where('username',$commande->vendeurs)->first());
							$rex_account = RexAccount::where('numero',User::where('username',$commande->vendeurs)->first()->rex)->first();

							// dd($rex_account);
							// crediter le compte cga du vendeur
							$new_solde_rex_vendeur = $rex_account->solde + $request->input('montant');
							RexAccount::where('numero',$rex_account->numero)->update([
								'solde'	=>	$new_solde_rex_vendeur
							]);

							// debiter le compte central cga
							$new_solde_rex_central	=	Credit::where('designation','rex')->first()->solde - $request->input('montant');
							Credit::where('designation','rex')->update([
								'solde'	=>	$new_solde_rex_central
							]);

							$transaction_rex	= new	TransactionRex;

							$transaction_rex->rex	=	$rex_account->numero;
							$transaction_rex->montant	=	$request->input('montant');

							$transaction_rex->save();
							CommandCredit::where("id",$commande->id)->update([
								'status'	=>	'validated'
							]);
							$this->sendNotification("Commande Credit Rex" ,"Une commande Rex a ete valide",User::where('type','admin')->first()->username);
							$this->sendNotification("Commande Credit Rex" , "Votre Commande rex a ete valide",$commande->vendeurs);
							$this->sendNotification("Commande Credit Rex" , "Vous avez valide une commande rex",Auth::user()->username);
							return response()
								->json('done');
						} else {
							throw new AppException("Montant Indisponible!");
						}
							break;
						default:
							// code...
							break;
					}

				} else {
					throw new AppException("Erreur sur le montant saisi !");
				}
			} else {
				throw new AppException("Deja validee!");
			}
		} catch (AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}

	}

	// status de la commande
	public function commandCreditState($id) {
		return CommandCredit::where("id",$id)->first()->status;
	}

	// solde gloal
	public function getSoldeGlobal($type = "afrocash") {
		if($type == 'afrocash') {
			return Credit::where('designation','afrocash')->first()->solde;
		} else if($type == 'cga') {
			return Credit::where('designation','cga')->first()->solde;
		} else {
			return Credit::where('designation','rex')->first()->solde;
		}
	}
	// OPERATIONS AFROCASH


	#@@@ get account list
	public function getAccountList(Afrocash $a,User $u) {
		try {
			$temp = $a->select(['numero_compte','vendeurs'])->where('type','courant')
				->whereIn('vendeurs',$u->select('username')
					->whereIn('type',['v_da','v_standart']))
				->get();
			
			$_temp = $a->select(['numero_compte','vendeurs'])->where('type','semi_grossiste')
				->whereIn('vendeurs',$u->select('username')
					->whereIn('type',['pdc']))
				->get();

			return response()
				->json([
					'account_da' => $this->organizeAccountList($temp),
					'account_pdc'	=>	$this->organizeAccountList($_temp)
					]);
		}catch (AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}
	public function organizeAccountList($data) {
		$all =[];
		foreach($data as $key => $value) {
			$all [$key] =[
				'numero_compte'	=>	$value->numero_compte ,
				'vendeurs'	=>	$value->vendeurs()->localisation,
				'id_vendeurs'	=>	$value->vendeurs
			];
		}
		return $all;
	}
// ENVOI DES TRANSACTION AFROCASH
	public function sendDepot(Request $request) {
		try {
			if($request->input('type_operation') == 'depot') {

				$validation = $request->validate([
						'type_operation'	=>	'required|string',
						'numero_compte_courant'	=>	'required|string|exists:afrocashes,numero_compte',
						'montant'	=>	'required|numeric|min:50000',
						'password'	=>	'required|string'
				],[
					'required'	=>	'Champ(s) `:attribute` requis!',
					'exists'	=>	'Compte afrocash inexistant'
				]);

				if(!Afrocash::where([
					'vendeurs'	=>	Auth::user()->username,
					'type'	=>	'semi_grossiste'
				])->first()) {
					throw new AppException("Operation Indisponible pour ce type de compte !");
				}
				// depot
				if($this->afrocashTypeAccount($request->input("numero_compte_courant")) == 'courant') { //verification de la validite du type de compte
					if(Afrocash::where([
						'vendeurs'	=>	Auth::user()->username,
						'type'	=>	'semi_grossiste'
						])->first()->solde >= $request->input('montant')) {
							if(Hash::check($request->input('password'),Auth::user()->password)) {

								// debiter l'expediteur

								$new_solde_expediteur = Afrocash::where([
									'vendeurs'	=>	Auth::user()->username,
									'type'	=>	'semi_grossiste'
								])->first()->solde - $request->input('montant');

								Afrocash::where([
									'vendeurs'	=>	Auth::user()->username,
									'type'	=>	'semi_grossiste'
								])->update([
									'solde'	=>	$new_solde_expediteur
								]);

								// crediter le destinataire

								$new_solde_destinataire = Afrocash::where('numero_compte',$request->input('numero_compte_courant'))->first()->solde + $request->input('montant');

								Afrocash::where('numero_compte',$request->input("numero_compte_courant"))->update([
									'solde'	=>	$new_solde_destinataire
								]);
								// enregistrement de la transaction
								$transaction_depot = new TransactionAfrocash;
								$transaction_depot->compte_debite = Afrocash::where([
									'vendeurs'	=>	Auth::user()->username,
									'type'	=>	'semi_grossiste'
									])->first()->numero_compte;
									$transaction_depot->compte_credite = $request->input('numero_compte_courant');
									$transaction_depot->montant	=	$request->input('montant');
									$transaction_depot->motif = "Depot Afrocash";
									// enregistrement dans la table transaction credit
									$transaction_credit	=	new TransactionCredit;
									$transaction_credit->credits = 'afrocash';
									$transaction_credit->montant	=	$request->input('montant');
									// sauvegarde dans la base de donnees
									$transaction_depot->save();
									$transaction_credit->save();
									$vendeurs = Afrocash::where('numero_compte',$request->input('numero_compte_courant'))->first()->vendeurs ;
									$n = $this->sendNotification("Depot Afrocash" , "Depot de  ".number_format($request->input('montant'))." GNF effectué par :".Auth::user()->localisation." sur le compte de :".User::where('username',$vendeurs)->first()->localisation,'admin');
									$n->save();
									$n = $this->sendNotification("Depot Afrocash" , "Reception de ".number_format($request->input('montant'))." GNF de la part de ".Auth::user()->localisation,$vendeurs);
									$n->save();
									$n = $this->sendNotification("Depot Afrocash" , "Vous avez effectue un depot de ".number_format($request->input('montant'))." GNF pour ".User::where('username',$vendeurs)->first()->localisation,Auth::user()->username);
									$n->save();
									return response()
									->json('done');

							} else {
								throw new AppException("Mot de passe Invalide!");
							}
					} else {
						throw new AppException("Montant Indisponible!");
					}
				} else {
					throw new AppException("Transaction indisponible pour ce type de compte!");
				}
			}
			 else if($request->input('type_operation')	==	'transfert_courant') {
				 // transfert courant
				 $validation = $request->validate([
					 'vendeurs'	=>	'required|exists:users,username',
					 'montant'	=>	'required|numeric|min:10000',
					 'password'	=>	'required|string'
				 ],[
					 'required'	=>	'Champs :attribute requis!',
					 'exists'	=>	':attribute n\'existe pas dans la base de donnees'
				 ]);
				 // verification du compte courant
				 if($this->getAfrocashAccountByUsername(Auth::user()->username) && $this->getAfrocashAccountByUsername($request->input('vendeurs'))) {
					 if($this->montantAfrocashAccount($this->getAfrocashAccountByUsername(Auth::user()->username)->numero_compte) > 0
					 		&&
					 		$this->montantAfrocashAccount($this->getAfrocashAccountByUsername(Auth::user()->username)->numero_compte) >= $request->input('montant')) {
						 if(Hash::check($request->input('password'),Auth::user()->password)) {
							 // debiter l'expediteur
							 $new_solde_expediteur = Afrocash::where([
								 'numero_compte'	=>	$this->getAfrocashAccountByUsername(Auth::user()->username)->numero_compte
							 ])->first()->solde - $request->input('montant');

							 Afrocash::where([
								 'numero_compte'	=>	$this->getAfrocashAccountByUsername(Auth::user()->username)->numero_compte
							 ])->update([
								 'solde'	=>	$new_solde_expediteur
							 ]);

							 // crediter le destinataire

							 $new_solde_destinataire = Afrocash::where('numero_compte',	$this->getAfrocashAccountByUsername($request->input('vendeurs'))->numero_compte)->first()->solde + $request->input('montant');

							 Afrocash::where('numero_compte',$this->getAfrocashAccountByUsername($request->input('vendeurs'))->numero_compte)->update([
								 'solde'	=>	$new_solde_destinataire
							 ]);
							 // enregistrement de la transaction
							 $transaction_depot = new TransactionAfrocash;

							 $transaction_depot->compte_debite = Afrocash::where([
								 'numero_compte'	=>	$this->getAfrocashAccountByUsername(Auth::user()->username)->numero_compte
								 ])->first()->numero_compte;

								 $transaction_depot->compte_credite = $this->getAfrocashAccountByUsername($request->input('vendeurs'))->numero_compte;
								 $transaction_depot->montant	=	$request->input('montant');
								 $transaction_depot->motif = "Transfert Afrocash";
								 // enregistrement dans la table transaction credit
								 $transaction_credit	=	new TransactionCredit;
								 $transaction_credit->credits = 'afrocash';
								 $transaction_credit->montant	=	$request->input('montant');

								 $transaction_depot->save();
								 $transaction_credit->save();
								 // $this->sendNotificationForGestionnaire("Transfert Afrocash" , "Transaction de  ".number_format($request->input('montant'))." GNF effectué",$request->input('vendeurs'));
								 $n = $this->sendNotification("Transfert Afrocash" , "Reception de ".number_format($request->input('montant'))." GNF de la part de ".Auth::user()->localisation,$request->input('vendeurs'));
								 $n->save();
								 $n = $this->sendNotification("Transfert Afrocash" , "Vous avez effectue un transfert de ".number_format($request->input('montant'))." GNF pour ".User::where('username',$request->input('vendeurs'))->first()->localisation,Auth::user()->username);
								 $n->save();

								 return response()
								 	->json('done');
						 }
						 else {
							 throw new AppException("Mot de passe Invalide !");
						 }
					 }
					 else {
						 throw new AppException("Montant Indisponible !");
					 }
				 }
				 else {
					 throw new AppException("Operation Indisponible pour ce type de compte !");
				 }
			 }
			 else {
				 // retrait
				 dd($request);
			 }
		} catch (AppException $e) {
				header("Erreur",true,422);
				die(json_encode($e->getMessage()));
		}

	}

	// type compte afrocash
	public function afrocashTypeAccount($numero) {
		return Afrocash::where('numero_compte',$numero)->first()->type;
	}
	//montant compte afrocash
	public function montantAfrocashAccount($numero) {
		return Afrocash::where('numero_compte',$numero)->first()->solde;
	}

	public function getAfrocashAccountByUsername($username) {
		$temp = Afrocash::where([
			'vendeurs'	=>	$username,
			'type'	=>	'courant'
			])->first();
			if($temp) {
				return $temp;
			}
			return false;
	}
	// Toutes les transaction afrocash
	
	public function getAllTransaction(TransactionAfrocash $t , Request $request) {
		try {
			$all = [];
			$thisMonth = Date('m');
			
			if($request->user()->type !== 'admin' && $request->user()->type !== 'commercial' && $request->user()->type !== 'gcga') {

				$afrocashAccount = $request->user()->type == 'v_standart' || $request->user()->type == 'pdc' ? $request->user()->afroCash('semi_grossiste')->first() : $request->user()->afroCash('courant')->first();

				$trans = $t->select()->orderBy('created_at','desc')
					->where('compte_debite',$afrocashAccount->numero_compte)
					->orWhere('compte_credite',$afrocashAccount->numero_compte)
					->paginate(100);
			}
			else {
				$trans = $t->select()->orderBy('created_at','desc')
					->paginate(100);
			}

			foreach($trans as $key	=>	$value) {
				$c = new Carbon($value->created_at);
				$all[$key] = [
					'date'	=>	$c->toDateTimeString(),
					'expediteur'	=>	$value->compte_debite ? $value->afrocash()->vendeurs()->localisation : '-',
					'destinataire'	=>	$value->compte_credite ? $value->afrocashcredite()->vendeurs()->localisation : '-',
					'montant'	=>	$value->montant,
					'motif'	=>	$value->motif
				];
			}
			return response()
				->json([
					'all'	=>	$all,
					'next_url'	=> $trans->nextPageUrl(),
					'last_url'	=> $trans->previousPageUrl(),
					'per_page'	=>	$trans->perPage(),
					'current_page'	=>	$trans->currentPage(),
					'first_page'	=>	$trans->url(1),
					'first_item'	=>	$trans->firstItem(),
					'total'	=>	$trans->total()
				]);
		} catch (AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}


	public function getAllTransactionAfrocashVendeur(Request $request , $filter = false) {
		try {
			if($filter) {

				$transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('vendeurs',Auth::user()->username)->get())
				->whereBetween('created_at',[$request->input('date_debut'),$request->input('date_fin')])
				->orWhere(function ($query) {
					$query->whereIn('compte_credite',Afrocash::select('numero_compte')->where('vendeurs',Auth::user()->username)->get());
				})->whereBetween('created_at',[$request->input('date_debut'),$request->input('date_fin')])->get();
			} else {
				$transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('vendeurs',Auth::user()->username)->get())
				->orWhere(function ($query) {
					$query->whereIn('compte_credite',Afrocash::select('numero_compte')->where('vendeurs',Auth::user()->username)->get());
				})->orderBy('created_at','desc')->limit(30)->get();
			}

				$all = [];
				foreach($transactions as $key => $value) {
					$date = new Carbon($value->created_at);
					$date->setLocale('fr_FR');
					$all[$key] = [
						'date'	=>	$date->toFormattedDateString()."(".$date->diffForHumans().")",
						'expediteur'	=> is_null($value->compte_debite) ? "Ravitaillment" : $value->afrocash()->vendeurs()->localisation,
						'destinataire'	=>	$value->afrocashcredite()->vendeurs()->localisation,
						'montant'	=>	number_format($value->montant)
					];
				}
			return response()->json($all);
		} catch (AppException $e) {
			header("unprocessible entity",true,422);
			die(json_encode($e->getMessage()));
		}

	}
// FILTRER L'HISTORIQUE DES TRANSACTIONS AFROCASH
public function filterTransactionAfrocash(Request $request) {
	try {
		if($request->input('date_debut') && $request->input('date_fin')) {
			return $this->getAllTransactionAfrocashVendeur($request,true);
		} else {
			throw new AppException("Selectionnez les dates pour activer le filtre!");
		}
	} catch (AppException $e) {
		header("unprocessible entity",true,422);
		die(json_encode($e->getMessage()));
	}
}

// GET INFOS REMBOURSEMENT PROMO 

public function getInfosRemboursementPromo(Request $request,
	Produits $produit,
	Livraison $l ,
	Promo $p , 
	commandMaterial $cm , 
	Exemplaire $e , 
	RavitaillementVendeur $rv,
	LivraisonSerialFile $lf,
	CommandProduit $cp
	) {
	try {
		#command material promo 
		return response()
			->json($this->getInfosRemboursementByUsers(
				$request->user()->username,
				$produit,
				$l,
				$p,
				$cm,
				$e,
				$rv,
				$lf,
				$cp
				)
			);
		
	} catch(AppException $e) {
		header("Erreur",true,422);
		die(json_encode($e->getMessage()));
	}
}
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	public function getInfosRemboursementByUsers($vendeur,
		Produits $produit,
		Livraison $l ,
		Promo $p , 
		commandMaterial $cm , 
		Exemplaire $e , 
		RavitaillementVendeur $rv,
		LivraisonSerialFile $lf,
		CommandProduit $cp
		) {
		
			#command material promo 
			$promo = $this->isExistPromo();
			$promoState = true;
			if(!$promo) {
				// throw new AppException("Aucune promo en cours !");
				$promo = $p->all()->first();
				$promoState = false;
			}
			
			$terminal = $produit->where('with_serial',1)->first();

			$command_promo = $cm->select('id_commande')
				->where('vendeurs',$vendeur)
				->whereIn('status',['confirmed','unconfirmed'])
				->where('promos_id',$promo->id)->get();

			$commandPromoQuantite = $cp->whereIn('commande',$command_promo)
				->where('produit',$terminal->reference)
				->sum('quantite_commande');
			
			$rapportPromo = RapportVente::where('promo',$promo->id)
				->where('vendeurs',$vendeur)
				->whereIn('type',['recrutement','migration'])
				->whereBetween('date_rapport',[$promo->debut,$promo->fin])
				->sum('quantite');

			return [
					'kits'	=>	$commandPromoQuantite - $rapportPromo,
					'remboursement'	=>	($commandPromoQuantite - $rapportPromo) * $promo->subvention,
					'promo_id'	=>	$promo->id,
					'promo_state'	=>	$promoState
				];
	}
	#LISTING DES REMBOURSEMENT LIEES A LA PROMO
	public function getRemboursementForUsers( 
		Request $request,
		User $u,
		Produits $produit,
		Livraison $l ,
		Promo $p ,
		commandMaterial $cm , 
		Exemplaire $e , 
		RavitaillementVendeur $rv,
		LivraisonSerialFile $lf,
		CommandProduit $cp,
		RemboursementPromo $rp
		) {
		try {
			$vendeurs = $u->whereIn('type',['v_da','v_user'])
				->get();
			$data = [];
			foreach($vendeurs as $key => $value) {
				
				$remboursement = $this->getInfosRemboursementByUsers(
					$value->username,
					$produit,
					$l,
					$p,
					$cm,
					$e,
					$rv,
					$lf,
					$cp
				);
				
				$_data = $rp->where('vendeurs',$value->username)
					->where('promo_id',$remboursement['promo_id'])
					->first();

				
				if($_data) {
					$data[$key] = [
						'vendeur'	=>	$value->localisation,
						'remboursement'	=>	$remboursement,
						'pay_at'	=> $_data->pay_at ? $_data->pay_at : '-',
						'status'	=>	$_data->pay_at ? 'regler' : '-'
					];
				} else {
					$data[$key] = [
						'vendeur'	=>	$value->localisation,
						'remboursement'	=>	$remboursement,
						'pay_at'	=>	'',
						'status'	=>	''
					];
				}
				// MISE A JOUR DE LA TABLE DE REMBOURSEMENT
				if($data) {
					$this->updateRemboursementTable($data[$key] , $value->username);
				}
			}
			return response()
				->json($data);
				// ->json($_data);
		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}
	// FILTER LA LISTE DE REMBOURSEMENT POUR LES UTILISATEURS
	public function makeFilterRemboursementUsers(
		Request $request,
		User $u,
		Produits $produit,
		Livraison $l ,
		Promo $p , 
		commandMaterial $cm , 
		Exemplaire $e , 
		RavitaillementVendeur $rv,
		LivraisonSerialFile $lf,
		CommandProduit $cp,
		RemboursementPromo $rp
	) {
		try {
			$vendeurs = $u->whereIn('type',['v_da','v_user'])
				->get();
			$data = [];

			foreach($vendeurs as $key => $value) {
				
				$remboursement = $this->FilterGetRemboursementForUsers(
					$request,
					$value->username,
					$produit,
					$l,
					$p,
					$cm,
					$e,
					$rv,
					$lf,
					$cp
				);
				
				$_data = $rp->where('vendeurs',$value->username)
					->where('promo_id',$remboursement['promo_id'])
					->first();

				if($_data) {
					$data[$key] = [
						'vendeur'	=>	$value->localisation,
						'remboursement'	=>	$remboursement,
						'pay_at'	=> $_data->pay_at ? $_data->pay_at : '-',
						'status'	=>	$_data->pay_at ? 'regler' : '-'
					];
				} else {
					$data[$key] = [
						'vendeur'	=>	$value->localisation,
						'remboursement'	=>	$remboursement,
						'pay_at'	=>	'',
						'status'	=>	''
					];
				}
				// MISE A JOUR DE LA TABLE DE REMBOURSEMENT
				if($data) {
					$this->updateRemboursementTable($data[$key] , $value->username);
				}
			}
			return response()
				->json($data);
		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}
	
	public function FilterGetRemboursementForUsers(
		Request $request,
		$vendeur,
		Produits $produit,
		Livraison $l ,
		Promo $p , 
		commandMaterial $cm , 
		Exemplaire $e , 
		RavitaillementVendeur $rv,
		LivraisonSerialFile $lf,
		CommandProduit $cp
	) {
		try {
			$promo = $p->find($request->input('id_promo'));
			
			$terminal = $produit->where('with_serial',1)->first();

			$command_promo = $cm->select('id_commande')
				->where('vendeurs',$vendeur)
				->whereIn('status',['confirmed','unconfirmed'])
				->where('promos_id',$promo->id)->get();

			$commandPromoQuantite = $cp->whereIn('commande',$command_promo)
				->where('produit',$terminal->reference)
				->sum('quantite_commande');
			
			$rapportPromo = RapportVente::where('promo',$promo->id)
				->where('vendeurs',$vendeur)
				->whereIn('type',['recrutement','migration'])
				->whereBetween('date_rapport',[$promo->debut,$promo->fin])
				->sum('quantite');

			return [
				'kits'	=>	$commandPromoQuantite - $rapportPromo,
				'remboursement'	=>	($commandPromoQuantite - $rapportPromo) * $promo->subvention,
				'promo_id'	=>	$promo->id,
			];
			
		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}

	// MISE A JOUR DANS LA TABLE DE REMBOURSEMENT

	public function updateRemboursementTable($remboursementData , $vendeur) {
		
		$_rm = RemboursementPromo::where('vendeurs',$vendeur)
			->where('promo_id',$remboursementData['remboursement']['promo_id'])
			->first();
		if(!$_rm) {
			$rm = new RemboursementPromo;
			$rm->vendeurs = $vendeur;
			$rm->kits  = $remboursementData['remboursement']['kits'];
			$rm->montant = $remboursementData['remboursement']['remboursement'];
			$rm->promo_id = $remboursementData['remboursement']['promo_id'];
			$rm->save();
		} else {
			$_rm->kits = $remboursementData['remboursement']['kits'];
			$_rm->montant = $remboursementData['remboursement']['remboursement'];
			$_rm->save();
		}

	}

	// LISTING TABLE REMBOURSEMENT PROMO
	public function getRemboursementListing(Request $request , RemboursementPromo $rp) {
		try {
			$data = $rp->where('vendeurs',$request->user()->username)->get();
			$_data = [];
			foreach($data as $key => $value) {
				$_data[$key] = [
					'kits'	=>	$value->kits,
					'montant'	=>	$value->montant,
					'pay_at'	=>	$value->pay_at ? $value->pay_at : '-',
					'status'	=>	$value->pay_at ? 'regler' : '-',
					'promo'	=>	$value->promos()
				];
			}
			return response()
				->json($_data);
		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}
}
