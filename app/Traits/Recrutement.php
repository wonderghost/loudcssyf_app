<?php


namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Formule;
use App\Http\Requests\RecrutementRequest;
use App\Exemplaire;
use Carbon\carbon;
use App\Lesclients;
use App\Recrutements;
use App\StockVendeur;
use App\CgaAccount;
use App\Abonnement;
use App\Produits;

Trait Recrutement {

	// use Cga;
	public function isExistNumAbonne($num)  {
		$temp = Abonnement::select()->where('numero',$num)->first();

		if($temp) {
			return $temp;
		}
		return false;
	}

	// VERIFIER SI LE MATERIEL POSSEDE DEJA UN ABONNEMENT
	public function isHasAbonnement($materiel) {
		$temp = Abonnement::select()->where('serial_number',$materiel)->get();
	}

	// AJOUTER UN ABONNEMENT WITH RECRUTEMENT
	public function makeAbonnement(RecrutementRequest $request,$client) {

		$abonnement = new Abonnement;
		do {
			$abonnement->numero = random_int(1000,9999);
		} while ($this->isExistNumAbonne($abonnement->numero));
		
		$abonnement->formules = $request->input('formule');
		$abonnement->vendeur = Auth::user()->username;
		$abonnement->client = $client;
		$abonnement->debut = new Carbon($request->input('debut'));
		$abonnement->fin = new Carbon($request->input('fin'));
		$abonnement->materiel = $request->input('serial_number');
		// AJOUT DE LA DUREE DE L'ABONNEMENT 
		$abonnement->duree_abonnement = $request->input('duree');
		// $abonnement->status = 'en_cours';
		// VERIFIER LE SOLDE CGA
		if($cga = $this->isAvailableSolde($request->input('formule'))) {
			// DEBITER LE SOLDE CGA
			$newSolde = $cga->solde - Formule::select()->where('nom',$request->input('formule'))->first()->prix;

			CgaAccount::select()->where("numero",$cga->numero)->update([
				'solde' => $newSolde
			]);
			// AJOUT DE L'ABONNEMENT
			$abonnement->save();
		} else {
			// SOLDE INDISPONIBLE
			return redirect('/user/recrutement')->with("_errors","Solde CGA insuffisant!");
		}

	}

	// 

	public function recrutement() {
		// $material = Produits::select()->where()->first();
		return view('ventes.recrutement');
	}

	public function isExisteNumClient($num) {
		$temp = Lesclients::select()->where('num',$num)->first();
		if($temp) {
			return $temp;
		}
		return false;
	}

	public function getFormule() {
		$formules = Formule::all();
		if($formules) {
			return response()->json($formules);
		}
		return response()->json("fail");
	}

	public function isValidSn(Request $request) {

		// VERIFIER LA VALIDITE DU SERIAL NUMBER
		$user = Auth::user()->username;
		$sn = Exemplaire::select()->where('vendeurs',$user)
									->where('serial_number',$request->input('ref'))
									->where('status','inactif')->first();
		if($sn) {
			return response()->json($sn);
		}
		return response()->json('fail');
	}

	public function makeRecrutement(RecrutementRequest $request) {
		// VERIFICATION DE LA VALIDITE DU S/N
		$user = Auth::user()->username;
		$materiel = Exemplaire::select()->where('serial_number',$request->input('serial_number'))
										->where('vendeurs',$user)
										->where('status','inactif')->first();
		// dd($request);
		if($this->isAvailableSolde($request->input('formule'))) {
			// SOLDE CGA SUFFISANT
			if($materiel) {
			// LE S/N EST VALIDE
			$debut = new Carbon($request->input('debut'));
			$fin = new Carbon($request->input('fin'));	
			$diff = $debut->diffInDaysFiltered(function (Carbon $date) {
				return $date;
			},$fin);
			// dd($diff);
			if($diff >= 28) {
				// LA DUREE EST VALIDE
				// VERIFIER L'EXISTENCE DU CLIENT
				$client =  Lesclients::select()->where('email',$request->input('email'))
												->orWhere('phone',$request->input('phone'))->first();
				$recrutement = new Recrutements;												
				$recrutement->vendeurs = Auth::user()->username;
				$recrutement->serial_number = $request->input('serial_number');

				if($client) {
					// LE CLIENT EXISTE
					// ON RECUPERE LE CLIENT EXISTANT
					// ON FAIT JUSTE LE RECRUTEMENT ET L'ABONNEMENT
					$recrutement->clients = $client->num;
					
					// CHANGEMENT DU STATUS EN ACTIF
					Exemplaire::select()->where('vendeurs',$recrutement->vendeurs)
											->where('serial_number',$recrutement->serial_number)
												->update([
													'status' => 'actif'
												]);
					// DEBIT DE LA QUANTITE DANS LE STOCK VENDEUR 
					$quantite = StockVendeur::select()->where('produit',$materiel->produit)
											->where('vendeurs',$recrutement->vendeurs)->first()->quantite;


					if($quantite > 0) {
						$qte = $quantite-1;
						StockVendeur::select()->where('produit',$materiel->produit)
											->where('vendeurs',$recrutement->vendeurs)
											->update([
												'quantite' => $qte
											]);
					$recrutement->save();
					$this->makeAbonnement($request,$client->num);
					// AJOUT DE L'ABONNEMENT
					return redirect('/user/recrutement')->with('success',"Recrutement effectué !");
					} else {
						// ERREUR
						return redirect('/user/recrutement')->with('_errors',"Quantite indisponible!");
					}

				} else {
					// LE CLIENT N'EXITE PAS
					// ON AJOUTE LE NOUVEAU CLIENT
					$newClient = new Lesclients;
					$newClient->email = $request->input('email');
					$newClient->phone = $request->input('phone');
					$newClient->nom = $request->input('nom');
					$newClient->adresse = $request->input('adresse');
					$newClient->prenom = $request->input('prenom');
					do {
						$newClient->num = random_int(1000,9999);

					} while($this->isExisteNumClient($newClient->num));
					
					$recrutement->clients = $newClient->num;
					// CHANGEMENT DU STATUS EN ACTIF
					Exemplaire::select()->where('vendeurs',$recrutement->vendeurs)
											->where('serial_number',$recrutement->serial_number)
												->update([
													'status' => 'actif'
												]);
					// DEBIT DE LA QUANTITE DANS LE STOCK VENDEUR 
					$quantite = StockVendeur::select()->where('produit',$materiel->produit)
											->where('vendeurs',$recrutement->vendeurs)->first()->quantite;

					if($quantite > 0) {
						$qte = $quantite-1;
						StockVendeur::select()->where('produit',$materiel->produit)
											->where('vendeurs',$recrutement->vendeurs)
											->update([
												'quantite' => $qte
											]);
					$newClient->save();											
					$recrutement->save();
					// AJOUT DE L'ABONNEMENT
					$this->makeAbonnement($request,$newClient->num);
					return redirect('/user/recrutement')->with('success',"Recrutement effectué !");
					} else {
						// ERREUR
						return redirect('/user/recrutement')->with('_errors',"Quantite indisponible!");
					}
				}

			} else {
				// LA DUREE N'EST PAS VALIDE
				return redirect('/user/recrutement')->with('_errors',"La duree d'abonnement minimum est de 28 jours");
			}
			// dd($fin);
		} else {
			// LE S/N INVALIDE
			return redirect('/user/recrutement')->with('_errors',"Le numero du materiel est invalide");
		}

		} else {
			// SOLDE CGA INSUFFISANT
			return redirect('/user/recrutement')->with('_errors',"Solde CGA insuffisant");
		}
		
	}

	// HISTORIQUE DES RECRUTEMENTS
	public function allRecrutement(Request $request) {
		$recrutements = Recrutements::select()->where('vendeurs',Auth::user()->username)->orderBy('created_at','desc')->get();
		if($recrutements) {
			$temp = $this->organizeRecrutmentsEntries($recrutements);
			return response()->json($temp);
		} else {
			return response()->json('fail');
		}

	}

	public function organizeRecrutmentsEntries($tab) {
		$temp = [];
		foreach($tab as $key => $values) {
			$abonn = $this->getRecrutementAbonnementByMaterial($values->serial_number,$values->created_at);

			$client = $this->getClientByRecrutement($values->clients);

			$temp[$key] = [
				'material' => $values->serial_number,
				'clients' => $values->clients,
				'formule' => $abonn->formules,
				'status' => $abonn->status,
				'nom' => $client->nom,
				'prenom' => $client->prenom,
				'expiration' => $abonn->fin,
				'duree'	=>	$abonn->duree_abonnement
			] ;
		}
		return $temp;
	}

	public function getRecrutementAbonnementByMaterial($material,$created_at) {
		$temp	=	Abonnement::select()->where('materiel',$material)->get()[0];
		return $temp;
	}

	public function getClientByRecrutement($numClient) {
		$temp	=	Lesclients::select()->where('num',$numClient)->first();
		return $temp;
	}

	// CALCUL DU MONTANT NET A PAYER
	public function getNetValue(Request $request) {
		$exemp = Exemplaire::all()[0];
		$produit = Produits::select()->where('reference',$exemp->produit)->first();
		$formule = Formule::select()->where('nom',$request->input('formule'))->first();
		$duree = explode('-',$request->input('duree'))[0];
		$montant = $produit->prix_vente + ($duree * $formule->prix);

		return response()->json($montant);
	}

}