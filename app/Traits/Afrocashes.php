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
use App\TransactionAfrocash;

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
		$vendeurs =	User::whereIn('type',['v_standart','v_da'])->get();
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
				'vendeurs'	=>	$value->username." ( ".$agence->societe." )",
				'afrocash_courant'=>	number_format($value->afroCash()->first()->solde),
				'afrocash_semi_grossiste'	=>	$solde_ac_sm,
				'cga'	=>	number_format($value->cgaAccount()->solde),
				'rex'	=> $solde_rex
			];
		}
		return response()->json($all);
	}

	// envoi de la commande semi grossiste
	public function sendCommandSemiGrossiste(Request $request) {
		try {

			if(CommandCredit::where('vendeurs',Auth::user()->username)->where('status','unvalidated')->first()) {
				throw new AppException("Une commande est deja en attente de validation !");
			}

			if(Auth::user()->type == 'v_standart') {
				$validation = $request->validate([
					'montant'	=>	'required',
					'numero_recu'	=>	'required|string',
					'piece_jointe'	=>	'required|image'
				]);
				$credit = new	CommandCredit;
				$credit->montant = $request->input('montant');
				$credit->type = 'afro_cash_sg';
				$credit->numero_recu = $request->input('numero_recu');
				$credit->vendeurs = Auth::user()->username;
				if($request->hasFile('piece_jointe')) {
					$extension = $request->file('piece_jointe')->getClientOriginalExtension();
					$credit->recu =	Str::random()."_recu_".time().'.'.$extension;
					if($request->file('piece_jointe')->move(config('image.path'),$credit->recu)) {
						$credit->save();
						return redirect('/user/new-command')->withSuccess("Success!");
					} else {
						throw new AppException("Erreur de telechargement !");
					}
				} else {
					throw new AppException("Erreur de telechargement !");
				}
			} else {
				throw new AppException("Action non autorisee!");
			}
		} catch (AppException $e) {
			return back()->with("_error",$e->getMessage());
		}

	}

	// ENVOI DE CREDIT AFROCASH AU SEMI GROSSISTE
	public function sendAfrocash(Request $request) {
		$validation = $request->validate([
			'commande'	=> 'required|exists:command_credits,id',
			'montant'	=>	'required',
			'password_confirmed'	=>	'required'
		]);
		try {
			$commande = CommandCredit::where('id',$request->input('commande'))->first();
			// tester l'invalidite de la commande
			if($this->commandCreditState($commande->id) == 'unvalidated') {
				//tester la disponibilite du montant
				if($this->getSoldeGlobal("afrocash") >= $commande->montant) {
					// tester la conformite du montant demande au montant saisi
					if($commande->montant == $request->input('montant')) {
						// validation du mote de passe
						if(!Hash::check($request->input("password_confirmed"),Auth::user()->password)) {
							throw new AppException("Mauvais mot de passe !");
						}
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
						return redirect('/user/credit-cga/commandes')->withSuccess("Success!");
					} else {
						throw new AppException("Erreur sur le montant saisi !");
					}
				} else {
					throw new AppException("Montant Indisponible!");
				}
			} else {
				throw new AppException("Deja validee!");
			}
		} catch (AppException $e) {
			return back()->with("_error",$e->getMessage());
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

	public function afrocashOperation() {
		$afrocash_compte = Afrocash::where('type','courant')->get();
		return view('afrocash.operations')->withComptes($afrocash_compte);
	}
// ENVOI DES TRANSACTION AFROCASH
	public function sendDepot(Request $request) {

		try {
			if($request->input('type_operation') == 'depot') {

				$validation = $request->validate([
						'type_operation'	=>	'required|string',
						'numero_compte_courant'	=>	'required|string|exists:afrocashes,numero_compte',
						'montant'	=>	'required',
						'password'	=>	'required|string'
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
									// enregistrement dans la table transaction credit
									$transaction_credit	=	new TransactionCredit;
									$transaction_credit->credits = 'afrocash';
									$transaction_credit->montant	=	$request->input('montant');
									// sauvegarde dans la base de donnees
									$transaction_depot->save();
									$transaction_credit->save();
									return redirect('/user/afrocash')->withSuccess("Success!");

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
					 'montant'	=>	'required',
					 'password'	=>	'required|string'
				 ]);
				 // verification du compte courant
				 if($this->getAfrocashAccountByUsername(Auth::user()->username) && $this->getAfrocashAccountByUsername($request->input('vendeurs'))) {
					 if($this->montantAfrocashAccount($this->getAfrocashAccountByUsername(Auth::user()->username)->numero_compte)) {
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
								 // enregistrement dans la table transaction credit
								 $transaction_credit	=	new TransactionCredit;
								 $transaction_credit->credits = 'afrocash';
								 $transaction_credit->montant	=	$request->input('montant');

								 $transaction_depot->save();
								 $transaction_credit->save();
								 return redirect('/user/afrocash')->withSuccess("Success!");
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
				return back()->with('_error',$e->getMessage());
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
}
