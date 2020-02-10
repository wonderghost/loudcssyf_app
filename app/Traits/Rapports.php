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

Trait Rapports {
	public function isExistRapportOnThisDate(Carbon $date,$vendeurs,$type = 'recrutement') {
	  $temp = RapportVente::where([
	    'date_rapport'  =>  $date->toDateTimeString(),
	    'vendeurs'  =>  $vendeurs,
			'type'	=>	$type
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
	public function sendRapport(Request $request,$slug) {
		try {
		if($slug) {

				switch ($slug) {
					case 'recrutement':
					$validation = $request->validate([
						'quantite_materiel'  =>  'required|min:1',
						'montant_ttc' =>  'required|numeric',
						'vendeurs'   =>  'required|exists:users,username',
						'date'  =>  'required|before_or_equal:'.(date("Y/m/d",strtotime("now")))
					],[
						'required'  =>  'Veuillez remplir le champ `:attribute`',
						'numeric'  =>  '`:attribute` doit etre une valeur numeric',
						'before_or_equal' =>  'Vous ne pouvez ajouter de rapport a cette date'
					]);

					if(!$this->isExistRapportOnThisDate(new Carbon($request->input('date')),$request->input('vendeurs'))) {
						// verifier si le solde cga existe pour le vendeur
						if($this->isCgaDisponible($request->input("vendeurs"),$request->input('montant'))) {
							// verification de l'existence des numeros de serie et de leur inactivite
							$rapport = new RapportVente;
							do {
								$rapport->id_rapport =  Str::random(10).'_'.time();
							} while ($rapport->isExistRapportById());

							// dump($rapport->id_rapport);
							// die();

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
							// redirection
							if(Auth::user()->type == 'admin') {
									return redirect('/admin/add-rapport')->withSuccess("Success!");
							} else {
									return redirect('/user/add-rapport')->withSuccess("Success!");
							}
						} else {
							throw new AppException("Solde Cga Indisponible!");
						}
					} else {
						throw new AppException("Un rapport existe deja a cette date pour ce vendeur!");
					}
					break;
					case 'reabonnement':
						$validation = $request->validate([
							'montant_ttc' =>  'required|numeric',
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
								if(Auth::user()->type == 'admin') {
									return redirect('/admin/add-rapport')->withSuccess("Success!");
							} else {
									return redirect('/user/add-rapport')->withSuccess("Success!");
							}
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
						if(Auth::user()->type == 'admin') {
							return redirect('/admin/add-rapport')->withSuccess("Success!");
						} else {
							return redirect('/user/add-rapport')->withSuccess("Success!");
						}
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
				return back()->with('_errors',$e->getMessage());
			}

	}

	// VERIFICATION DU NUMERO DE SERIE
	public function checkSerial(Request $request) {
		try {
			$serial = Exemplaire::where([
				'serial_number' =>  $request->input('ref-0'),
				'vendeurs'  =>  $request->input('ref-1'),
				'status'  =>  'inactif'
				])->first();

			if($serial) {

				return response()->json('success');

			} else {
				throw new AppException("Numero invalide!");
			}
		} catch (AppException $e) {
			return response()->json($e->getMessage());
		}

	}
	// LIST DES RAPPORTS
	public function listRapport() {
		$users = User::whereIn('type',['v_standart','v_da'])->get();
			return view('admin.list-rapport-vente')->withUsers($users);
	}

// HISTORIQUE DE REABONNE POUR L'ADMINISTRATEUR
		public function getAllRapport(RapportVente $r) {
			try {
				$all = $r->select()->orderBy('date_rapport','desc')->get();
				return response()
					->json($this->organizeRapport($all));
			} catch (AppException $e) {
				header("Erreur!",true,$e);
				die(json_encode($e->getMessage()));
			}
		}
			public function reabonnementRapport() {
				try {
					$reabonnement = RapportVente::where('type','reabonnement')->orderBy('date_rapport','desc')->paginate(10);
					$all=$this->organizeRapport($reabonnement);
					return response()->json($all);
				} catch (AppException $e) {
					header("Erreur!",true,422);
					die(json_encode($e->getMessage()));
				}
		}

// HISTORIQUE DE RECRUTEMENT POUR L'ADMINISTRATEUR

	public function recrutementRapport() {
		try {
			$recrutement = RapportVente::where('type','recrutement')->orderBy('date_rapport','desc')->paginate(10);
			$all = $this->organizeRapport($recrutement);
			return response()->json($all);
		} catch (AppException $e) {
			header("Erreur!",true,422);
			die(json_encode($e->getMessage()));
		}

	}
	// HISTORIQUE DE MIGRATION POUR L'ADMINISTRATEUR
	public function migrationRapport() {
		try {
			$migration = RapportVente::where('type','migration')->orderBy('date_rapport','desc')->paginate(10);
			$all = $this->organizeRapport($migration);
			return response()->json($all);
		} catch (AppException $e) {
			header("Erreur!",true,422);
			die(json_encode($e->getMessage()));
		}
	}


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
				'paiement_commission' =>  $value->statut_paiement_commission
			];
		}
		return $all;
	}
// cumulee total des comissions de tous les rapports
public function totalCommission(RapportVente $r) {
	try {
		$commission = $r->whereIn('type',['recrutement','reabonnement'])
			->whereNull('pay_comission_id')
			->sum('commission');
		return response()->json($commission);
	} catch (AppException $e) {
		header("Erreur!",true,422);
		die(json_encode($e->getMessage()));
	}

}

}
