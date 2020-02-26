<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\AppException;
use Carbon\Carbon;
use App\Http\Requests\RapportRequest;
use App\RapportVente;
use App\Exemplaire;
use Illuminate\Support\Str;

use App\RexAccount;
use App\Afrocash;
use App\Formule;
use App\CommandCredit;

use App\User;
use App\Produits;
use App\Agence;
use App\Depots;

use App\RavitaillementDepot;
use App\CgaAccount;
use App\Option;
use App\StockVendeur;
use App\StockPrime;
use App\Credit;
use App\Promo;
use App\TransactionCreditCentral;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

Trait Rapports {

	public function isExistRapportOnThisDate(Carbon $date,$vendeurs,$type = 'recrutement') {
	  $temp = RapportVente::where([
	    'date_rapport'  =>  $date->toDateTimeString(),
	    'vendeurs'  =>  $vendeurs,
			'type'	=>	$type,
			'state'	=>	'unaborted'
	    ])->first();
	  if($temp) {
	    return $temp;
	  }
	  return false;
	}

	// AJOUTER UN RAPPORT

	public function addRapport() {
		$vendeurs = User::whereIn('type',['v_standart','v_da'])->get();
		return view('admin.add-rapport-vente')->withVendeurs($vendeurs);
	}

	// ENREGISTREMENT D'UN RAPPORT
	public function sendRapport(Request $request,$slug , Exemplaire $e) {
		try {
		if($slug) {

				switch ($slug) {
					case 'recrutement':

					$validation = $request->validate([
						'quantite_materiel'  =>  'required|min:1',
						'montant_ttc' =>  'required|numeric|min:10000',
						'vendeurs'   =>  'required|exists:users,username',
						'date'  =>  'required|before_or_equal:'.(date("Y/m/d",strtotime("now"))),
						'serial_number.*'	=>	'required|distinct|exists:exemplaire,serial_number'
					],[
						'required'  =>  'Veuillez remplir le champ `:attribute`',
						'numeric'  =>  '`:attribute` doit etre une valeur numeric',
						'before_or_equal' =>  'Vous ne pouvez ajouter de rapport a cette date',
						'distinct'	=>	"Doublons repere!",
						'exists'	=>	'Numero inexistant! : `:attribute`'
					]);

					if(!$this->isExistRapportOnThisDate(new Carbon($request->input('date')),$request->input('vendeurs'))) {
						// verifier si le solde cga existe pour le vendeur
						if($this->isCgaDisponible($request->input("vendeurs"),$request->input('montant'))) {
							// verification de l'existence des numeros de serie et de leur inactivite

							foreach($request->input('serial_number') as $value) {
								if(!$this->checkSerial($value,$request->input('vendeurs'),$e)) {
									throw new AppException("Numero de Serie Invalide  : ". $value);
								}
							}

							$rapport = new RapportVente;
							do {
								$rapport->id_rapport =  Str::random(10).'_'.time();
							} while ($rapport->isExistRapportById());

							$temp_date = new Carbon($request->input('date'));
							$rapport->date_rapport = $temp_date->toDateTimeString();
							$rapport->vendeurs  = $request->input('vendeurs');
							$rapport->montant_ttc = $request->input('montant_ttc');
							$rapport->quantite =  $request->input('quantite_materiel');
							$rapport->credit_utilise  = 'cga';
							$rapport->type = 'recrutement';
							$rapport->calculCommission('recrutement');

							// DEBIT DU CREDIT CGA
							$new_solde_cga = CgaAccount::where('vendeur',$request->input('vendeurs'))->first()->solde - $request->input('montant_ttc');
							CgaAccount::where('vendeur',$request->input('vendeurs'))->update([
								'solde' =>  $new_solde_cga
							]);

							// DEBIT DE LA QUANTITE DANS LE STOCK DU VENDEURS
							$new_quantite = StockVendeur::where([
								'vendeurs'  =>  $request->input('vendeurs'),
								'produit' =>  Produits::where('with_serial',1)->first()->reference
							])->first()->quantite - $request->input('quantite_materiel');


							StockVendeur::where('vendeurs',$request->input('vendeurs'))->update([
								'quantite'  =>  $new_quantite
							]);

							$id_rapport = $rapport->id_rapport;
							$rapport->save();

							// CHANGEMENT DE STATUS DES MATERIELS
							foreach($request->input('serial_number') as $value) {
								Exemplaire::where([
									'vendeurs'  =>  $request->input('vendeurs'),
									'serial_number' =>  $value,
									'status'  =>  'inactif'
									])->update([
										'status'  =>  'actif',
										'rapports'  =>  $id_rapport
									]);
							}

							// redirection
							return response()
								->json('done');
						} else {
							throw new AppException("Solde Cga Indisponible!");
						}
					} else {
						throw new AppException("Un rapport existe deja a cette date pour ce vendeur!");
					}
					break;
					case 'reabonnement':

						$validation = $request->validate([
							'montant_ttc' =>  'required|numeric|min : 10000',
							'vendeurs'   =>  'required|exists:users,username',
							'date'  =>  'required|before_or_equal:'.(date("Y/m/d",strtotime("now")))
						],[
							'required'  =>  'Veuillez remplir le champ `:attribute`',
							'numeric'  =>  '`:attribute` doit etre une valeur numeric',
							'before_or_equal' =>  'Vous ne pouvez ajouter de rapport a cette date'
						]);

						if(!$this->isExistRapportOnThisDate(new Carbon($request->input('date')),$request->input('vendeurs'),'reabonnement')) {
							if(($request->input('type_credit') == "cga") && $this->isCgaDisponible($request->input("vendeurs"),$request->input('montant'))) {

								$rapport = new RapportVente;
								$rapport->makeRapportId();
								$rapport->vendeurs = $request->input('vendeurs');
								$rapport->montant_ttc = $request->input('montant_ttc');
								$rapport->type  = 'reabonnement';
								$rapport->credit_utilise  = $request->input('type_credit');
								$rapport->date_rapport  = $request->input('date');
								$rapport->calculCommission('reabonnement');

								// DEBIT DU SOLDE INDIQUE
								$new_solde_cga = CgaAccount::where('vendeur',$request->input('vendeurs'))->first()->solde - $request->input('montant_ttc');

								CgaAccount::where('vendeur',$request->input('vendeurs'))->update([
									'solde' =>  $new_solde_cga
								]);

								$rapport->save();
								return response()
									->json('done');
							} else if(($request->input('type_credit') == "rex") && $this->isRexDisponible($request->input('vendeurs'),$request->input('montant')) ) {
								dd($request);
							} else {
								throw new AppException("Solde Indisponible!");
							}
						} else {
							throw new AppException("Un rapport existe deja a cette date");
						}
					break;
					case 'migration':
					$validation = $request->validate([
						'date'  =>  'required|date|before_or_equal :'.(date("Y/m/d",strtotime("now"))),
						'vendeurs'  =>  'required|exists:users,username',
						'quantite_materiel' =>  'required|min:1'
					]);

					if(!$this->isExistRapportOnThisDate(new Carbon($request->input('date')),$request->input('vendeurs'),'migration')) {
						$rapport = new RapportVente;
						$rapport->makeRapportId();
						$rapport->date_rapport  = $request->input('date');
						$rapport->vendeurs  = $request->input('vendeurs');
						$rapport->quantite = $request->input('quantite_materiel');
						$rapport->type = 'migration';

						$id_rapport = $rapport->id_rapport;
						$rapport->save();
						// CHANGEMENT DE STATUS DES MATERIELS
						for($i = 1 ; $i <= $request->input('quantite_materiel') ; $i++) {
							Exemplaire::where([
								'vendeurs'  =>  $request->input('vendeurs'),
								'serial_number' =>  $request->input('serial-number-'.$i),
								'status'  =>  'inactif'
							])->update([
								'status'  =>  'actif',
								'rapports'  =>  $id_rapport
							]);
						}
						// DEBIT DE LA QUANTITE DANS LE STOCK DU VENDEURS
						$new_quantite = StockVendeur::where([
							'vendeurs'  =>  $request->input('vendeurs'),
							'produit' =>  Produits::where('with_serial',1)->first()->reference
						])->first()->quantite - $request->input('quantite_materiel');
						StockVendeur::where('vendeurs',$request->input('vendeurs'))->update([
							'quantite'  =>  $new_quantite
						]);

						return response()
							->json('done');

					} else {
						throw new AppException("Un rapport existe deja a cette date!");
					}
					break;
					default:
					die();
					break;
				}
			} else {
				throw new AppException("Error!");
			}
		} catch (AppException $e) {
				header("Erreur",true,422);
				die(json_encode($e->getMessage()));
			}
	}

	// VERIFICATION DU NUMERO DE SERIE
	public function checkSerial($serial,$vendeurs, Exemplaire $e) {
		return $e->where([
			'serial_number'	=>	$serial,
			'vendeurs'	=>	$vendeurs,
			'status'	=>	'inactif'
		])
		->first();
	}
	// LIST DES RAPPORTS
	public function listRapport() {
		return view('admin.list-rapport-vente');
	}

// HISTORIQUE DE RAPPORT POUR L'ADMINISTRATEUR
		public function getAllRapport(RapportVente $r) {
			try {
				$all = $r->select()
					->orderBy('date_rapport','desc')
					->limit(500)->get();
				return response()
					->json($this->organizeRapport($all));
			} catch (AppException $e) {
				header("Erreur!",true,$e);
				die(json_encode($e->getMessage()));
			}
		}

// HISTORIQUE DE RECRUTEMENT POUR L'ADMINISTRATEUR


	public function organizeRapport($data) {
		$all = [];
		foreach ($data as $key => $value) {
			$all[$key] = [
				'id'	=>	$value->id_rapport,
				'date'  =>  $value->date_rapport,
				'vendeurs'  =>  $value->vendeurs()->agence()->societe." ( ".$value->vendeurs()->localisation." )",
				'type'  =>  $value->type,
				'credit'  =>  $value->credit_utilise,
				'quantite'  =>  $value->quantite,
				'montant_ttc' =>  number_format($value->montant_ttc),
				'commission'  =>  number_format($value->commission),
				'promo'	=>	$value->promo > 0 ? '' : 'hors promo',
				'paiement_commission' =>  $value->statut_paiement_commission,
				'state'	=>	$value->state
			];
		}
		return $all;
	}
// cumulee total des comissions de tous les rapports
public function totalCommission(RapportVente $r) {
	try {
		$commission = $r->whereIn('type',['recrutement','reabonnement'])
			->where('state','unaborted')
			->whereNull('pay_comission_id')
			->sum('commission');
		return response()->json($commission);
	} catch (AppException $e) {
		header("Erreur!",true,422);
		die(json_encode($e->getMessage()));
	}
}
#@@@@@@@@@@@@@ ANNULATION DE RAPPORT DE VENTE @@@@@@@@@@@@@@@@@@@
// SUPPRIMER UN RAPPORT
public function abortRapport(Request $request , RapportVente $r) {
	$validation = $request->validate([
		'id_rapport' => 'required|exists:rapport_vente,id_rapport'
	]);
	try {
		// verification du mot de passe
		if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
			throw new AppException("Mot de passe Invalide!");
		}

		$rapport = RapportVente::find($request->input('id_rapport'));
		// verifier si le rapport est deja annuler
		if($rapport->state == 'aborted') {
			throw new AppException("Ce rapport n'est plus valide!");
		}
		// recuperation du compte cga
		$vendeurs = $rapport->vendeurs();
		if($rapport->credit_utilise == 'cga') {
			// REX
			$cga = $vendeurs->cgaAccount();
			$cga->solde += $rapport->montant_ttc;
			$rapport->state = 'aborted';

			// enregistrement de la notification
			$this->sendNotification("Annulation de Rapport","Le rapport du ".$rapport->date_rapport." a ete annule",$vendeurs->username);
			$this->sendNotification("Annulation de Rapport","Vous avez annule le rapport du ".$rapport->date_rapport." pour : ".$vendeurs->localisation,Auth::user()->username);
			$cga->save();
			$rapport->save();
		} else {
			// REX
		}
		return response()->json('done');
	} catch (AppException $e) {
		header("Unprocessable entity",true , 422);
		die(json_encode($e->getMessage()));
	}

}

}
