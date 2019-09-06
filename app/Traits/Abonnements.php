<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Formule;
// use App\Http\Requests\RecrutementRequest;
use App\Exemplaire;
use Carbon\carbon;
use App\Lesclients;
use App\Recrutements;
// use App\StockVendeur;
use App\CgaAccount;
use App\Abonnement;
use App\Http\Requests\SearchRequest;

Trait Abonnements {
	// use Cga;

	public function activerAbonnement(SearchRequest $request) {
		// RECHERCHE PAR NUMERO MATERIEL 
		$result = [];
		$materiel = Recrutements::select()->where('serial_number',$request->input('search'))->first();
		// $test = Lesclients::select()->where('phone',$request->input('search'))->first();
		$tmp = Lesclients::select()->where('phone',$request->input('search'))->first();
		$byphone = null;
		if($tmp) {
			$byphone	=	Recrutements::select()->where('clients',$tmp->num)->first();
		}

		$client	=	Recrutements::select()->where('clients',$request->input('search'))->first();

		if($materiel) {
			// PAR NUMERO MATERIEL 
			$clientByMat = Lesclients::select()->where('num',$materiel->clients)->first();
			$result	=	[
				'materiel'	=>	$materiel->serial_number,
				'num_client'	=>	$materiel->clients,
				'telephone'	=>	$clientByMat->phone,
				'nom'	=>	$clientByMat->nom,
				'prenom'	=>	$clientByMat->prenom
			];

			return response()->json($result);
		} else if ($byphone) {
			// PAR NUMERO DE TELEPHONE
			
			$result = [
				'materiel'	=>	$byphone->serial_number,
				'num_client'	=>	$byphone->clients,
				'telephone'	=>	$request->input('search'),
				'nom'	=>	$tmp->nom,
				'prenom'	=>	$tmp->prenom
			];
			return response()->json($result);
		} else if($client) {
			// PAR NUMERO CLIENT
			return response()->json($client);
		} else {
			// AUCUNE CORRESPONDANCE
			return response()->json('fail');
		}
	}
}