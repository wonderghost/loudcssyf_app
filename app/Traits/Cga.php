<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CgaAccount;
use App\Formule;
use App\CommandCredit;
use App\Credit;
use App\Afrocash;

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
			'montant'	=>	'required|min:1'
		]);
		try {
			// verifier la disponibilite du montant dans le compte afrocash
			$afrocash_account = $this->getAfrocashAccountByUsername(Auth::user()->username);
			if($afrocash_account->solde >= $request->input('montant')) {
				$commande = new CommandCredit;
				$commande->type = 'cga';
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
				return redirect('/user/new-command')->withSuccess("Success !");
			} else {
				throw new AppException("Montant Indisponible!");
			}
		} catch (AppException $e) {
			return back()->with("_error",$e->getMessage());
		}

	}
}
