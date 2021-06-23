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
use App\TransactionAfrocash;
use App\Kits;
use App\Interval;
use App\IntervalProduit;

Trait Rapports {

	public function isExistRapportOnThisDate(Carbon $date,$vendeurs,$type = 'recrutement') {
		$temp = RapportVente::where([
			'date_rapport'  =>  $date->toDateTimeString(),
			'vendeurs'  =>  $vendeurs,
				'type'	=>	$type,
				'state'	=>	'unaborted'
		])->get();

		if(count($temp) >= 2) {
			return true;
		}
		return false;
	}

	/**
	 * existence de reabonnement a une date
	 */
	public function isExistRapportOnThisDateForReabo(Carbon $date, $vendeurs , $type = 'reabonnement')
	{
		$temp = RapportVente::where([
			'date_rapport'  =>  $date->toDateTimeString(),
			'vendeurs'  =>  $vendeurs,
				'type'	=>	$type,
				'state'	=>	'unaborted'
		])->first();

		if($temp) {
			return true;
		}
		return false;
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

				$diff = $now->diffInDays($debut);
				
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
	public function sendRapport($slug , Exemplaire $e ,RapportPromo $rp , Promo $p , StockVendeur $sv,Produits $produit , \App\ComissionSetting $cs) {
		try {
			if($slug) {
				switch ($slug) {
					case 'recrutement':
						# RAPPORT DE RECRUTEMENT

						$validation = request()->validate([
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

						#VERIFIER L'EXISTENCE D'UN RAPPORT A CETTE DATE POUR CE VENDEUR
						if($this->isExistRapportOnThisDate(new Carbon(request()->date),request()->vendeurs)) {
							// throw new AppException("Un rapport existe deja a cette date pour ce vendeur!");
							throw new AppException("Rapport maximum atteint a cette date pour ce vendeur.");
						}

						#VERIFIER SI LE SOLDE EXISTE POUR CE VENDEUR
						if(!$this->isCgaDisponible(request()->vendeurs,request()->montant)) {
							throw new AppException("Solde indisponible");
						}

						# VERIFIER LA DISPONIBILITE DES NUMEROS MATERIELS

						foreach(request()->serial_number as $value) {
							if(!$this->checkSerial($value,request()->vendeurs,$e)) {
								throw new AppException("Materiel invalide : ".$value);
							}
						}

						#NOUVEAU RAPPORT
						$rapport = new RapportVente;

						do {
							$rapport->id_rapport =  Str::random(10).'_'.time();
						} while ($rapport->isExistRapportById());

						$temp_date = new Carbon(request()->date);
						$rapport->date_rapport = $temp_date->toDateTimeString();
						$rapport->vendeurs = request()->vendeurs;
						$rapport->montant_ttc = request()->montant_ttc;
						$rapport->quantite = request()->quantite_materiel;
						$rapport->credit_utilise = 'cga';
						$rapport->type = 'recrutement';
						$rapport->calculCommission('recrutement',$cs);

						$id_rapport = $rapport->id_rapport;


						# DEBITE DU CREDIT CGA
						$user_rapport = User::where('username',request()->vendeurs)
							->first();
						
						$cga_account = $user_rapport->cgaAccount();
						$cga_account->solde -= request()->montant_ttc;

						# RECUPERATION DES INFOS LIES AU NUMEROS MATERIELS

						$serialNumbers = Exemplaire::whereIn('serial_number',request()->serial_number)
							->get();

						foreach($serialNumbers as $value) {
							$value->status = 'actif';
							$value->rapports = $id_rapport;
						}

						# DEBIT DU STOCK VENDEUR
						$produit = $serialNumbers->first()->produit();
						$article = $produit->articles()
							->first()
							->kits()
							->first()
							->articles()
							->select('produit')
							->groupBy('produit')
							->get();

						$user_stock = StockVendeur::where('vendeurs',request()->vendeurs)
							->whereIn('produit',$article)
							->get();


						foreach($user_stock as $value) {
							$value->quantite -= request()->quantite_materiel;
						}

						// LA PROMO EXISTE SUR LE MATERIEL SAT
						$tmp_promo = $this->isExistPromo();
						
						if($tmp_promo) {
							// la promo est active
							$promo_fin_to_carbon_date = new Carbon($tmp_promo->fin);
							$promo_debut_to_carbon_date = new Carbon($tmp_promo->debut);
							$rapport_date_to_carbon_date = new Carbon(request()->date);//new Carbon($request->input('date'));
							if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// le rapport est en mode promo
								// AJOUT DU RAPPORT PROMO
								do {
									$rp->id =  Str::random(10).'_'.time();
								} while ($rp->isExistId());

								$rp->quantite_a_compenser = request()->quantite_materiel;
								$rp->compense_espece = request()->quantite_materiel * $tmp_promo->subvention;
								$rp->promo = $tmp_promo->id;
								$rapport->id_rapport_promo = $rp->id;
								$rapport->promo = $tmp_promo->id;
								$rp->save();
							}
						} else {
							// la promo n'est pas active
							if(request()->promo_id != 'none') {
								// le rapport appartien a une promo
								$thePromo = $p->find(request()->promo_id);//$p->find($request->input('promo_id'));

								$promo_fin_to_carbon_date = new Carbon($thePromo->fin);

								$promo_debut_to_carbon_date = new Carbon($thePromo->debut);

								$rapport_date_to_carbon_date = new Carbon(request()->date);//new Carbon($request->input('date'));

								if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// 	// le rapport est en mode promo
								// 	// AJOUT DU RAPPORT PROMO
									do {
										$rp->id =  Str::random(10).'_'.time();
									} while ($rp->isExistId());

									$rp->quantite_a_compenser = request()->quantite_materiel;//$request->input('quantite_materiel');
									$rp->compense_espece = request()->quantite_materiel * $thePromo->subvention;
									$rp->promo = $thePromo->id;
									$rapport->id_rapport_promo = $rp->id;

									$rapport->promo = $thePromo->id;

									$rp->save();
								} else {
									throw new AppException("La date choisi n'est pas inclut dans la periode de promo !");
								}

							}
						}

						

						// ENREGISTREMENT DES ABONNEMENTS
						$abonnement_data = [];
						$allAbonnOption_data = [];
						foreach(request()->serial_number as $key => $value) {
							$abonnement_data[$key] = new Abonnement;
							$abonnement_data[$key]->makeAbonnementId();
							$abonnement_data[$key]->rapport_id = $id_rapport;
							$abonnement_data[$key]->serial_number = $value;
							$abonnement_data[$key]->debut = request()->debut[$key];//$request->input('debut')[$key];
							$abonnement_data[$key]->duree = request()->duree[$key]; //$request->input('duree')[$key];
							$abonnement_data[$key]->formule_name = request()->formule[$key]; //$request->input('formule')[$key];

							// VERIFICATION DE L'EXISTENCE DE L'OPTION
							if(array_key_exists($key,request()->options)) {//$request->input('options')
								$allAbonnOption_data[$key] = new AbonneOption;
								$allAbonnOption_data[$key]->id_abonnement = $abonnement_data[$key]->id;
								$allAbonnOption_data[$key]->id_option = request()->options[$key]; //$request->input('options')[$key];
							}
						}

						// TRANSACTION PAIEMENT MARGE MATERIEL
						$mat = $serialNumbers->first()->produit();
						$montantTransaction = ceil(($mat->marge / 1.18) * request()->quantite_materiel);

						$receiver_account = $user_rapport->afroCash()->first();
						$sender_user = User::where('type','logistique')
							->first();
						
						$sender_account = $sender_user->afroCash()->first();
						
						#ENREGISTREMENT DE LA TRANSACTION // ACTION DISPONIBLE SEULEMENT POUR LE DISTRIBUTEUR

						if($user_rapport->type == 'v_da') {

							$receiver_account->solde += $montantTransaction;
							$sender_account->solde -= $montantTransaction;

							$trans = new TransactionAfrocash;
							$trans->compte_credite = $receiver_account->numero_compte;
							$trans->compte_debite = $sender_account->numero_compte;
							$trans->montant = $montantTransaction;
							$trans->motif = "Paiement_Marge_Materiel";
							$trans->rapport_id = $id_rapport;

						}


						// TRAITEMENT EN CAS DE PROMO SUR LA FORMULE

						$tmp_promo = $this->isExistPromo('on_formule');

						$transPromoOnFormule = new TransactionAfrocash;
						$transPromoOnFormule->compte_credite = $receiver_account->numero_compte;
						$transPromoOnFormule->compte_debite = $sender_account->numero_compte;
						$transPromoOnFormule->motif = "SUBVENTION_PROMO";
						$transPromoOnFormule->rapport_id = $id_rapport;

						$subventionPromo = 0;
						
						
						if($tmp_promo)
						{
							// la promo est active
							foreach(request()->formule as $value)
							{		
								if(in_array($value,$tmp_promo->promoFormule()))
								{
									$subventionPromo += $tmp_promo->subvention;
								}
							}
							$transPromoOnFormule->montant = $subventionPromo;
							$rapport->promo_id = $tmp_promo->id;
							$rapport->promo = true;
						}
						else
						{
							// la promo est inactive
							if(request()->promo_id != 'none')
							{
								$laPromo = $p->find(request()->promo_id);
								if($laPromo->type == 'on_formule')
								{
									$promo_fin_to_carbon_date = new Carbon($laPromo->fin);
									$promo_debut_to_carbon_date = new Carbon($laPromo->debut);
									$rapport_date_to_carbon_date = new Carbon(request()->date);

									foreach(request()->formule as $value)
									{		
										if(in_array($value,$laPromo->promoFormule()))
										{
											$subventionPromo += $laPromo->subvention;
										}
									}
	
									if(!($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date))
									{
										throw new AppException("La date choisie ne se trouve pas dans la periode promo.");
									}
									
									$transPromoOnFormule->montant = $subventionPromo;
									$rapport->promo_id = $laPromo->id;
									$rapport->promo = true;
								}
							}

						}

						if($subventionPromo > 0)
						{
							$receiver_account->solde += $subventionPromo;
							$sender_account->solde -= $subventionPromo;
						}




						// return response()->json([$transPromoOnFormule,$subventionPromo],200);
						
						foreach($user_stock as $value) {
							$value->update();
						}

						$cga_account->update();						
						$rapport->save();

						foreach($serialNumbers as $value) {
							$value->update();
						}

						foreach(request()->serial_number as $key => $value) {
							$abonnement_data[$key]->save();
							if(array_key_exists($key,$allAbonnOption_data) && !is_null($allAbonnOption_data[$key]->id_option)) {
								$allAbonnOption_data[$key]->save();
							}							
						}
						$receiver_account->update();
						$sender_account->update();
						if($user_rapport->type == 'v_da')
						{
							$trans->save();
						}
						if($subventionPromo > 0)
						{
							$transPromoOnFormule->save();
						}
						
												
						// redirection
						return response()
							->json('done',200);
						
					break;
					case 'reabonnement':
						# RAPPORT DE REABONNEMENT
						$validation = request()->validate([
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


						# VERIFICATION DE L'EXISTENCE D'UN RAPPORT A CETTE DATE

						if($this->isExistRapportOnThisDateForReabo(new Carbon(request()->date),request()->vendeurs,'reabonnement')) {
							throw new AppException("Un rapport existe deja a cette date!");
						}

						# VERIFICATION DE LA DISPONIBILITE DU SOLDE CGA DANS LE COMPTE DU VENDEUR

						if(!(request()->type_credit == 'cga' && $this->isCgaDisponible(request()->vendeurs,request()->montant))) {
							throw new AppException("Solde indisponible !");
						}

						$rapport = new RapportVente;
						$rapport->makeRapportId();
						$rapport->vendeurs = request()->vendeurs; //$request->input('vendeurs');
						$rapport->montant_ttc = request()->montant_ttc; //$request->input('montant_ttc');
						$rapport->type  = 'reabonnement';
						$rapport->credit_utilise  = request()->type_credit; //$request->input('type_credit');
						$rapport->date_rapport  = request()->date; //$request->input('date');
						$rapport->calculCommission('reabonnement',$cs);


						# DEBITE DU CREDIT CGA
						$user_rapport = User::where('username',request()->vendeurs)
							->first();
						
						$cga_account = $user_rapport->cgaAccount();
						$cga_account->solde -= request()->montant_ttc;

						// LA PROMO EXISTE
						$tmp_promo = $this->isExistPromo();
						
						if($tmp_promo) {

							$promo_fin_to_carbon_date = new Carbon($tmp_promo->fin);
							$promo_debut_to_carbon_date = new Carbon($tmp_promo->debut);
							$rapport_date_to_carbon_date = new Carbon(request()->date); //new Carbon($request->input('date'));

							if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// le rapport est en mode promo
								$rapport->promo = $tmp_promo->id;
							}

						} else {
							// la promo n'est pas active
							if(request()->promo_id != 'none') {
								// le rapport appartien a une promo
								$thePromo = $p->find(request()->promo_id); //$p->find($request->input('promo_id'));

								$promo_fin_to_carbon_date = new Carbon($thePromo->fin);

								$promo_debut_to_carbon_date = new Carbon($thePromo->debut);

								$rapport_date_to_carbon_date = new Carbon(request()->date); //new Carbon($request->input('date'));

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

						# VERIFIER SI LE MATERIEL EST ACTIF
						$serialNumbers = Exemplaire::whereIn('serial_number',request()->serial_number)
							->get();
						foreach($serialNumbers as $value) {
							if($value->status == 'inactif' && $value->origine == 1) {
								throw new AppException("Attention materiel vierge : ".$value->serial_number);
							} 
						}

						# VERIFICATION DE LA DATE DE DEBUT EN CAS D'UN ABONNEMENT ACTIF

						foreach(request()->serial_number as $key => $value) {
							if(array_key_exists($key,request()->upgrade) && request()->upgrade[$key]) {
								#CECI EST UN UPGRADE
								#PAS BESOIN DE TEST
							}
							else {
								# CECI EST UN ABONNEMENT SIMPLE
								
								$dateSuggest = $this->checkSerialDebutDate($value);
								$choiceDate = request()->debut[$key] ? new Carbon(request()->debut[$key]) : null;
								
								if(is_null($choiceDate)) {
									throw new AppException("Erreur ! ressayez ...");
								}

								# TEST SUR LA DATE DE DEBUT QUI DOIT ETRE SUPERIEUR OU EGAL A LA DATE D'ACTIVATION

								$date_rapport = new Carbon(request()->date); //new Carbon($request->input('date'));
								if($choiceDate < $date_rapport) {
									throw new AppException("Le debut doit etre egal ou superieur a la date d'activation !");
								}

								if($choiceDate < $dateSuggest) {
									throw new AppException("Erreur sur la date de debut pour :".$value.",la date suggeree est : `".$dateSuggest."`");
								}

							}
						}

						#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

						$abonnement_data = [];
						$allAbonnOption_data = [];
						$upgrade = [];

						
						foreach(request()->serial_number as $key	=>	$value) {
							$tmp = $e->find($value);

							// 

							$abonnement_data[$key] = new Abonnement;
							$abonnement_data[$key]->makeAbonnementId();
							$abonnement_data[$key]->rapport_id = $id_rapport;
							$abonnement_data[$key]->serial_number = $value;
							$abonnement_data[$key]->formule_name = request()->formule[$key]; //$request->input('formule')[$key];

							
							if(array_key_exists($key,request()->upgrade) && request()->upgrade[$key]){
								// abonnement avec upgrade
								$upgrade[$key] = new Upgrade;

								if(array_key_exists($key,request()->upgradeData) && !is_null(request()->upgradeData[$key])){

									$upgrade[$key]->depart = request()->upgradeData[$key]['formule_name']; //$request->input('upgradeData')[$key]['formule_name'];
									$upgrade[$key]->old_abonnement = request()->upgradeData[$key]['id']; //$request->input('upgradeData')[$key]['id'];

									// tester la conformite de la date et de la duree

									$debutTest = new Carbon(request()->debut[$key]); //new Carbon($request->input('debut')[$key]);
									$debutRequest = new Carbon(request()->upgradeData[$key]['debut']); //new Carbon($request->input('upgradeData')[$key]['debut']);

									if($debutTest->ne($debutRequest)) {
										throw new AppException("date de debut non conforme pour : `".$value."` ");
									}

									if(request()->upgradeData[$key]['mois_restant'] != request()->duree[$key]) {
										throw new AppException("Duree non conforme pour : ".$value);
									}
								}
								else {
									$upgrade[$key]->depart = request()->old_formule[$key]; //$request->input('old_formule')[$key];
								}
								
								$upgrade[$key]->finale = request()->formule[$key];//$request->input('formule')[$key];
								$upgrade[$key]->id_abonnement = $abonnement_data[$key]->id;
								
								$abonnement_data[$key]->upgrade = true;

							}
							else {

								// verifier si un abonnement existe a la meme date de debut  pour le meme numero de materiel

								if($abonnement_data[$key]->isExistAbonnementForDebutDate()) {
									throw new AppException("Un abonnement existe deja a cette date de debut pour :`".$value."`!");
								}
							}

							$abonnement_data[$key]->debut = request()->debut[$key]; //$request->input('debut')[$key];
							$abonnement_data[$key]->duree = request()->duree[$key]; //$request->input('duree')[$key];
							
							// VERIFICATION DE L'EXISTENCE DE L'OPTION
							if(array_key_exists($key, request()->options)) {	
								$allAbonnOption_data[$key] = new AbonneOption;
								$allAbonnOption_data[$key]->id_abonnement = $abonnement_data[$key]->id;
								$allAbonnOption_data[$key]->id_option = request()->options[$key]; //$request->input('options')[$key];
							}

							if($tmp) {
								// get serial info from database
								
							} else {
								// insert into databse

								# TROUVER LE MATERIEL CORRESPONDANT A TRAVERS L'INTERVAL DU NUMERO DE MATERIEL
								$debut_serial = Str::substr($value,0,3);
								$interval = Interval::where('interval_serial_first','<=',$debut_serial)
									->where('interval_serial_last','>=',$debut_serial)
									->first();

								$_data = $interval->produit()
									->first()
									->produitData()
									->first();
								#
								$exem = new Exemplaire;
								$exem->status = 'actif';
								$exem->serial_number = $value;
								$exem->origine = false;
								$exem->produit = $_data->reference;
								$exem->save();
							}
						}

						$cga_account->save();
						$rapport->save();

						foreach(request()->serial_number as $key => $value) {

							$abonnement_data[$key]->save();

							if(array_key_exists($key,$allAbonnOption_data) && !is_null($allAbonnOption_data[$key]->id_option)) {
								$allAbonnOption_data[$key]->save();
							}

							if(array_key_exists($key,$upgrade)) {
								$upgrade[$key]->save();
							}
						}

						# REDIRECTION

						return response()
							->json('done');

					break;
					case 'migration':
						# RAPPORT DE MIGRATION
						$validation = request()->validate([
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
	
						foreach(request()->serial_number as $value) {
							if(!$this->checkSerial($value,request()->vendeurs,$e)) {
								throw new AppException("Numero de Serie Invalide  : ". $value);
							}
						}

						# VERIFICATION DE L 'EXISTENCE DU RAPPORT DE MIGRATION A CETTE DATE
						if($this->isExistRapportOnThisDate(new Carbon(request()->date),request()->vendeurs,'migration')) {
							throw new AppException("Un rapport existe deja a cette date!");
						}

						$rapport = new RapportVente;
						$rapport->makeRapportId();
						$rapport->date_rapport  = request()->date;
						$rapport->vendeurs  = request()->vendeurs;
						$rapport->quantite = request()->quantite_materiel;
						$rapport->type = 'migration';

						$id_rapport = $rapport->id_rapport;
						// LA PROMO EXISTE
						$tmp_promo = $this->isExistPromo();

						if($tmp_promo) {
							$promo_fin_to_carbon_date = new Carbon($tmp_promo->fin);
							$promo_debut_to_carbon_date = new Carbon($tmp_promo->debut);
							$rapport_date_to_carbon_date = new Carbon(request()->date);
							if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// le rapport est en mode promo
								$rapport->promo = $tmp_promo->id;
							}
						} else {
							// la promo n'est pas active
							if(request()->promo_id != 'none') {
								// le rapport appartien a une promo
								$thePromo = $p->find(request()->promo_id);

								$promo_fin_to_carbon_date = new Carbon($thePromo->fin);

								$promo_debut_to_carbon_date = new Carbon($thePromo->debut);

								$rapport_date_to_carbon_date = new Carbon(request()->date);

								if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// 	// le rapport est en mode promo
								// 	// AJOUT DU RAPPORT PROMO
									$rapport->promo = $thePromo->id;

									// $rp->save();
								} else {
									throw new AppException("La date choisi n'est pas inclut dans la periode de promo !");
								}

							}
						}
						
						$rapport->save();

						# CHANGEMENT DE STATUS DES MATERIELS

						$serialNumbers = Exemplaire::whereIn('serial_number',request()->serial_number)
							->get();

						foreach($serialNumbers as $value) {
							$value->status = 'actif';
							$value->rapports = $id_rapport;
							$value->update();
						}

						# DEBIT DE LA QUANTITE DANS LE STOCK DU VENDEURS

						$produit = $serialNumbers->first()->produit();

						$user_stock = StockVendeur::where('vendeurs',request()->vendeurs)
							->where('produit',$produit->reference)
							->get();

						foreach($user_stock as $value) {
							$value->quantite -= request()->quantite_materiel;
							$value->update();
						}

						// TRANSACTION PAIEMENT MARGE MATERIEL
						$mat = $serialNumbers->first()->produit();
						$montantTransaction = ceil(($mat->marge / 1.18) * request()->quantite_materiel);

						$user_rapport = User::where('username',request()->vendeurs)
							->first();

						$receiver_account = $user_rapport->afroCash()->first();
						$sender_user = User::where('type','logistique')
							->first();
						
						$sender_account = $sender_user->afroCash()->first();

						$receiver_account->solde += $montantTransaction;
						$sender_account->solde -= $montantTransaction;

						
						#ENREGISTREMENT DE LA TRANSACTION // ACTION DISPONIBLE UNIQUEMENT POUR LE DISTRIBUTEUR

						if($user_rapport->type == 'v_da') {

							$trans = new TransactionAfrocash;
							$trans->compte_credite = $receiver_account->numero_compte;
							$trans->compte_debite = $sender_account->numero_compte;
							$trans->montant = $montantTransaction;
							$trans->motif = "Paiement_Marge_Materiel";
							$trans->rapport_id = $id_rapport;
													
							$receiver_account->update();
							$sender_account->update();
							$trans->save();
						}
						
						

						return response()
							->json('done');

					break;
					case 'migration-gratuite' : 
						# RAPPORT DE MIGRATION
						$validation = request()->validate([
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
	
						foreach(request()->serial_number as $value) {
							if(!$this->checkSerial($value,request()->vendeurs,$e)) {
								throw new AppException("Numero de Serie Invalide  : ". $value);
							}
						}

						# VERIFICATION DE L 'EXISTENCE DU RAPPORT DE MIGRATION A CETTE DATE
						if($this->isExistRapportOnThisDate(new Carbon(request()->date),request()->vendeurs,'migration')) {
							throw new AppException("Un rapport existe deja a cette date!");
						}

						$rapport = new RapportVente;
						$rapport->makeRapportId();
						$rapport->date_rapport  = request()->date;
						$rapport->vendeurs  = request()->vendeurs;
						$rapport->quantite = request()->quantite_materiel;
						$rapport->type = 'migration';

						$id_rapport = $rapport->id_rapport;
						// LA PROMO EXISTE
						$tmp_promo = $this->isExistPromo();

						if($tmp_promo) {
							$promo_fin_to_carbon_date = new Carbon($tmp_promo->fin);
							$promo_debut_to_carbon_date = new Carbon($tmp_promo->debut);
							$rapport_date_to_carbon_date = new Carbon(request()->date);
							if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// le rapport est en mode promo
								$rapport->promo = $tmp_promo->id;
							}
						} else {
							// la promo n'est pas active
							if(request()->promo_id != 'none') {
								// le rapport appartien a une promo
								$thePromo = $p->find(request()->promo_id);

								$promo_fin_to_carbon_date = new Carbon($thePromo->fin);

								$promo_debut_to_carbon_date = new Carbon($thePromo->debut);

								$rapport_date_to_carbon_date = new Carbon(request()->date);

								if($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date) {
								// 	// le rapport est en mode promo
								// 	// AJOUT DU RAPPORT PROMO
									$rapport->promo = $thePromo->id;

									// $rp->save();
								} else {
									throw new AppException("La date choisi n'est pas inclut dans la periode de promo !");
								}

							}
						}
						
						$rapport->save();

						# CHANGEMENT DE STATUS DES MATERIELS

						$serialNumbers = Exemplaire::whereIn('serial_number',request()->serial_number)
							->get();

						foreach($serialNumbers as $value) {
							$value->status = 'actif';
							$value->rapports = $id_rapport;
							$value->update();
						}

						# DEBIT DE LA QUANTITE DANS LE STOCK DU VENDEURS

						$produit = $serialNumbers->first()->produit();

						$user_stock = StockVendeur::where('vendeurs',request()->vendeurs)
							->where('produit',$produit->reference)
							->get();

						foreach($user_stock as $value) {
							$value->quantite -= request()->quantite_materiel;
							$value->update();
						}

						// TRANSACTION PAIEMENT MARGE MATERIEL
						$mat = $serialNumbers->first()->produit();
						$montantTransaction = ceil(($mat->marge / 1.18) * request()->quantite_materiel);

						$user_rapport = User::where('username',request()->vendeurs)
							->first();

						$receiver_account = $user_rapport->afroCash()->first();
						$sender_user = User::where('type','logistique')
							->first();
						
						$sender_account = $sender_user->afroCash()->first();

						$receiver_account->solde += $montantTransaction;
						$sender_account->solde -= $montantTransaction;

						
						#ENREGISTREMENT DE LA TRANSACTION // ACTION DISPONIBLE UNIQUEMENT POUR LE DISTRIBUTEUR

						// if($user_rapport->type == 'v_da') {

						// 	$trans = new TransactionAfrocash;
						// 	$trans->compte_credite = $receiver_account->numero_compte;
						// 	$trans->compte_debite = $sender_account->numero_compte;
						// 	$trans->montant = $montantTransaction;
						// 	$trans->motif = "Paiement_Marge_Materiel";
						// 	$trans->rapport_id = $id_rapport;
													
						// 	$receiver_account->update();
						// 	$sender_account->update();
						// 	$trans->save();
						// }

						return response()
							->json('done');
					break;
					case 'recrutement-easy':
						$validation = request()->validate([
							'quantite_materiel'  =>  'required|min:1',
							'montant_ttc' =>  'required|numeric',
							'vendeurs'   =>  'required|exists:users,username',
							'date'  =>  'required|before_or_equal:'.(date("Y/m/d",strtotime("now"))),
							'serial_number.*'	=>	'required|distinct|exists:exemplaire,serial_number',
							'debut.*'	=>	'required|date|after_or_equal:date',
							// 'formule.*'	=>	'required|string|exists:formule,nom',
							// 'duree.*'	=>	'required|numeric'
						],[
							'required'  =>  'Veuillez remplir le champ `:attribute`',
							'numeric'  =>  '`:attribute` doit etre une valeur numeric',
							'before_or_equal' =>  'Vous ne pouvez ajouter de rapport a cette date',
							'after_or_equal'	=>	'Le debut doit etre egal ou superieur a la date d\'activation',
							'distinct'	=>	"Doublons repere!",
							'exists'	=>	'Numero inexistant! : `:attribute`'
						]);

						#VERIFIER L'EXISTENCE D'UN RAPPORT A CETTE DATE POUR CE VENDEUR
						if($this->isExistRapportOnThisDate(new Carbon(request()->date),request()->vendeurs)) {
							throw new AppException("Un rapport existe deja a cette date pour ce vendeur!");
						}

						// #VERIFIER SI LE SOLDE EXISTE POUR CE VENDEUR
						if(!$this->isCgaDisponible(request()->vendeurs,request()->montant)) {
							throw new AppException("Solde indisponible");
						}

						// # VERIFIER LA DISPONIBILITE DES NUMEROS MATERIELS

						foreach(request()->serial_number as $value) {
							if(!$this->checkSerial($value,request()->vendeurs,$e)) {
								throw new AppException("Materiel invalide : ".$value);
							}
						}

						// #NOUVEAU RAPPORT
						$rapport = new RapportVente;

						do {
							$rapport->id_rapport =  Str::random(10).'_'.time();
						} while ($rapport->isExistRapportById());

						$temp_date = new Carbon(request()->date);
						$rapport->date_rapport = $temp_date->toDateTimeString();
						$rapport->vendeurs = request()->vendeurs;
						$rapport->montant_ttc = request()->montant_ttc;
						$rapport->quantite = request()->quantite_materiel;
						$rapport->credit_utilise = 'cga';
						$rapport->type = 'recrutement';
						$rapport->commission = 0;
						// $rapport->calculCommission('recrutement',$cs);

						$id_rapport = $rapport->id_rapport;


						// # DEBITE DU CREDIT CGA
						$user_rapport = User::where('username',request()->vendeurs)
							->first();
						
						$cga_account = $user_rapport->cgaAccount();
						$cga_account->solde -= request()->montant_ttc;

						// # RECUPERATION DES INFOS LIES AU NUMEROS MATERIELS

						$serialNumbers = Exemplaire::whereIn('serial_number',request()->serial_number)
							->get();

						foreach($serialNumbers as $value) {
							$value->status = 'actif';
							$value->rapports = $id_rapport;
						}

						// # DEBIT DU STOCK VENDEUR
						$produit = $serialNumbers->first()->produit();
						$article = $produit->articles()
							->first()
							->kits()
							->first()
							->articles()
							->select('produit')
							->groupBy('produit')
							->get();

						$user_stock = StockVendeur::where('vendeurs',request()->vendeurs)
							->whereIn('produit',$article)
							->get();


						foreach($user_stock as $value) {
							$value->quantite -= request()->quantite_materiel;
						}

						// // TRANSACTION PAIEMENT MARGE MATERIEL
						$mat = $serialNumbers->first()->produit();
						$montantTransaction = ceil(($mat->marge / 1.18) * request()->quantite_materiel);

						$receiver_account = $user_rapport->afroCash()->first();
						$sender_user = User::where('type','logistique')
							->first();
						
						$sender_account = $sender_user->afroCash()->first();

						if($user_rapport->type == 'v_da')
						{
							$receiver_account->solde += $montantTransaction;
							$sender_account->solde -= $montantTransaction;
						}

						// // LA PROMO EXISTE
						$subventionPromo = 0;
						$tmp_promo = $this->isExistPromo('kit_easy');

						$transPromo = new TransactionAfrocash;
						$transPromo->compte_credite = $receiver_account->numero_compte;
						$transPromo->compte_debite = $sender_account->numero_compte;
						$transPromo->motif = "SUBVENTION_PROMO";

						if($tmp_promo)
						{
							// la promo est active
							$transPromo->montant = $tmp_promo->subvention * request()->quantite_materiel;
							$subventionPromo = $tmp_promo->subvention * request()->quantite_materiel;
							$rapport->promo_id = $tmp_promo->id;
							$rapport->promo = true;
						}
						else
						{
							// la promo est inactive
							if(request()->promo_id != 'none')
							{
								$laPromo = $p->find(request()->promo_id);
								$subventionPromo = $laPromo->subvention * request()->quantite_materiel;
								$promo_fin_to_carbon_date = new Carbon($laPromo->fin);
								$promo_debut_to_carbon_date = new Carbon($laPromo->debut);
								$rapport_date_to_carbon_date = new Carbon(request()->date);

								if(!($promo_fin_to_carbon_date >= $rapport_date_to_carbon_date && $rapport_date_to_carbon_date >= $promo_debut_to_carbon_date))
								{
									throw new AppException("La date choisie ne se trouve pas dans la periode promo.");
								}
								
								$transPromo->montant = $laPromo->subvention * request()->quantite_materiel;
								$rapport->promo_id = $laPromo->id;
								$rapport->promo = true;
							}

						}

						if($subventionPromo > 0)
						{
							$receiver_account->solde += $subventionPromo;
							$sender_account->solde -= $subventionPromo;
						}
						
						
						// // ENREGISTREMENT DES ABONNEMENTS
						$abonnement_data = [];
						$allAbonnOption_data = [];
						foreach(request()->serial_number as $key => $value) {
							$abonnement_data[$key] = new Abonnement;
							$abonnement_data[$key]->makeAbonnementId();
							$abonnement_data[$key]->rapport_id = $id_rapport;
							$abonnement_data[$key]->serial_number = $value;
							$abonnement_data[$key]->debut = request()->debut[$key];//$request->input('debut')[$key];
							$abonnement_data[$key]->duree = request()->duree; //$request->input('duree')[$key];
							$abonnement_data[$key]->formule_name = request()->formule; //$request->input('formule')[$key];

							// VERIFICATION DE L'EXISTENCE DE L'OPTION
							if(array_key_exists($key,request()->options)) {//$request->input('options')
								$allAbonnOption_data[$key] = new AbonneOption;
								$allAbonnOption_data[$key]->id_abonnement = $abonnement_data[$key]->id;
								$allAbonnOption_data[$key]->id_option = request()->options[$key]; //$request->input('options')[$key];
							}
						}

						// foreach($user_stock as $value) {
						// 	$value->update();
						// }

						$cga_account->update();						
						$rapport->save();

						foreach($serialNumbers as $value) {
							$value->update();
						}

						foreach(request()->serial_number as $key => $value) {
							$abonnement_data[$key]->save();
							if(array_key_exists($key,$allAbonnOption_data) && !is_null($allAbonnOption_data[$key]->id_option)) {
								$allAbonnOption_data[$key]->save();
							}							
						}

						
						// #ENREGISTREMENT DE LA TRANSACTION // ACTION DISPONIBLE SEULEMENT POUR LE DISTRIBUTEUR

						if($user_rapport->type == 'v_da') {

							$trans = new TransactionAfrocash;
							$trans->compte_credite = $receiver_account->numero_compte;
							$trans->compte_debite = $sender_account->numero_compte;
							$trans->montant = $montantTransaction;
							$trans->motif = "Paiement_Marge_Materiel";
							$trans->rapport_id = $id_rapport;

							if($receiver_account->update() && $sender_account->update())
							{
								if($trans->save())
								{
									if($subventionPromo > 0)
									{
										if($transPromo->save())
										{
											return response()->json('done',200);
										}
									}
								}
							}
						}
						elseif($user_rapport->type == 'v_standart')
						{
							if($receiver_account->update() && $sender_account->update())
							{
								if($subventionPromo > 0)
								{
									if($transPromo->save())
									{
										return response('done',200);
									}
								}
							}
						}
						return response()->json('done',200);
					break;
					default:
						throw new AppException("Veuillez ressayez ulterieurement !");
					break;
				}
			}
		}
		catch(AppException $e) {
			header("Erreur",true,422);
			return response()->json($e->getMessage(),422);
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


	// FILTRE DES RAPPORTS

	public function filterRapportRequest($type,$state,$promo,$payState,$user,$from,$to) {
		try {

			$promoStateValue = [
				'en_promo'	=>	1,
				'hors_promo'	=>	0
			];

			$all = [];
			$comission = 0;

			if(request()->user()->type == 'v_da' || request()->user()->type == 'v_standart') {
				$user = request()->user()->username;
			}


			if($type && $state && $promo && $payState && $user) {
				if($user != 'all') {
					if($type != 'all') {
						if($state != 'all') {
							if($promo != 'all') {
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->whereNotNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->whereNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->where('vendeurs',$user);
								}
							}
							else {
								// promo / hors promo
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->whereNotNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->whereNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->where('vendeurs',$user);
								}
							}
						}
						else {
							// tous les status
							if($promo != 'all') {
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('type',$type)
										->where('promo',$promoStateValue[$promo])
										->whereNotNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('type',$type)
										->where('promo',$promoStateValue[$promo])
										->whereNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('type',$type)
										->where('promo',$promoStateValue[$promo])
										->where('vendeurs',$user);
								}
							}
							else {
								// promo / hors promo
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('type',$type)
										->whereNotNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('type',$type)
										->whereNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('type',$type)
									->where('vendeurs',$user);
								}
							}
						}
					}	
					else {
						// tous les types de rapport
						if($state != 'all') {
							if($promo != 'all') {
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->whereNotNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->whereNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->where('vendeurs',$user);
								}
							}
							else {
								// promo / hors promo
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('state',$state)
										->whereNotNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('state',$state)
										->whereNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('state',$state)
										->where('vendeurs',$user);
								}
							}
						}
						else {
							// tous les status
							if($promo != 'all') {
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('promo',$promoStateValue[$promo])
										->whereNotNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('promo',$promoStateValue[$promo])
										->whereNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('promo',$promoStateValue[$promo])
										->where('vendeurs',$user);
								}
							}
							else {
								// promo / hors promo
								if($payState == 'paye') {
									// paye
									$r = RapportVente::whereNotNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::whereNull('pay_comission_id')
										->where('vendeurs',$user);
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('vendeurs',$user);
								}
							}
						}
					}
				}
				else {
					// all users
					if($type != 'all') {
						if($state != 'all') {
							if($promo != 'all') {
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->whereNotNull('pay_comission_id');
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->whereNull('pay_comission_id');
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->where('promo',$promoStateValue[$promo]);
								}
							}
							else {
								// promo / hors promo
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->whereNotNull('pay_comission_id');
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('type',$type)
										->where('state',$state)
										->whereNull('pay_comission_id');
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('type',$type)
										->where('state',$state);
								}
							}
						}
						else {
							// tous les status
							if($promo != 'all') {
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('type',$type)
										->where('promo',$promoStateValue[$promo])
										->whereNotNull('pay_comission_id');
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('type',$type)
										->where('promo',$promoStateValue[$promo])
										->whereNull('pay_comission_id');
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('type',$type)
										->where('promo',$promoStateValue[$promo]);
								}
							}
							else {
								// promo / hors promo
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('type',$type)
										->whereNotNull('pay_comission_id');
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('type',$type)
										->whereNull('pay_comission_id');
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('type',$type);
								}
							}
						}
					}	
					else {
						// tous les types de rapport
						if($state != 'all') {
							if($promo != 'all') {
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->whereNotNull('pay_comission_id');
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('state',$state)
										->where('promo',$promoStateValue[$promo])
										->whereNull('pay_comission_id');
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('state',$state)
										->where('promo',$promoStateValue[$promo]);
								}
							}
							else {
								// promo / hors promo
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('state',$state)
										->whereNotNull('pay_comission_id');
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('state',$state)
										->whereNull('pay_comission_id');
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('state',$state);
								}
							}
						}
						else {
							// tous les status
							if($promo != 'all') {
								if($payState == 'paye') {
									// paye
									$r = RapportVente::where('promo',$promoStateValue[$promo])
										->whereNotNull('pay_comission_id');
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::where('promo',$promoStateValue[$promo])
										->whereNull('pay_comission_id');
								}
								else {
									// tous les status de paiements
									$r = RapportVente::where('promo',$promoStateValue[$promo]);
								}
							}
							else {
								// promo / hors promo
								if($payState == 'paye') {
									// paye
									$r = RapportVente::whereNotNull('pay_comission_id');
								}
								else if($payState == 'non_paye') {
									// impaye
									$r = RapportVente::whereNull('pay_comission_id');
								}
								else {
									// tous les status de paiements
									$r = RapportVente::select();
								}
							}
						}
					}
				}
			}
			// INTERVAL DE DATE PRIS EN COMPTE
			if($from != 'all' && $to != 'all') {
				
				$result = $r->whereDate('date_rapport','>=',$from)
					->whereDate('date_rapport','<=',$to)
					->orderBy('date_rapport','desc')
					->paginate(100);

			}
			else {

				$result = $r->orderBy('date_rapport','desc')
					->paginate(100);
			}

			// 
			
				
			$comission = $r->sum('commission');

			return response()
				->json([
					'all'	=>	$this->organizeRapport($result),
					'next_url'	=> $result->nextPageUrl(),
					'last_url'	=> $result->previousPageUrl(),
					'per_page'	=>	$result->perPage(),
					'current_page'	=>	$result->currentPage(),
					'first_page'	=>	$result->url(1),
					'first_item'	=>	$result->firstItem(),
					'total'	=>	$result->total(),
					'comission'	=>	$comission
				]);
		}
		catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}

// HISTORIQUE DE RAPPORTS DE VENTE
		public function getAllRapport(RapportVente $r,Request $request) {
			try {
				if($request->user()->type != 'v_da' && $request->user()->type != 'v_standart') {
					$all = $r->select()
						->orderBy('date_rapport','desc')
						->paginate(100);
				}
				else {
					$all = $request->user()->rapportVente()
						->orderBy('date_rapport','desc')
						->paginate(100);
				}

				return response()
					->json([
						'all'	=>	$this->organizeRapport($all),
						'next_url'	=> $all->nextPageUrl(),
						'last_url'	=> $all->previousPageUrl(),
						'per_page'	=>	$all->perPage(),
						'current_page'	=>	$all->currentPage(),
						'first_page'	=>	$all->url(1),
						'first_item'	=>	$all->firstItem(),
						'total'	=>	$all->total()
					]);
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
			if($value->type == 'recrutement')
			{
				$abonnement = $value->abonnementType() ? $value->abonnementType()->formule_name : '';
				$type = $abonnement == 'EASY TV' ? 'RECRUT EASYTV' : 'RECRUT CANAL SAT';
			}
			else {
				$type = $value->type;
			}
			$all[$key] = [
				'id'	=>	$value->id_rapport,
				'date'  =>  $value->date_rapport,
				'vendeurs'  =>  $value->vendeurs()->agence()->societe." ( ".$value->vendeurs()->localisation." )",
				'type'  =>	$type,
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
public function totalCommission(RapportVente $r , Request $request) {
	try {
		if($request->user()->type != 'v_da' && $request->user()->type != 'v_standart') {

			$commission = $r->whereIn('type',['recrutement','reabonnement'])
				->where('state','unaborted')
				->whereNull('pay_comission_id')
				->sum('commission');
		}
		else {
			$commission = $r->whereIn('type',['recrutement','reabonnement'])
				->where('state','unaborted')
				->whereNull('pay_comission_id')
				->where('vendeurs',$request->user()->username)
				->sum('commission');
		}
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
					

					$serialNumbers = $rapport->serialNumbers()
						->get();

					$produit = $serialNumbers->first()->produit();
						$article = $produit->articles()
							->first()
							->kits()
							->first()
							->articles()
							->select('produit')
							->groupBy('produit')
							->get();

					$user_stock = StockVendeur::where('vendeurs',$rapport->vendeurs)
						->whereIn('produit',$article)
						->get();


					foreach($user_stock as $value) {
						$value->quantite += $rapport->quantite;
						$value->update();
					}
						##@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
					// renvoi des numeros de series a l'etat inactif

					foreach($serialNumbers as $serial) {
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

					# ANNULATION DE LA TRANSACTION 
					$user_rapport = $rapport->vendeurs();
					$sender_account = $user_rapport->afroCash()->first();

					$receiver_user = User::where('type','logistique')
						->first();
					$receiver_account = $receiver_user->afroCash()->first();

					$trans = $rapport->transactions()
						->first();

					$sender_account->solde -= $trans ? $trans->montant : 0;
					$receiver_account->solde += $trans ? $trans->montant : 0;

					$new_trans = new TransactionAfrocash;
					$new_trans->compte_credite = $receiver_account->numero_compte;
					$new_trans->compte_debite = $sender_account->numero_compte;
					$new_trans->montant = $trans ? $trans->montant : 0;
					$new_trans->motif = "Annulation_Paiement_Marge_Materiel";
					$new_trans->rapport_id = $rapport->id_rapport;

					$sender_account->update();
					$receiver_account->update();
					$new_trans->save();
					############

					// enregistrement de la notification
					$n = $this->sendNotification("Annulation de Rapport","Le rapport du ".$rapport->date_rapport." a ete annule",$vendeurs->username);
					$n->save();

					$n = $this->sendNotification("Annulation de Rapport","Vous avez annule le rapport du ".$rapport->date_rapport." pour : ".$vendeurs->localisation,'root');
					$n->save();
					
					$n = $this->sendNotification("Annulation de Rapport","Vous avez annule le rapport du ".$rapport->date_rapport." pour : ".$vendeurs->localisation,'admin');
					$n->save();

					$cga->update();
					$rapport->update();
					
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

					$cga->update();
					$rapport->update();
					
				}
				else {
					// REX
				}
			break;

			case 'migration':
				$rapport = $r->find($request->input('id_rapport'));
				$rapport->state = 'aborted';

				#recuperation du stock vendeur

				$serialNumbers = $rapport->serialNumbers()
					->get();

				$produit = $serialNumbers->first() ? $serialNumbers->first()->produits()
					->where('with_serial',true)
					->first() : null;

				$user_stock = $produit ? StockVendeur::where('vendeurs',$rapport->vendeurs)
					->where('produit',$produit->reference)
					->first() : null;
				if($user_stock) {
					$user_stock->quantite -= $rapport->quantite;
				}

				// renvoi des numeros de series a l'etat inactif
				if($serialNumbers) {

					foreach($serialNumbers as $serial) {
						$serial->rapports = NULL;
						$serial->status = 'inactif';
						$serial->save();
					}
				}

				# ANNULATION DE LA TRANSACTION 
				$user_rapport = $rapport->vendeurs();
				$sender_account = $user_rapport->afroCash()->first();

				$receiver_user = User::where('type','logistique')
					->first();
				$receiver_account = $receiver_user->afroCash()->first();

				$trans = $rapport->transactions()
					->first();

				$sender_account->solde -= $trans ? $trans->montant : 0;
				$receiver_account->solde += $trans ? $trans->montant : 0;

				$new_trans = new TransactionAfrocash;
				$new_trans->compte_credite = $receiver_account->numero_compte;
				$new_trans->compte_debite = $sender_account->numero_compte;
				$new_trans->montant = $trans ? $trans->montant : 0;
				$new_trans->motif = "Annulation_Paiement_Marge_Materiel";
				$new_trans->rapport_id = $rapport->id_rapport;

				$sender_account->update();
				$receiver_account->update();
				$new_trans->save();
				############

				if($user_stock) {
					$user_stock->update();
				}
				$rapport->update();

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
