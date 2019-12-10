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
}
