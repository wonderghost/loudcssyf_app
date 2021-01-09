<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth','admin'])->group(function () {
	// INTERVAL & FORMULES
	Route::get('/admin/get-interval-infos','AdminController@getIntervalInfos');
	Route::post('/admin/add-formule-interval','AdminController@addFormuleToInterval');

	// 
	Route::post('/admin/serial-number/actived','LogistiqueController@makeActivedSerial');
	// RESEAUX DES PDC ET PDRAF
	Route::get('/admin/pdc/list','PdcController@getList');

	Route::post('/admin/pdc/add','PdcController@addNewPdc');
	Route::post('/admin/pdraf/add','PdrafController@addNewPdraf');
	Route::get('/admin/pdraf/list','AdminController@creationPdraf');
	Route::get('/admin/pdraf/get-list','PdrafController@getListCreationPdraf');
	Route::post('/admin/pdraf/set-vendeur-for-reabo','PdrafController@setUserStandartForReabo');
	Route::post('/admin/pdraf/confirm-reabo-afrocash','PdrafController@confirmReaboAfrocash');
	Route::post('/admin/pdraf/confirm-recrutement-afrocash','PdrafController@confirmRecrutementAfrocash');
	Route::post('/admin/pdraf/remove-reabo-afrocash','PdrafController@removeReaboAfrocash');
	// Route::post('/admin/pdraf/remove-recrutement-afrocash','PdrafController@removeRecrutementAfrocash');

	Route::post('/admin/pdraf/change-status-reactivation-materiel','PdrafController@changeStatusReactivationMateriel');
	

	# affectation de pdraf a un pdc

	// get list all pdraf
	Route::get('/admin/pdraf/all','PdrafController@listPdraf');
	Route::post('/admin/pdraf/affectation/send-request','PdrafController@sendRequestPdrafAffectation');
	
	

	// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	// traitement parametre rapport vente
	Route::post('/admin/set-rapport-parameters','AdminController@setRapportParameters');
	Route::get('/admin/get-rapport-parameters','AdminController@getRapporParameters');
	// 
	Route::get('tmp','AdminController@emailTest');
	// affection des materiels dans les depots
	Route::get('/admin/infos-affectation','AdminController@getInfosAffectation');
	Route::post('/admin/send-affectation','AdminController@affectationMaterielToDepot');
	// @@@ Objectifs router@@@
	Route::post('/admin/objectifs/make-classify','ObjectifController@classificationVendeurByCA');
	Route::post('/admin/objectifs/finalise-make-objectif','ObjectifController@finaliseMakeObjectif');
	#listing de tous les objectifs sauvegarde
	Route::get('/admin/objectifs/list-all','ObjectifController@AllObjectifs');

	
	// recouvrement coursier
	Route::get('/admin/recouvrement/operations','RecouvrementController@operations');
	Route::get('/admin/recouvrement/all-transactions','RecouvrementController@allTransactions');
	Route::get('/admin/recouvrement/get-montant-du/{vendeur}','RecouvrementController@getMontantDuRecouvrement');
	Route::get('/admin/recouvrement/all-recouvrement','RecouvrementController@allRecouvrement');

	// annulation de rapport de vente
	Route::post('/admin/rapport/abort','AdminController@abortRapport');
	//
	Route::post('admin/search/{slug}','AdminController@SearchText');
	// COMMANDES
	Route::get('/admin/commandes','AdminController@allCommandes');
	# PARAMETRAGE DE COMMANDES DE KITS MATERIELS
	Route::post('/admin/commandes/save-kits','AdminController@saveKitRequest');
	
	Route::get('/admin/commandes/livraison','AdminController@getAllLivraison');
	// DASHBOARD DATA
	Route::get('/admin/chart/user-stat','ChartController@userStat');
	Route::get('/admin/chart/command-stat','ChartController@commandStat');
	Route::get('/admin/chart/command-material-stat','ChartController@commandMaterialStat');
	Route::get('/admin/chart/livraison-stat','ChartController@livraisonMaterialStat');
	#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	#DATA PERFORMANCE  ON DASHBOARD
	Route::get('/admin/perform-obj/recrutement','ChartController@getRecrutementStat');
	Route::get('/admin/perform-obj/reabonnement','ChartController@getReabonnementStat');
	Route::get('/admin/perform-obj/migration','ChartController@getMigrationStat');
	Route::post('/admin/perform-obj/filter','ChartController@makeFilter');

	Route::get('/admin/performance/acte-reabonnement','ChartController@getActeReabonnementStat');
	// Route::get('/admin/performance/acte-reabonnement-list','ChartController@getActeReabonnementList');
	// 
	# DATA OBJECTIFS ON DASHBOARD
	Route::get('/admin/objectif/recrutement','ObjectifController@getObjectifRecrutementStat');
	Route::get('/admin/objectif/reabonnement','ObjectifController@getObjectifReabonnementStat');
	Route::post('/admin/objectif/get-details','ObjectifController@getDetails');
	#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	// Promo
	Route::post('/admin/promo/add','AdminController@addPromo');
	Route::get('/admin/promo/list','AdminController@getPromo');
	Route::post('/admin/promo/edit','AdminController@editPromo');
	Route::post('/admin/promo/interrompre','AdminController@interruptionPromo');
	Route::get('/admin/promo/get-remboursement','AdminController@getRemboursementForUsers'); // vue sur les remboursements lies a la promo
	Route::post('/admin/promo/filter-get-remboursement','AdminController@makeFilterRemboursementUsers');
	Route::get('/admin/promo/listing','AdminController@getListingPromo'); // Listing de toutes les promos
	// Depenses
	Route::post('/admin/afrocash/depenses','AdminController@addDepenses');
	//
	Route::get('/admin/afrocash','AdminController@operationAfrocash');
	Route::get('/admin/afrocash/historique-apports','AdminController@historiqueApport');
	Route::get('/admin/afrocash/historique-depenses','AdminController@historiqueDepenses');
	Route::post('/admin/afrocash/apport','AdminController@apportCapital');
	Route::get('/admin/afrocash/all-transactions','CreditController@allTransactionAfrocash');

	// recuperation des soldes vendeurs
	Route::get('/admin/get-soldes','CreditController@getSoldesVendeurs');
	// etat depot Central
	Route::get('/admin/depot-central','AdminController@etatDepotCentral');
	Route::get('/admin/entrepot/all','AdminController@getEtatDepotCentral');

	Route::get('/admin/material/edit/{slug}','AdminController@getDataEditMateriel');

	// PARAMETRES
	Route::get('/admin/settings','Settings@index');
	Route::post('/admin/change-password','Settings@changePassword');
	// AJOUT DE RAPPORT
	Route::get('/admin/add-rapport','AdminController@addRapport');
	Route::post('/admin/send-rapport/{slug}','AdminController@sendRapport');
	Route::post('/admin/rapport/check-serial','AdminController@checkSerial');
	Route::get('/admin/all-rapport','AdminController@listRapport');
	Route::post('/admin/rapport/remove-rapport','AdminController@removeRapport');
	Route::get('/admin/pay-comissions/all','RapportControlleur@payComissionList');
	Route::post('/admin/rapport/list/export','RapportControlleur@exportRapportData');

	// Route::get('/admin/rapport/all','AdminController@getAllRapport');
	Route::get('/admin/rapport/commission-total','AdminController@totalCommission');
	// OPTION DE FORMULES
	// FORMULES
	Route::get('/admin/formule','AdminController@formule');
	Route::post('/admin/formule/add','AdminController@addFormule');
	Route::get('/admin/formule/{slug}/edit','AdminController@getDataEditFormule');

	Route::post('/admin/formule/{slug}/edit','AdminController@editFormule');
	
	Route::post('/admin/option/add','AdminController@addOptionFormule');
	Route::post('/admin/option/{slug}/edit','AdminController@editOption');
	Route::get('/admin/option/{slug}/edit','AdminController@getDataEditOption');
	
	Route::get('/admin/formule/list','AdminController@listFormule');
	// creation des comptes de credit
	Route::get('/admin/add-account-credit','CreditController@addAccount');
	Route::get('admin/get-global-solde','CreditController@getGlobalSolde');
	Route::post('/admin/add-account-credit','CreditController@makeAddAccount');

	// admin routing
	Route::get('/app',function () {
		return view('layouts.template');
		// return view('emails.commande-canal-plus');
	});
// //
	Route::get('/admin/add-user','AdminController@getFormUser');
	Route::post('/admin/add-user','AdminController@addUser');
	Route::post('/admin/add-technicien','AdminController@addTechnicien');
	Route::get('/admin/list-users','AdminController@listUser');
	Route::get('/admin/users/list','AdminController@getListUsers');

	Route::get('/admin/add-depot','LogistiqueController@makeDepot');
	Route::post("/admin/add-depot",'LogistiqueController@addDepot');
	Route::post('/admin/add-material','LogistiqueController@addMaterial');
	//

	//
	Route::post('/admin/add-depot/auto-complete','LogistiqueController@findMaterial');
	// list des materieles
	Route::get('/admin/list-material','LogistiqueController@listMaterial');

	// editer les infos utilisateurs
	
	Route::get('/admin/users/edit/{slug}','AdminController@editUser');
	Route::post('/admin/users/edit-request','AdminController@editUserRequest');
	// bloquer un utilisateur
	Route::post('/admin/block-user','AdminController@blockUser');
	Route::post('/admin/unblock-user','AdminController@unblockUser');
	Route::get('/admin/history-depot','LogistiqueController@historyDepot');
	
	// Reinitialiser un utilisateur
	Route::post('/admin/reset-user','AdminController@resetUser');
	// liste des depots
	Route::get('/admin/list-depot','AdminController@listDepot');
	//
	// INVENTAIRE SUR LE TERRAIN
	Route::get('/admin/inventory','LogistiqueController@inventory');
	Route::get('/admin/inventory/get-serial-number-list','LogistiqueController@getListMaterialByVendeurs');
	Route::get('/admin/inventory/all-material','LogistiqueController@getAllMaterialForVendeurs');

	# AFFECTATION D'UN MATERIEL D'UN DEPOT A UN VENDEUR
	Route::post('/admin/material/affectation/depot-vendeur','AdminController@affectationDepotVendeur');
	# transfert de materiel d'un vendeur a un autre
	Route::post('/admin/inventory/transfert-material','AdminController@transfertMaterialToOtherUser');
	Route::post('/admin/inventory/replace-material-defectuous','AdminController@replaceMaterialDefectuous');
	#transfert materiel d'un depot a un autre
	Route::post('/admin/inventory-depot/transfert-material','AdminController@transfertMaterialToOtherDepot');
	// ##INVENTAIRE DANS LES DEPOTS /user/inventory/depot
	Route::get('/admin/inventory/depot','LogistiqueController@depotList');
	Route::get('/admin/inventory/depot/serialNumber','LogistiqueController@getSerialNumberForDepot');
	
	// Editer les infos materiels
	Route::post('/admin/edit-material/','LogistiqueController@makeEditMaterial');
	//
	#historique de ravitaillement des depots
	Route::get('/admin/depot/historique-depot','LogistiqueController@historiqueRavitaillementDepot');
});

Auth::routes();

Route::middleware(['auth','unblocked'])->group(function () {
	// LISTING POUR LE CHOIX DES TECHNICIENS
	Route::get('/user/technicien-list','TechniqueController@getListTechnicien');
	// 
	Route::get('/user/inventory-stock/filter/{vendeurs}/{status}','LogistiqueController@filterInventoryStock');
	// 

	Route::get('/user/get-all-pdraf','PdrafController@getAllPdraf');
	Route::get('/user/get-all-pdc','PdcController@getAllPdc');
	
	Route::get('/user/reabo-afrocash/get-comission','PdrafController@getComissionToPay');
	Route::get('/user/recrutement-afrocash/get-comission','PdrafController@getComissionToPayRecrutement');
	Route::get('/user/reseaux-afrocash/get-comission','PdrafController@getAfrocashComission');
	######################################  PDC #########################$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
	Route::post('/pdc/command/new','PdcController@sendCommandAfrocash')->middleware('pdc');
	Route::get('/pdc/command/new','PdcController@newCommandGetData');
	Route::get('/pdc/command/list/{slug?}','CommandAfrocashController@commandAfrocashList');

	Route::get('/pdc/command/confirm/{slug}','CommandAfrocashController@getDataConfirmCommand')->middleware('vendeur');
	Route::post('/pdc/command/confirm/{slug}','CommandAfrocashController@confirmCommandAfrocash')->middleware('vendeur');

	Route::get('/pdraf/command/confirm/{slug}','CommandAfrocashController@getDataConfirmCommand')->middleware('pdc');
	// Route::post('/pdraf/command/confirm/{slug}','CommandAfrocashController@confirmCommandAfrocashForPdraf')->middleware('pdc');
	Route::post('/pdraf/command/confirm/{slug}','CommandAfrocashController@confirmCommandAfrocash')->middleware('pdc');

	Route::get('/pdc/material/inventory','CommandAfrocashController@inventoryStock');
	

	Route::post('/pdc/command/remove','CommandAfrocashController@removeCommandAfrocash')->middleware('vendeur');
	Route::post('/pdraf/command/remove','CommandAfrocashController@removeCommandAfrocash')->middleware('pdc');

	Route::post('/user/afrocash/depot-pdc/','PdcController@depotDepot')->middleware('vendeur');
	Route::get('/user/pdc/','PdcController@operation')->middleware('pdc');
	Route::get('/user/get-pdraf-list','PdrafController@getList')->middleware('pdc');
	Route::post('/user/pdc/send-transaction','PdcController@sendTransaction')->middleware('pdc');
	Route::get('/user/pdc/get-solde','PdcController@getSoldePdc')->middleware('pdc');
	Route::get('/user/pdc/get-pdraf-solde','PdcController@getPdrafSoldes')->middleware('pdc');
	Route::post('/user/pdc/send-pdraf-add-request','PdcController@SendPdrafAddRequest')->middleware('pdc');
	Route::get('/user/pdc/get-create-request','PdcController@getCreateRequest')->middleware('pdc');
	Route::post('/user/pdc/pay-comission-request','PdcController@sendPayComissionRequest')->middleware('pdc');

	Route::post('/user/pdraf/confirm-reabo-afrocash','PdrafController@confirmReaboAfrocash')->middleware('cga');
	Route::post('/user/pdraf/confirm-recrutement-afrocash','PdrafController@confirmRecrutementAfrocash')->middleware('cga');

	##############################@@@@@@@@@@@@@ -------------- PDRAF -------------- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@####

	Route::get('/user/pdraf','PdrafController@index')->middleware('pdraf');
	Route::get('/user/pdraf/get-other-users','PdrafController@getOtherUsers')->middleware('pdraf');
	Route::post('/user/pdraf/send-transaction','PdrafController@sendTransaction')->middleware('pdraf');
	Route::get('/user/pdraf/retour-afrocash-infos','PdrafController@infosRetourAfrocash')->middleware('pdraf');
	Route::post('/user/pdraf/send-retour-afrocash','PdrafController@sendRetourAfrocash')->middleware('pdraf');
	Route::get('/user/pdraf/get-solde','PdrafController@getSolde');

	Route::post('/user/pdraf/send-reabo-afrocash','PdrafController@sendReaboAfrocash')->middleware('pdraf');
	Route::post('/user/pdraf/send-recrutement-afrocash','PdrafController@sendRecrutementAfrocash')->middleware('pdraf');

	Route::post('/user/pdraf/send-upgrade-afrocash','PdrafController@sendUpgradeAfrocash')->middleware('pdraf');
	Route::get('/user/pdraf/get-reabo-afrocash','PdrafController@getAllReaboAfrocash');
	Route::get('/user/pdraf/get-recrutement-afrocash','PdrafController@getAllRecrutementAfrocash');
	Route::get('/user/pdraf/get-vente-details/{slug}','PdrafController@getVenteDetails');
	Route::post('/user/pdraf/pay-comission-request','PdrafController@sendPayComissionRequest')->middleware('pdraf');

	Route::get('/user/pdraf/filter-reabo-afrocash/{pdc}/{user}/{payState}/{state}/{margeState}','PdrafController@filterReaboAfrocash');
	Route::get('/user/pdraf/filter-recrutement-afrocash/{pdc}/{user}/{payState}/{state}/{margeState}','PdrafController@filterRecrutementAfrocash');

	Route::post('/user/pdraf/reactivation-materiel','PdrafController@reactivationMaterielRequest')->middleware('pdraf');
	Route::get('/user/pdraf/get-reactivation-materiel','PdrafController@getReactivationList');
	Route::post('/pdraf/command/new','PdcController@sendCommandAfrocash')->middleware('pdraf');

	Route::get('/admin/pay-comission/pdraf/all','PdrafController@getAllPayComission');

	# LISTING DES INSTALLATIONS

	Route::get('/user/technique/interventions','PdrafController@getAllInterventions');
	# VALIDER LE PAIEMENT DE L'INSTALLATION
	Route::post('/user/technique/validate-intervention','PdrafController@validatePaymentIntervention');

	######################################## AFROCASH MONEY TRANSFERT ROUTES ############################

	Route::post('/user/afrocash/retrait','PdrafController@afrocashRetraitRequest');
	Route::post('/user/afrocash/depot','PdrafController@afrocashDepotRequest');

	############################@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@##############################


	
	#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	# ALERTES ABONNEMENTS
	Route::get('/{slug}/alert-abonnement/all','RapportControlleur@getAlertAbonnementForAllUsers');
	Route::get('{slug}/alert-abonnement/count','RapportControlleur@getAlertAbonnementCount');
	
	Route::get('/{slug}/all-vendeurs','LogistiqueController@allVendeurs');
	Route::get('/{slug}/all-produits','LogistiqueController@allProduits');
	// CONSTRUCTION DU TABLEAU DE BORD
	// ENVOI DU FEEDBACK
	Route::post('/user/feedback/send','ToolsController@sendFeedback');
	Route::get('/user/feedback/list','ToolsController@listFeedback');
	// rapport pour controleur
	Route::get('user/add-rapport','RapportControlleur@addRapport')->middleware('controleur');
	Route::post('/user/send-rapport/{slug}','RapportControlleur@sendRapport')->middleware('controleur');
	Route::post('/user/rapport/check-serial','RapportControlleur@checkSerial')->middleware('controleur');
	Route::get('/user/formule/list','AdminController@listFormule');
	
	Route::post('/user/rapport/check-upgrade','RapportControlleur@checkSerialOnUpgradeState');
	
	Route::get('/user/rapport/filter/{type}/{state}/{promo}/{payState}/{user}/{from}/{to}','AdminController@filterRapportRequest');
	Route::get('/user/rapport/all','AdminController@getAllRapport');
	Route::get('user/rapport/commission-total','RapportControlleur@totalCommission');
	#listing promo
	Route::get('/user/promo/listing','AdminController@getListingPromo');
	# CHECK SERIAL FOR GET DEBUT DATE
	Route::post('/user/rapport/check-serial-debut-date','RapportControlleur@checkSerialForGetDebutDate');

	// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

	// voir les details d'un rapport
	Route::post('/user/rapport-ventes/get-details','RapportControlleur@getDetailsForRapport');
	// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	// ravitailler un depot
	Route::get('/user/ravitailler-depot','LogistiqueController@ravitaillerDepot')->middleware('logistique');
	Route::get('/user/depot/historique-depot','LogistiqueController@historiqueRavitaillementDepot')->middleware('logistique');
	
	Route::get('{slug}/logistique/get-materiel','LogistiqueController@getMateriel');
	Route::post('/user/ravitailler-depot','LogistiqueController@sendRavitaillementDepot')->middleware('logistique');
	Route::post('user/ravitailler-depot/get-mat-dispo','LogistiqueController@getMaterialDispo')->middleware('logistique');
// livraison a valider
	Route::post('/user/commandes/livraison-validation','LogistiqueController@getListLivraisonToValidate')->middleware('logistique');
	Route::post("/user/commandes/livraison-validee",'LogistiqueController@getListLivraisonValidee')->middleware('logistique');
	Route::post('/user/commandes/get-serial-validation','LogistiqueController@getSerialForValidateLivraison')->middleware('logistique');
	Route::post('/user/commandes/validate-livraison-serials','LogistiqueController@validateLivraisonSerial')->middleware('logistique');
// ###
	Route::get('/user/add-material/complete-registration','LogistiqueController@completeRegistration')->middleware('logistique');
	Route::post('/user/add-material/complete-registration','LogistiqueController@completRegistrationFinal')->middleware('logistique');

	Route::post('/user/add-material/find-serial-number','LogistiqueController@findSerialNumber')->middleware('logistique');
	// Annuler un enregistrement
	Route::post('/user/add-material/abort-registration','LogistiqueController@abortRegistration')->middleware('logistique');
	// completer l'enregistrement du materiel
	// PROFILE UTILISATEURS
	Route::get('/user/settings','Settings@indexUser');
	Route::get('/user/get-profile-infos','Settings@profileInfos');
	Route::post('/user/change-password','Settings@changePasswordUser');
	
	// ==
	
	Route::get('/user',function () {
		return view('layouts.template');
	});
	// LOGISTIQUE
	// CONFIRMER UNE COMMANDE
	Route::post('/user/confirm-command/{id}','LogistiqueController@confirmCommand')->middleware('logistique');
	// 	LISTE DES COMMANDES
	Route::get('/user/commandes','LogistiqueController@allCommandes')->middleware('logistique');
	Route::get('/user/logistique/afrocash-solde','LogistiqueController@getSoldeLogistique')->middleware('logistique');	//Recuperation du solde afrocash de la logistique
	
	Route::get('/logistique/commandes/livraison','AdminController@getAllLivraison')->middleware('logistique');
	Route::post('/logistique/commandes/abort','AdminController@abortCommandMaterial')->middleware('logistique');
	Route::get('/user/commandes/livraison','AdminController@getAllLivraison');
	Route::get('/user/livraison/filter/{user}/{state}','AdminController@livraisonFilterRequest');
	// === MATERIELS
	Route::get('/user/list-material','LogistiqueController@listMaterial')->middleware('logistique');
	Route::post('/user/list-material','LogistiqueController@getListMaterial')->middleware('logistique');
	Route::post('/user/history-depot','LogistiqueController@getHistoryDepot')->middleware('logistique');
	# recuperation des numeros de SERIES
	Route::post('/user/get-serialNumber','LogistiqueController@ListSerialNumber')->middleware('logistique');
	// RAVITAILLEMENT VENDEURS
	Route::get('/user/ravitailler/{commande}','LogistiqueController@addStock')->middleware('logistique');
	Route::post('/user/ravitailler/{commande}','LogistiqueController@makeAddStock')->middleware('logistique');
	Route::get('/user/ravitailler/validate-test/{commande}','LogistiqueController@commandStateTest')->middleware('logistique');
	#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	Route::get('/logistique/ravitaillement/{slug}/infos','LogistiqueController@getInfosCommande')->middleware('logistique');
	Route::get("/logistique/ravitaillement/list-depot",'LogistiqueController@depotList')->middleware('logistique');
	#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@22


	// COMPLETER LE RAVITAILLEMENT EN SAISISSANT LES NUMEROS DE SERIE
	
	Route::get('/user/inventory/all-material','LogistiqueController@getAllMaterialForVendeurs')->middleware('logistique');
	// INVENTAIRE
	Route::get('/user/inventory','LogistiqueController@inventory')->middleware('logistique');
	Route::get('/user/inventory/get-serial-number-list','LogistiqueController@getListMaterialByVendeurs');
	Route::get('/user/inventory/depot','LogistiqueController@depotList');
	Route::get('/user/inventory/depot/serialNumber','LogistiqueController@getSerialNumberForDepot');

	// VENDEURS

	Route::get('/user/my-inventory','LogistiqueController@inventoryVendeur')->middleware('vendeur');
	Route::get('/user/inventory/all-vendeur-material','LogistiqueController@getAllMaterialByVendeurs')->middleware('vendeur');
	Route::get('/user/inventory/all-credit-vendeurs','CreditController@getCreditForVendeurs');

	Route::get('/user/new-command','CommandController@addCommand')->middleware('vendeur');
	// Route::get('/user/new-command/get-infos-material','CommandController@infoMaterial')->middleware('vendeur');
	Route::get('/user/new-command/get-infos-material/{slug}','CommandController@infoMaterial');//->middleware('vendeur');
	Route::get('/user/new-command/get-data','CommandController@getData');

	// ========= LISTE DES COMMANDES
	Route::get('/user/command/filter/{user}/{state}/{promo}','AdminController@filterRequestCommande');
	Route::get('/user/commandes/all','AdminController@getAllCommandes');//->middleware('vendeur');
	// DETAILS COMMANDES

	Route::get('/user/details-command/{id}','CommandController@DetailsCommand')->middleware('vendeur');
	Route::post('user/details-command/{id}','CommandController@getDetailsCommand')->middleware('vendeur');
	Route::post('user/details-command/{id}/get-livraison','CommandController@getListLivraisonByVendeurs')->middleware('vendeur');

	// ==--
	// ----	ENVOI DES COMMANDES -----
		// 	CREDIT CGA
	Route::post('/user/new-command/cga','CreditController@commandCga')->middleware('vendeur');
	// CREDIT REX
	Route::post('/user/new-command/rex','CreditController@commandRex')->middleware('vendeur');
	// ##
	Route::post('/user/new-command/afrocash-sg','CreditController@sendCommandSemiGrossiste')->middleware('vendeur');
		// MATERIEL
	Route::post('/user/new-command/material','CommandController@sendCommand')->middleware('vendeur');
	// REPERTOIRE CLIENT
	Route::get('/user/add-client','VendeurController@addClient')->middleware('vendeur');
	Route::post('/user/carnet-adresse/add-client','VendeurController@makeAddClient')->middleware('vendeur');
	Route::get('/user/carnet-adresse/list','VendeurController@listClient')->middleware('vendeur');
	
	// GESTIONNAIRE CGA
	Route::get('/user/cga-credit/','CreditController@crediterVendeur')->middleware('cga');
	Route::get('/user/credit-cga/commandes','CreditController@commandCredit')->middleware('cga');

	Route::get('/user/commandes/credit/filter/{state}/{user}','CreditController@filterRequestCommandCredit');
	Route::get('/user/commandes/credit-all','CreditController@getAllCommandes');
	Route::get('/user/get-soldes','CreditController@getSoldesVendeurs')->middleware('cga');
	Route::get('user/get-global-solde','CreditController@getGlobalSolde')->middleware('cga');


	Route::post('/user/send-afrocash','CreditController@sendAfrocash');
	Route::post('/user/credit-cga/abort-commandes','CreditController@abortCommande')->middleware('cga');
	Route::post('/user/cga-credit/','CreditController@getListVendeur')->middleware('cga');

	#### SOLDE VENDEURS  #####

	Route::post('/user/cga-credit','CreditController@getSoldeVendeur')->middleware('cga');
	Route::post("/user/cga-credit/{slug}",'CreditController@searchText')->middleware('cga');
	Route::post('/user/rex-credit','CreditController@getSoldeVendeur')->middleware('rex');
	// GESTIONNAIRE REX
	Route::get('/user/rex-credit','CreditController@crediterVendeur')->middleware('rex');

	Route::get('/user/credit-rex/commandes','CreditController@commandCredit')->middleware('rex');
	Route::post('user/credit-rex/commandes','CreditController@getListCommandGrex')->middleware('rex');
	// TOUTES LES VENTES
	Route::get('/user/ventes','VendeurController@allVentes')->middleware('vendeur');
	// ACTIVER UN ABONNEMENT
	Route::get('/user/abonnement','VendeurController@abonnement')->middleware('vendeur');
	Route::post('/user/abonnement','VendeurController@activerAbonnement')->middleware('vendeur');
	// ++ HISTORIQUE DES RECRUTEMENTS
	Route::post('/user/ventes','VendeurController@allRecrutement')->middleware('vendeur');

	// ####  	GESTIONNAIRE DEPOT 		####
	Route::get('/user/inventaire-depot','LogistiqueController@inventaireDepot')->middleware('depot');
	Route::get('/user/livraison','LogistiqueController@inventaireLivraison')->middleware('depot');
	Route::post("/user/livraison",'LogistiqueController@getListLivraison')->middleware('depot');
	Route::post("/user/livraison-confirmee",'LogistiqueController@getListLivraisonConfirmee')->middleware('depot');
	Route::post('/user/livraison/confirm','LogistiqueController@confirmLivraison')->middleware('depot');
	Route::post('/user/inventaire-depot/get-list','LogistiqueController@getInventaireForDepot')->middleware('depot');
	Route::post('/user/livraions/with-serial','LogistiqueController@itemWithSerial')->middleware('depot');
	Route::post('/user/livraison/validate-serial','LogistiqueController@validateSerialNumberForLivraison')->middleware('depot');
	// AFROCASH

	Route::get('/user/afrocash','CreditController@afrocashOperation')->middleware('vendeur');
	Route::get('/user/afrocash/get-account-list','CreditController@getAccountList')->middleware('vendeur');
	Route::post('/user/afrocash/transaction','CreditController@sendDepot')->middleware("vendeur");

	Route::get('/user/afrocash/transactions','CreditController@allTransactionAfrocash')->middleware('cga');
	Route::get('/user/afrocash/all-transactions','CreditController@allTransactionAfrocashVendeur')->middleware('vendeur');
	
	Route::get('/{slug}/afrocash/get-transactions','CreditController@getAllTransaction');
	Route::post('/user/afrocash/filter-transactions','CreditController@filterTransactionAfrocash')->middleware('vendeur');

	// RECOUVREMENTS
	Route::get('user/recouvrement','RecouvrementController@operations')->middleware('coursier');
	Route::post('/user/recouvrement/add','RecouvrementController@addRecouvrement')->middleware('coursier');
	Route::get('/user/recouvrement/all-transactions','RecouvrementController@allTransactions');
	Route::get('/user/recouvrement/get-montant-du/{vendeur}','RecouvrementController@getMontantDuRecouvrement')->middleware('coursier');
	Route::get('/user/recouvrement/all-recouvrement','RecouvrementController@allRecouvrement');

	// ENVOI DE LA DEMANDE DE PAIEMENT DE COMMISSION
	Route::post('/user/rapport-ventes/pay-commission','RapportControlleur@payCommission')->middleware('vendeur');
	Route::get('/user/rapport-ventes/get-pay-commission','RapportControlleur@PayCommissionListForVendeurs')->middleware('vendeur');

	Route::post('/user/rapport-ventes/validate-pay-commission','RapportControlleur@validatePayComission')->middleware('cga');
	Route::post('/user/rapport-ventes/abort-pay-commission','RapportControlleur@abortPayComission')->middleware('cga');
	
	Route::get('/user/pay-comissions/all','RapportControlleur@payComissionList')->middleware('cga');
	// NOTIFICATIONS
	Route::get('/user/notification/getlist','NotificationController@getList');
	Route::post('/user/notification/mark-as-read','NotificationController@markAsRead');
	// CHAT SERVICES
	Route::get('/user/chat-service/users-list','ChatController@UserList');
	// RECHERCHE INFORMATIONS  DE SERIAL NUMBER
	Route::post('/user/search/serial','SearchController@finSerialNumber');
	// SEND DEBLOCAGE FORM
	Route::post('/user/tools/deblocage-cga','ToolsController@deblocageCga');
	Route::get('/user/deblocage/get-infos','ToolsController@getInfos');
	Route::get('/user/deblocage/list','ToolsController@getDeblocageList');
	Route::post('/user/deblocage/confirm-state','ToolsController@ConfirmStateDeblocage');
	
	// SEND ANNULATION DE SAISIE FORM
	Route::post('/user/tools/annulation-saisie','ToolsController@annulationSaisi');
	// 
	Route::get('/user/promo/list','AdminController@getPromo');
	
	// Infos compense Promo
	Route::get('/user/promo/infos-compense','VendeurController@getInfosRemboursementPromo')->middleware('vendeur');
	Route::get('/user/promo/listing-remboursement','VendeurController@getRemboursementListing')->middleware('vendeur');
	Route::get('/user/promo/infos-remboursement','VendeurController@infosRemboursement')->middleware('vendeur');
	Route::post('/user/promo/send-compense-promo','VendeurController@sendCompensePromo')->middleware('vendeur');
	// ON RECUPERE LES DONNEES POUR LES GRAPHIQUES DE PERFORMANCES ET DES OBJECTIFS
	Route::get('/user/chart/performances/recrutement','ChartController@performanceVendeurRecrutement')->middleware('vendeur');
	Route::get('/user/chart/performances/reabonnement','ChartController@performanceVendeurReabonnement')->middleware('vendeur');
	Route::get('/user/chart/objectif/recrutement','ObjectifController@statForVendeursRecrutement')->middleware('vendeur');
	Route::post('/user/chart/performances/filter-by-date','ChartController@makeFilter')->middleware('vendeur');
	// 
	# get Bonus objectif
	Route::get('/user/objectif/get-bonus-objectif','ObjectifController@getBonusObjectif')->middleware('vendeur');
	# confirmation du paiement du bonus des objectif 
	Route::post('/user/objectif/pay-bonus-objectif','ObjectifController@payBonusObjectif')->middleware('vendeur');
	#
	Route::get('/user/chart/command-stat','ChartController@commandStat')->middleware('cga');
	Route::get('/user/chart/command-material-stat','ChartController@commandMaterialStat')->middleware('logistique');
	Route::get('/user/chart/livraison-stat','ChartController@livraisonMaterialStat')->middleware('logistique');

});

Route::middleware(['auth'])->group(function () {
	Route::get('/no-permission','UtilisateurSimple@noPermission');
});
