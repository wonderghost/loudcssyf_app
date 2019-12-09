<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Afrocash;
use App\User;
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
				$solde_ac_sm = $afrocash_sg->solde;
			} else {
				$solde_ac_sm = 'inexistant';
			}
			$solde_rex = 0;

			if($value->rexAccount()->first()) {
				$solde_rex = $value->rexAccount()->first()->solde;
			} else {
				$solde_rex = 'inexistant';
			}
			$all[$key]	=	[
				'vendeurs'	=>	$value->username." ( ".$agence->societe." )",
				'afrocash_courant'=>	$value->afroCash()->first()->solde,
				'afrocash_semi_grossiste'	=>	$solde_ac_sm,
				'cga'	=>	$value->cgaAccount()->solde,
				'rex'	=> $solde_rex
			];
		}
		return response()->json($all);
	}
}
