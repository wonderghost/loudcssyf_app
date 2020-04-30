<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CgaAccount;
use App\Formule;
use App\CommandCredit;
use App\Credit;
use App\Afrocash;
use App\Exceptions\AppException;
use App\Notifications;
use App\Alert;
use Illuminate\Support\Facades\DB;
use App\User;

Trait Cga {

	// VERIFIER SI LE SOLDE CGA EST DISPONIBLE

	public function isAvailableSolde($formule) {
		$solde = Formule::select()->where('nom',$formule)->first()->prix;
		$user = Auth::user()->username;
		$temp = CgaAccount::select()->where('vendeur',$user)->first();
		if($temp->solde >= $solde) {
			return $temp;
		}

		return false;
	}

	public function commandCga(Request $request) {
		$validation = $request->validate([
			'montant'	=>	'required|numeric|min:50000'
		]);

		try {
			// verifier la disponibilite du montant dans le compte afrocash
			$afrocash_account = $this->getAfrocashAccountByUsername($request->user()->username);
			if(!$afrocash_account) {
				throw new AppException("Compte Afrocash inexistant!");
			}
			if($afrocash_account->solde >= $request->input('montant')) {
				$commande = new CommandCredit;
				$commande->type = 'cga';
				$commande->vendeurs = $request->user()->username;
				$commande->montant = $request->input('montant');
				// debite dans le compte afrocash courant
				$new_solde_courant	=	Afrocash::where('numero_compte',$afrocash_account->numero_compte)->first()->solde - $request->input('montant');

				Afrocash::where("numero_compte",$afrocash_account->numero_compte)->update([
					'solde'	=>	$new_solde_courant
				]);
				// credit dans le compte central afrocash
				$new_solde_central	=	Credit::where('designation','afrocash')->first()->solde + $request->input('montant');

				Credit::where('designation','afrocash')->update([
					'solde'	=>	$new_solde_central
				]);

				$commande->save();
				// ENVOI DE LA NOTIFICATION
				$n = $this->sendNotification("Commande Credit Cga" ,"Il y a une commande en attente de confirmation pour : ".Auth::user()->localisation,User::where('type','admin')->first()->username);
				$n->save();
				$n = $this->sendNotification("Commande Credit Cga" , "Vous avez envoye une commande Cga!",Auth::user()->username);
				$n->save();
				$n = $this->sendNotification("Commande Credit Cga" , "Une Commande Cga est en attente de confirmation!",User::where('type','gcga')->first()->username);
				$n->save();

				return response()
					->json('done');
			} else {
				throw new AppException("Montant Indisponible!");
			}
		}
		catch (AppException $e) {
			header("Erreur!",true,422);
			die(json_encode($e->getMessage()));
		}
	}

	public function isCgaDisponible($vendeur,$montant) {
		$temp = CgaAccount::where('vendeur',$vendeur)->first();
		if($temp && ($temp->solde >= $montant)) {
			return $temp;
		}
		return false;
	}


}
