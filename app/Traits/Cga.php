<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CgaAccount;
use App\Formule;
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

	public function commandCga() {
		
	}
}