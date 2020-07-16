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
use App\RapportPromo;
use App\CommandMaterial;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Abonnement;
use App\AbonneOption;
use App\Upgrade;


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
		return view('admin.add-rapport-vente');
	}

	// 
	// Trouver l'abonnement actif
	public function checkAbonnementActif($abonnements) {
		try {
			$valide_abonnement = null;
			$valides_abonnements = [];

			foreach($abonnements as $key => $value) {
				$debut = new Carbon($value->debut);
	
				$fin = new Carbon($value->debut);
				$fin->addMonths($value->duree)->subDay();

				$now = Carbon::now();
				
				if($debut <= $now && $now <= $fin) {
					// $valide_abonnement = $value;
					array_push($valides_abonnements,$value);
					// break;
				} else {
					$valide_abonnement = null;
				}
			}

			return end($valides_abonnements);

		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}
	#
	public function checkSerialDebutDate($serialNumber) {

		$serial = Exemplaire::find($serialNumber);
		$abonnements = $serial ? $serial->abonnements() : null;
		$valide_abonnement = null;
		$future_abonnement = [];
		$debut_prochain_abonnement = null;

		if(!is_null($abonnements)) {
			// trouver l'abonnement valide
			foreach($abonnements as $key => $value) {
				$debut = new Carbon($value->debut);
	
				$fin = new Carbon($value->debut);
				$fin->addMonths($value->duree)->subDay();
				$now = Carbon::now();
				
				if($debut <= $now && $now <= $fin) {
					$valide_abonnement = $value;
				} else {
					$valide_abonnement = 'nothing';
				}
	
				if($now < $debut) {
					// abonement a venir
					$future_abonnement[$key] = $value;
				}
			}
			
			// existence d'une abonnement valide
			if(!is_null($valide_abonnement) && $valide_abonnement != 'nothing') {
				if(!empty($future_abonnement)) {
					
				} else {
					// aucun future abonnement
					#calcul de la date de debut prevue
					$debut_prochain_abonnement = new Carbon($valide_abonnement->debut);
					$debut_prochain_abonnement->addMonths($valide_abonnement->duree);
				}
			} else {
				// aucun abonnement valide n'existe
				return false;
			}
	
			return $debut_prochain_abonnement;
		}
		return false;
	}
	// VERIFIER LA VALIDITE D'UN ABONNEMENT POUR UN SERIAL NUMBER POUR DETERMINER LE DEBUT DE L'ABONNEMENT
	public function checkSerialForGetDebutDate(Request $request) {
		try {
			if($this->checkSerialDebutDate($request->input('serial_materiel'))) {
				return response()
					->json($this->checkSerialDebutDate($request->input('serial_materiel'))->toDateString());
			}
				
		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}
	// VERIFIER L'EXISTENCE D'UN SERIAL NUMBER EN CAS DE REABONEMENT UPGRADE
public function checkSerialOnUpgradeState(Request $request , Exemplaire $e) {
		try {
			$validation = $request->validate([
				'serial_number'	=>	'required|string'
			],[
				'required'	=>	'`:attribute` est obligatoire !'
			]);

			$serialNumber = $e->find($request->input('serial_number'));
			$abonnements = null;
			$valide_abonnement = null;

			if($serialNumber) {
				// le serial number existe
				if(!$serialNumber->origine) {
					// il n'appartien pas au reseau
					$abonnements = $serialNumber->abonnements();
					if($abonnements->count() > 0) {

						$valide_abonnement = $this->checkAbonnementActif($abonnements);

						if(!is_null($valide_abonnement) && $valide_abonnement ) {
							$valide_abonnement['formule_prix'] = $valide_abonnement->formule()->prix;

							// calcul de la duree restante en (mois) 
							$fin_abonnement = new Carbon($valide_abonnement->debut);
							$fin_abonnement->addMonths($valide_abonnement->duree)
								->subDay()
								->addHours(23)
								->addMinutes(59)
								->addSeconds(59);
							$today = Carbon::now();
							
							$valide_abonnement['fin_abonnement'] = $fin_abonnement->toDateTimeString();
							$valide_abonnement['jour_restant'] = $fin_abonnement->diffInDays($today);
							if($valide_abonnement['jour_restant'] < 15 && $valide_abonnement['jour_restant'] > 0) {
								$valide_abonnement['mois_restant'] = 1;
							}
							else {
								$valide_abonnement['mois_restant'] = round($fin_abonnement->diffInDays($today) / 28);
							}

							return response()
								->json($valide_abonnement);
						}
						return response()
							->json('fail');

					} else {
						// aucun abonnement enregistre
						return response()
							->json('fail');
					}
				} else {
					// s/n appartien au reseau
					if($serialNumber->status == 'inactif') {
						throw new AppException("Materiel vierge!");
					}
					$abonnements = $serialNumber->abonnements();
					if($abonnements->count() > 0) {
						// trouver l'abonnement actif
						$valide_abonnement = $this->checkAbonnementActif($abonnements);
						// renvoi de l'abonnement actif
						if(!is_null($valide_abonnement)) {
							$valide_abonnement['formule_prix'] = $valide_abonnement->formule()->prix;
							// calcul de la duree restante en (mois) 
							$fin_abonnement = new Carbon($valide_abonnement->debut);
							$fin_abonnement->addMonths($valide_abonnement->duree)
								->subDay()
								->addHours(23)
								->addMinutes(59)
								->addSeconds(59);
							$today = Carbon::now();
							
							$valide_abonnement['fin_abonnement'] = $fin_abonnement->toDateTimeString();
							$valide_abonnement['jour_restant'] = $fin_abonnement->diffInDays($today);
							if($valide_abonnement['jour_restant'] < 15 && $valide_abonnement['jour_restant'] > 0) {
								$valide_abonnement['mois_restant'] = 1;
							}
							else {
								$valide_abonnement['mois_restant'] = round($fin_abonnement->diffInDays($today) / 28);
							}

							return response()
								->json($valide_abonnement);
						}
						return response()
							->json('fail');
					} else {
						// aucun abonnement enregistre
						return response()
							->json('fail');
					}	
				}
			} else {
				// le numero n'existe pas dans le systeme
				return response()
					->json('fail');
			}
		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}

	// ENREGISTREMENT D'UN RAPPORT
	public function sendRapport(Request $request,$slug , Exemplaire $e ,RapportPromo $rp , Promo $p , StockVendeur $sv,Produits $produit , \App\ComissionSetting $cs) {
		try {

			// return response()
			// 	->json($request);

			// die();

		if($slug) {

				switch ($slug) {
					case 'recrutement':

					$validation = $request->validate([
						'quantite_materiel'  =>  'required|min:1',
						'montant_ttc' =>  'required|numeric',
						'vendeurs'   =>  'required|exists:users,username',
						'date'  =>  'required|before_or_equal:'.(date("Y/m/d",strtotime("now"))),
						'serial_number.*'	=>	'required|distinct|exists:exemplaire,serial_number',
						'debut.*'	=>	'required|date|after_or_equal:date',
						'formule.*'	=>	'required|string|exists:formule,nom',
						'duree.*'	=>	'required|numeric'
					],[
						'required'  =>  'Veuillez remplir le champ `:attribute`',
						'numeric'  =>  '`:attribute` doit etre une valeur numeric',
						'before_or_equal' =>  'Vous ne pouvez ajouter de rapport a cette date',
						'after_or_equal'	=>	'Le debut doit etre egal ou superieur a la date d\'activation',
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
							$rapport->calculCommission('recrutement',$cs);

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
							
							// LA PROMO EXISTE
							$tmp_promo = $this->isExistPromo();
							
							if($tmp_promo) {
								// la promo est active
								$promo_fin_to_carbon_date = new Carbon($tmp_promo->fin);
								$promo_debut_to_carbon_date = new Carbon($tmp_promo->debut);
								$rapport_date_to_carbon_date = new Carbon($request->input('date'));
								if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
									// le rapport est en mode promo
									// AJOUT DU RAPPORT PROMO
									do {
										$rp->id =  Str::random(10).'_'.time();
									} while ($rp->isExistId());

									$rp->quantite_a_compenser = $request->input('quantite_materiel');
									$rp->compense_espece = $request->input('quantite_materiel') * $tmp_promo->subvention;
									$rp->promo = $tmp_promo->id;
									$rapport->id_rapport_promo = $rp->id;
									$rapport->promo = $tmp_promo->id;
									$rp->save();
								}
							} else {
								// la promo n'est pas active
								if($request->input('promo_id')) {
									// le rapport appartien a une promo
									$thePromo = $p->find($request->input('promo_id'));

									$promo_fin_to_carbon_date = new Carbon($thePromo->fin);

									$promo_debut_to_carbon_date = new Carbon($thePromo->debut);

									$rapport_date_to_carbon_date = new Carbon($request->input('date'));

									if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
									// 	// le rapport est en mode promo
									// 	// AJOUT DU RAPPORT PROMO
										do {
											$rp->id =  Str::random(10).'_'.time();
										} while ($rp->isExistId());
	
										$rp->quantite_a_compenser = $request->input('quantite_materiel');
										$rp->compense_espece = $request->input('quantite_materiel') * $thePromo->subvention;
										$rp->promo = $thePromo->id;
										$rapport->id_rapport_promo = $rp->id;

										$rapport->promo = $thePromo->id;

										$rp->save();
									} else {
										throw new AppException("La date choisi n'est pas inclut dans la periode de promo !");
									}

								}
							}

							
							
							// $rp->save();
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

							// ENREGISTREMENT DES ABONNEMENTS
							$abonnement_data = [];
							$allAbonnOption_data = [];
							foreach($request->input('serial_number') as $key => $value) {
								$abonnement_data[$key] = new Abonnement;
								$abonnement_data[$key]->makeAbonnementId();
								$abonnement_data[$key]->rapport_id = $id_rapport;
								$abonnement_data[$key]->serial_number = $value;
								$abonnement_data[$key]->debut = $request->input('debut')[$key];
								$abonnement_data[$key]->duree = $request->input('duree')[$key];
								$abonnement_data[$key]->formule_name = $request->input('formule')[$key];

								// VERIFICATION DE L'EXISTENCE DE L'OPTION
								if(array_key_exists($key,$request->input('options'))) {
									$allAbonnOption_data[$key] = new AbonneOption;
									$allAbonnOption_data[$key]->id_abonnement = $abonnement_data[$key]->id;
									$allAbonnOption_data[$key]->id_option = $request->input('options')[$key];
								}
							}	

							foreach($request->input('serial_number') as $key => $value) {
								$abonnement_data[$key]->save();
								if(array_key_exists($key,$allAbonnOption_data) && !is_null($allAbonnOption_data[$key]->id_option)) {
									$allAbonnOption_data[$key]->save();
								}
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
							'montant_ttc' =>  'required|numeric|min:10000',
							'vendeurs'   =>  'required|exists:users,username',
							'date'  =>  'required|before_or_equal:'.(date("Y/m/d",strtotime("now"))),
							'serial_number.*'	=>	'required|string|min:14|max:14',
							'debut.*'	=>	'required|date',
							'formule.*'	=>	'required|string|exists:formule,nom',
							'duree.*'	=>	'required|numeric'
							
						],[
							'required'  =>  'Veuillez remplir le champ `:attribute`',
							'numeric'  =>  '`:attribute` doit etre une valeur numeric',
							'before_or_equal' =>  'Vous ne pouvez ajouter de rapport a cette date',
							'after_or_equal'	=>	'Le debut doit etre egal ou superieur a la date d\'activation',
							'min'	=>	'14 chiffres requis!',
							'max'	=>	'14 chiffres requis!'
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
								$rapport->calculCommission('reabonnement',$cs);

								// DEBIT DU SOLDE INDIQUE

								$theUser = User::where('username',$request->input('vendeurs'))->first();

								$cgaAccount = $theUser->cgaAccount();
								
								$cgaAccount->solde -= $request->input('montant_ttc');

								// LA PROMO EXISTE
								$tmp_promo = $this->isExistPromo();
								
								if($tmp_promo) {

									$promo_fin_to_carbon_date = new Carbon($tmp_promo->fin);
									$promo_debut_to_carbon_date = new Carbon($tmp_promo->debut);
									$rapport_date_to_carbon_date = new Carbon($request->input('date'));

									if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
										// le rapport est en mode promo
										$rapport->promo = $tmp_promo->id;
									}

								} else {
									// la promo n'est pas active
									if($request->input('promo_id')) {
										// le rapport appartien a une promo
										$thePromo = $p->find($request->input('promo_id'));
	
										$promo_fin_to_carbon_date = new Carbon($thePromo->fin);
	
										$promo_debut_to_carbon_date = new Carbon($thePromo->debut);
	
										$rapport_date_to_carbon_date = new Carbon($request->input('date'));
	
										if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
										// 	// le rapport est en mode promo
											// $rapport->id_rapport_promo = $rp->id;
											$rapport->promo = $thePromo->id;
										} else {
											throw new AppException("La date choisi n'est pas inclut dans la periode de promo !");
										}
	
									}
								}
								$id_rapport = $rapport->id_rapport;
								foreach($request->input('serial_number') as $key => $value) {
									$tmp = $e->find($value);
									if($tmp) {
										// verifier si le numero est actif
										if($tmp->status == 'inactif' && $tmp->origine == 1) {
											throw new AppException("Attention Materiel vierge :`".$tmp->serial_number."` !");
										}
									} 
								}

								// verification de la date de debut en cas d'un abonnement actif
								foreach($request->input('serial_number') as $key => $value) {

									
									if(array_key_exists($key,$request->input('upgrade')) && $request->input('upgrade')[$key]) {
										// ceci est un upgrade
										# pas besoin de ce test
									}
									else {
										// ceci est un abonnement simple

										$dateSuggest = $this->checkSerialDebutDate($value);
										

										$choiceDate = $request->input('debut')[$key] ? new Carbon($request->input('debut')[$key]) : null;

										if(is_null($choiceDate)) {
											throw new AppException("Erreur ! ressayez ...");
										}
	
										// test sur la date de debut qui doit etre superieur ou egal a la date d'activation

										$date_rapport = new Carbon($request->input('date'));

										if($choiceDate < $date_rapport) {
											throw new AppException("Le debut doit etre egal ou superieur a la date d'activation !");
										}

										if($choiceDate < $dateSuggest) {
											throw new AppException("Erreur sur la date de debut pour :".$value.",la date suggeree est : `".$dateSuggest."`");
										}

									}
								}
								

							// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

								$abonnement_data = [];
								$allAbonnOption_data = [];
								$upgrade = [];

								
								foreach($request->input('serial_number') as $key	=>	$value) {
									$tmp = $e->find($value);

									// 

									$abonnement_data[$key] = new Abonnement;
									$abonnement_data[$key]->makeAbonnementId();
									$abonnement_data[$key]->rapport_id = $id_rapport;
									$abonnement_data[$key]->serial_number = $value;
									$abonnement_data[$key]->formule_name = $request->input('formule')[$key];

									
									if(array_key_exists($key,$request->input('upgrade')) && $request->input('upgrade')[$key]){
										// abonnement avec upgrade
										$upgrade[$key] = new Upgrade;

										if(array_key_exists($key,$request->input('upgradeData')) && !is_null($request->input('upgradeData')[$key])){

											$upgrade[$key]->depart = $request->input('upgradeData')[$key]['formule_name'];
											$upgrade[$key]->old_abonnement = $request->input('upgradeData')[$key]['id'];

											// tester la conformite de la date et de la duree

											$debutTest = new Carbon($request->input('debut')[$key]);
											$debutRequest = new Carbon($request->input('upgradeData')[$key]['debut']);

											if($debutTest->ne($debutRequest)) {
												throw new AppException("date de debut non conforme pour : `".$value."` ");
											}

											if($request->input('upgradeData')[$key]['mois_restant'] != $request->input('duree')[$key]) {
												throw new AppException("Duree non conforme pour : `".$value."`");
											}
										}
										else {
											$upgrade[$key]->depart = $request->input('old_formule')[$key];
										}
										
										$upgrade[$key]->finale = $request->input('formule')[$key];
										$upgrade[$key]->id_abonnement = $abonnement_data[$key]->id;
										
										$abonnement_data[$key]->upgrade = true;

									}
									else {

										// verifier si un abonnement existe a la meme date de debut  pour le meme numero de materiel
	
										if($abonnement_data[$key]->isExistAbonnementForDebutDate()) {
											throw new AppException("Un abonnement existe deja a cette date de debut pour :`".$value."`!");
										}
									}

									$abonnement_data[$key]->debut = $request->input('debut')[$key];
									$abonnement_data[$key]->duree = $request->input('duree')[$key];
									
									// VERIFICATION DE L'EXISTENCE DE L'OPTION
									if(array_key_exists($key,$request->input('options'))) {	
										$allAbonnOption_data[$key] = new AbonneOption;
										$allAbonnOption_data[$key]->id_abonnement = $abonnement_data[$key]->id;
										$allAbonnOption_data[$key]->id_option = $request->input('options')[$key];
									}

									if($tmp) {
										// get serial info from database
										
									} else {
										// insert into databse
										$exem = new Exemplaire;
										$exem->status = 'actif';
										$exem->serial_number = $value;
										$exem->origine = false;
										$exem->produit = $produit->where('with_serial',1)->first()->reference;
										$exem->save();
									}
								}

								$cgaAccount->save();

								$rapport->save();

								foreach($request->input('serial_number') as $key => $value) {

									$abonnement_data[$key]->save();

									if(array_key_exists($key,$allAbonnOption_data) && !is_null($allAbonnOption_data[$key]->id_option)) {
										$allAbonnOption_data[$key]->save();
									}

									if(array_key_exists($key,$upgrade)) {
										$upgrade[$key]->save();
									}
								}
								
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
						'quantite_materiel' =>  'required|min:1',
						'serial_number.*'	=>	'required|distinct|exists:exemplaire,serial_number'
					],[
						'required'	=>	'Champ(s) :attribute est obligatoire!',
						'exists'	=>	':attribute n\'existe dans la base de donnees',
						'distinct'	=>	':attribute est duplique'
					]);

					// verification de l'existence des numeros de serie et de leur inactivite

					foreach($request->input('serial_number') as $value) {
						if(!$this->checkSerial($value,$request->input('vendeurs'),$e)) {
							throw new AppException("Numero de Serie Invalide  : ". $value);
						}
					}

					if(!$this->isExistRapportOnThisDate(new Carbon($request->input('date')),$request->input('vendeurs'),'migration')) {
						$rapport = new RapportVente;
						$rapport->makeRapportId();
						$rapport->date_rapport  = $request->input('date');
						$rapport->vendeurs  = $request->input('vendeurs');
						$rapport->quantite = $request->input('quantite_materiel');
						$rapport->type = 'migration';

						$id_rapport = $rapport->id_rapport;
						// LA PROMO EXISTE
						$tmp_promo = $this->isExistPromo();
								
						if($tmp_promo) {
							$promo_fin_to_carbon_date = new Carbon($tmp_promo->fin);
							$promo_debut_to_carbon_date = new Carbon($tmp_promo->debut);
							$rapport_date_to_carbon_date = new Carbon($request->input('date'));
							if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// le rapport est en mode promo
								$rapport->promo = $tmp_promo->id;
							}
						} else {
							// la promo n'est pas active
							if($request->input('promo_id')) {
								// le rapport appartien a une promo
								$thePromo = $p->find($request->input('promo_id'));

								$promo_fin_to_carbon_date = new Carbon($thePromo->fin);

								$promo_debut_to_carbon_date = new Carbon($thePromo->debut);

								$rapport_date_to_carbon_date = new Carbon($request->input('date'));

								if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// 	// le rapport est en mode promo
								// 	// AJOUT DU RAPPORT PROMO
									// do {
									// 	$rp->id =  Str::random(10).'_'.time();
									// } while ($rp->isExistId());

									// $rp->quantite_a_compenser = $request->input('quantite_materiel');
									// $rp->compense_espece = $request->input('quantite_materiel') * $thePromo->subvention;
									// $rp->promo = $thePromo->id;
									// $rapport->id_rapport_promo = $rp->id;

									$rapport->promo = $thePromo->id;

									// $rp->save();
								} else {
									throw new AppException("La date choisi n'est pas inclut dans la periode de promo !");
								}

							}
						}
						
						$rapport->save();

						// CHANGEMENT DE STATUS DES MATERIELS
						for($i = 0 ; $i < $request->input('quantite_materiel') ; $i++) {
							Exemplaire::where([
								'vendeurs'  =>  $request->input('vendeurs'),
								'serial_number' =>  $request->input('serial_number')[$i],
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

						StockVendeur::where('vendeurs',$request->input('vendeurs'))
						->where('produit',Produits::where('with_serial',1)->first()->reference)
						->update([
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
					->orderBy('date_rapport','desc')->get();
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
			$created_at = new Carbon($value->created_at);
			$all[$key] = [
				'id'	=>	$value->id_rapport,
				'date'  =>  $value->date_rapport,
				'vendeurs'  =>  $value->vendeurs()->agence()->societe." ( ".$value->vendeurs()->localisation." )",
				'type'  =>  $value->type,
				'credit'  =>  $value->credit_utilise,
				'quantite'  =>  $value->quantite,
				'montant_ttc' =>  $value->montant_ttc,
				'commission'  =>  $value->commission,
				'promo'	=>	$value->promo != 0 ? 'en promo' : 'hors promo',
				'paiement_commission' =>  $value->statut_paiement_commission,
				'state'	=>	$value->state,
				'created_at'	=>	$created_at->toDateTimeString()
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
public function abortRapport(Request $request , RapportVente $r , StockVendeur $sv ,Produits $p) {
	$validation = $request->validate([
		'id_rapport' => 'required|exists:rapport_vente,id_rapport'
	]);

	
	try {
		// verification du mot de passe
		if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
			throw new AppException("Mot de passe Invalide!");
		}
		$rapport = RapportVente::find($request->input('id_rapport'));
		$vendeurs = $rapport->vendeurs();

		// CATEGORISER PAR TYPE DE RAPPORT 
		// verifier si le rapport est deja annuler
		if($rapport->state == 'aborted') {
			throw new AppException("Ce rapport n'est plus valide!");
		}

		switch ($rapport->type) {
			case 'recrutement':
				// verifier si la comission a ete paye 

				if($rapport->statut_paiement_commission == 'paye') {
					throw new AppException("Annulation impossible , la comission a deja ete paye!");
				}

				if($rapport->credit_utilise == 'cga') {
					// CGA
					// retour du solde dans le compte cga
					$cga = $vendeurs->cgaAccount();
					$cga->solde += $rapport->montant_ttc;
					$rapport->state = 'aborted';

					// retour de la quantite dans le stock du vendeur

					#recuperation du stock vendeur
					$stock_vendeur_terminal = $sv->where('vendeurs',$vendeurs->username)
						->where('produit',$p->where('with_serial',1)->first()->reference)->first();
					
					$stock_vendeur_parabole = $sv->where('vendeurs',$vendeurs->username)
						->where('produit',$p->where('with_serial',0)->first()->reference)->first();

					// update de la quantite des materiels
					$new_qt_terminal = $stock_vendeur_terminal->quantite + $rapport->quantite;
					$new_qt_parabole = $stock_vendeur_parabole->quantite + $rapport->quantite;

					StockVendeur::where('vendeurs',$vendeurs->username)
						->where('produit',$p->where('with_serial',1)->first()->reference)
						->update([
							'quantite'	=>	$new_qt_terminal
						]);

					StockVendeur::where('vendeurs',$vendeurs->username)
					->where('produit',$p->where('with_serial',0)->first()->reference)
					->update([
						'quantite'	=>	$new_qt_parabole
					]);

						##@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
					// renvoi des numeros de series a l'etat inactif
					$serialsNumbers = $rapport->exemplaire();

					foreach($serialsNumbers as $serial) {
						$serial->rapports = NULL;
						$serial->status = 'inactif';
						$serial->save();
					}

					// suppression des abonnements actifs

					$abonnements = $rapport->abonnements();

					foreach($abonnements as $key => $value) {
						$options = $value->options();

						if($options) {
							foreach($options as $_value) {
								$_value->delete();
							}	
						}
						$value->delete();
					}

					// enregistrement de la notification
					$n = $this->sendNotification("Annulation de Rapport","Le rapport du ".$rapport->date_rapport." a ete annule",$vendeurs->username);
					$n->save();

					$n = $this->sendNotification("Annulation de Rapport","Vous avez annule le rapport du ".$rapport->date_rapport." pour : ".$vendeurs->localisation,'root');
					$n->save();
					
					$n = $this->sendNotification("Annulation de Rapport","Vous avez annule le rapport du ".$rapport->date_rapport." pour : ".$vendeurs->localisation,'admin');
					$n->save();

					$cga->save();
					$rapport->save();
					
				}
				else {
					// REX
				}
			break;
			case 'reabonnement':
				// recuperation du compte cga
				// verifier si la comission a ete paye 

				if($rapport->statut_paiement_commission == 'paye') {
					throw new AppException("Annulation impossible , la comission a deja ete paye!");
				}

				if($rapport->credit_utilise == 'cga') {
					// CGA
					$cga = $vendeurs->cgaAccount();
					$cga->solde += $rapport->montant_ttc;
					$rapport->state = 'aborted';

					$abonnements = $rapport->abonnements();

					foreach($abonnements as $key => $value) {
						$options = $value->options();
						$upgrade = $value->upgrades();

						if($options) {
							foreach($options as $_key => $_value) {
								$_value->delete();
							}
						}

						if($upgrade) {
							foreach($upgrade as $__key => $__value) {
								$__value->delete();
							}
						}

						$value->delete();
					}

					// enregistrement de la notification
					$n = $this->sendNotification("Annulation de Rapport","Le rapport du ".$rapport->date_rapport." a ete annule",$vendeurs->username);
					$n->save();

					$n = $this->sendNotification("Annulation de Rapport","Vous avez annule le rapport du ".$rapport->date_rapport." pour : ".$vendeurs->localisation,'root');
					$n->save();

					$n = $this->sendNotification("Annulation de Rapport","Vous avez annule le rapport du ".$rapport->date_rapport." pour : ".$vendeurs->localisation,'admin');
					$n->save();

					$cga->save();
					$rapport->save();
					
				}
				else {
					// REX
				}
			break;

			case 'migration':
				$rapport = $r->find($request->input('id_rapport'));
				$rapport->state = 'aborted';

				#recuperation du stock vendeur
				$stock_vendeur_terminal = $sv->where('vendeurs',$vendeurs->username)
				->where('produit',$p->where('with_serial',1)->first()->reference)->first();

				// update de la quantite des materiels
				$new_qt_terminal = $stock_vendeur_terminal->quantite + $rapport->quantite;

				StockVendeur::where('vendeurs',$vendeurs->username)
					->where('produit',$p->where('with_serial',1)->first()->reference)
					->update([
						'quantite'	=>	$new_qt_terminal
					]);

				// renvoi des numeros de series a l'etat inactif
				$serialsNumbers = $rapport->exemplaire();

				foreach($serialsNumbers as $serial) {
					$serial->rapports = NULL;
					$serial->status = 'inactif';
					$serial->save();
				}

				$rapport->save();

				return response()
					->json('done');
					
			break;
			
			default:
				throw new AppException("Erreur , Veuillez ressayer!");
			break;
		}
		return response()->json('done');
	} catch (AppException $e) {
		header("Erreur !",true , 422);
		die(json_encode($e->getMessage()));
	}
}

// SET RAPPORT VENTE PARAMETERS
	public function getRapporParameters(\App\ComissionSetting $cs) {
		try {
			return response()
				->json($cs->all()->first());
		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}

	public function setRapportParameters(Request $request , \App\ComissionSetting $cs) {
		try {
			$validation = $request->validate([
				'pourcent_recrut'	=>	'required|numeric',
				'pourcent_reabo'	=>	'required|numeric',
				'password_confirmation'	=>	'required'
			]);

			if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
				throw new AppException("Mot de passe invalide !");
			}
			// 
			$tmp = $cs->find(1);
			
			if($tmp) {
				// existe deja modifier
				$tmp->pourcentage_recrutement = $request->input('pourcent_recrut');
				$tmp->pourcentage_reabonnement = $request->input('pourcent_reabo');
				$tmp->save();
			}
			else {
				// n'existe pas ajouter

				$cs->pourcentage_recrutement = $request->input('pourcent_recrut');
				$cs->pourcentage_reabonnement = $request->input('pourcent_reabo');
				$cs->save();
			}

			// 

			return response()
				->json('done');
		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}
}
