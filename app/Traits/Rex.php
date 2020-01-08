<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\RexAccount;
use App\Formule;
use App\CommandCredit;
use App\Credit;
use App\Afrocash;
use App\Exceptions\AppException;

use App\Notifications;
use App\Alert;
use Illuminate\Support\Facades\DB;

Trait Rex {

	public function commandRex(Request $request) {
		$validation = $request->validate([
			'montant'	=>	'required|numeric|min:50000'
		]);

		// dd($request);
		try {
			// verifier la disponibilite du montant dans le compte afrocash
			$afrocash_account = $this->getAfrocashAccountByUsername(Auth::user()->username);
			if($afrocash_account->solde >= $request->input('montant')) {
				$commande = new CommandCredit;
				$commande->type = 'rex';
				$commande->vendeurs = Auth::user()->username;
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
				$this->sendNotification("Commande Rex" , "Commande de Credit Rex est en attente de confirmation!",'grex');
				return redirect('/user/new-command')->withSuccess("Success !");
			} else {
				throw new AppException("Montant Indisponible!");
			}
		} catch (AppException $e) {
			return back()->with("_error",$e->getMessage());
		}

	}
}
