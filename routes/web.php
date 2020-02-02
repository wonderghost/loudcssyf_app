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
	Route::post('admin/search/{slug}','AdminController@SearchText');
	// COMMANDES
	Route::get('/admin/commandes','AdminController@allCommandes');
	Route::post('/admin/commandes/all','AdminController@getAllCommandes');
	Route::post('/admin/commandes/credit-all','CreditController@getAllCommandes');
	// DASHBOARD DATA
	Route::post('/admin/dashboard/user-data','AdminController@dataForUserChart');
	Route::post('/admin/dashboard/depot-data','AdminController@dataForDepotChart');
	// Promo
	Route::post('/admin/promo/add','AdminController@addPromo');
	Route::post('/admin/promo/list','AdminController@getPromo');
	Route::post('/admin/promo/edit','AdminController@editPromo');
	Route::post('/admin/promo/interrompre','AdminController@interruptionPromo');
	// Depenses
	Route::post('/admin/depenses/add','AdminController@addDepenses');
	//
	Route::get('/admin/afrocash','AdminController@operationAfrocash');
	Route::post('/admin/afrocash/apport','AdminController@apportCapital');
	Route::get('/admin/afrocash/all-transactions','CreditController@allTransactionAfrocash');
	// recuperation des soldes vendeurs
	Route::post('/admin/get-soldes','CreditController@getSoldesVendeurs');
	// etat depot Central
	Route::get('/admin/depot-central','AdminController@etatDepotCentral');
	Route::post('/admin/depot-central','AdminController@getEtatDepotCentral');
	// PARAMETRES
	Route::get('/admin/settings','Settings@index');
	Route::post('/admin/change-password','Settings@changePassword');
	// AJOUT DE RAPPORT
	Route::get('/admin/add-rapport','AdminController@addRapport');
	Route::post('/admin/send-rapport/{slug}','AdminController@sendRapport');
	Route::post('/admin/rapport/check-serial','AdminController@checkSerial');
	Route::get('/admin/all-rapport','AdminController@listRapport');
	Route::post('/admin/rapport/remove-rapport','AdminController@removeRapport');

	//
	Route::get('/admin/rapport/list-reabonnement','AdminController@reabonnementRapport'); // recuperation de l'historique des rapports
	Route::get('/admin/rapport/list-recrutement','AdminController@recrutementRapport'); // recuperation de l'historique des rapports
	Route::get('/admin/rapport/list-migration','AdminController@migrationRapport'); // recuperation de l'historique des rapports
	Route::get('/admin/rapport/commission-total','AdminController@totalCommission');
	// OPTION DE FORMULES
	Route::post('/admin/add-option','AdminController@addOptionFormule');
	// FORMULES
	Route::get('/admin/formule','AdminController@formule');
	Route::post('/admin/formule','AdminController@addFormule');
	// creation des comptes de credit
	Route::get('/admin/add-account-credit','CreditController@addAccount');
	Route::get('admin/get-global-solde','CreditController@getGlobalSolde');
	Route::post('/admin/add-account-credit','CreditController@makeAddAccount');

	// admin routing
	Route::get('/admin','AdminController@dashboard');
	Route::post('/admin/transaction-for-dashboard','AdminController@getTransactionForDashboardView');
// //
	Route::get('/admin/add-user','AdminController@getFormUser');
	Route::post('/admin/add-user','AdminController@addUser');
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
	Route::post('/admin/list-material','LogistiqueController@getListMaterial');
	Route::get('/admin/get-serialNumber','LogistiqueController@ListSerialNumber');
	// editer les infos utilisateurs
	Route::get('/admin/edit-users/{username}','AdminController@editUser');
	Route::post('/admin/edit-users/{username}','AdminController@makeEditUser');
	// bloquer un utilisateur
	Route::post('/admin/block-user','AdminController@blockUser');
	Route::post('/admin/unblock-user','AdminController@unblockUser');
	Route::get('/admin/history-depot','LogistiqueController@historyDepot');
	Route::post('/admin/history-depot','LogistiqueController@getHistoryDepot');
	// Reinitialiser un utilisateur
	Route::post('/admin/reset-user','AdminController@resetUser');
	// liste des depots
	Route::get('/admin/list-depot','AdminController@listDepot');
	//
	Route::get('/admin/inventory','LogistiqueController@inventory');
	Route::post('/admin/inventory','LogistiqueController@getListMaterialByVendeurs');
	// Editer les infos materiels
	Route::get('/admin/edit-material/{reference}','LogistiqueController@editMaterial');
	Route::post('/admin/edit-material/{reference}','LogistiqueController@makeEditMaterial');
	//

});

Auth::routes();

Route::middleware(['auth','unblocked'])->group(function () {
	// CONSTRUCTION DU TABLEAU DE BORD
	Route::post('/user/dashboard/chart-vente','VendeurController@statistiqueVente');
	// rapport pour controleur
	Route::get('user/add-rapport','RapportControlleur@addRapport')->middleware('controleur');
	Route::post('/user/send-rapport/{slug}','RapportControlleur@sendRapport')->middleware('controleur');
	Route::post('/user/rapport/check-serial','RapportControlleur@checkSerial')->middleware('controleur');
	Route::get('/user/all-rapport','RapportControlleur@listRapport')->middleware('controleur');
	Route::get('/user/rapport/reabonnement-rapport','RapportControlleur@reabonnementRapport')->middleware('controleur');// recuperation de l'historique des rapports
	Route::get('/user/rapport/recrutement-rapport','RapportControlleur@recrutementRapport')->middleware('controleur');// recuperation de l'historique des rapports
	Route::get('/user/rapport/migration-rapport','RapportControlleur@migrationRapport')->middleware('controleur');// recuperation de l'historique des rapports
	Route::get('user/rapport/commission-total','RapportControlleur@totalCommission')->middleware('controleur');
	// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	Route::get('/user/rapport-ventes','RapportControlleur@getRapportByVendeurs')->middleware("vendeur");
	Route::get('/user/rapport/list-reabonnement','RapportControlleur@reabonnementList')->middleware('vendeur');
	Route::get('/user/rapport/list-recrutement','RapportControlleur@recrutementList')->middleware('vendeur');
	Route::get('/user/rapport/list-migration','RapportControlleur@migrationList')->middleware('vendeur');
	Route::get("/user/rapport/total-commission",'RapportControlleur@totalCommissionVendeur')->middleware('vendeur');
	// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	// ravitailler un depot
	Route::get('/user/ravitailler-depot','LogistiqueController@ravitaillerDepot')->middleware('logistique');
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
	Route::post('/user/change-password','Settings@changePasswordUser');
	// Route::get('/user/profile/','')
	// ==
	Route::get('/user','UtilisateurSimple@dashboard');
	// LOGISTIQUE
	// CONFIRMER UNE COMMANDE
	Route::post('/user/confirm-command/{id}','LogistiqueController@confirmCommand')->middleware('logistique');
	// 	LISTE DES COMMANDES
	Route::get('/user/commandes','LogistiqueController@allCommandes')->middleware('logistique');
	Route::post('/user/commandes','LogistiqueController@getAllCommandes')->middleware('logistique');
	// === MATERIELS
	Route::get('/user/list-material','LogistiqueController@listMaterial')->middleware('logistique');
	Route::post('/user/list-material','LogistiqueController@getListMaterial')->middleware('logistique');
	Route::get('/user/history-depot','LogistiqueController@historyDepot')->middleware('logistique');
	Route::post('/user/history-depot','LogistiqueController@getHistoryDepot')->middleware('logistique');
	# recuperation des numeros de SERIES
	Route::post('/user/get-serialNumber','LogistiqueController@ListSerialNumber')->middleware('logistique');
	// RAVITAILLEMENT VENDEURS
	Route::get('/user/ravitailler/{commande}','LogistiqueController@addStock')->middleware('logistique');
	Route::post('/user/ravitailler/{commande}','LogistiqueController@makeAddStock')->middleware('logistique');

	Route::post('/user/get-by-depot','LogistiqueController@getMaterialByDepot')->middleware('logistique');
	Route::post('/user/parabole-du','LogistiqueController@getParaboleDu')->middleware('logistique');
	Route::post('/user/reste-pour-ravitaillement','LogistiqueController@getRestantPourRavitaillement')->middleware('logistique');
	// COMPLETER LE RAVITAILLEMENT EN SAISISSANT LES NUMEROS DE SERIE
	Route::get('/user/ravitailler/{commande}/complete-transfert','LogistiqueController@completeTransfert')->middleware('logistique');
	Route::post('/user/ravitailler/{commande}/complete-transfert','LogistiqueController@completeTransfertFinal')->middleware('logistique');

	Route::post('/user/complete-transfert/find-serial-number','LogistiqueController@getSerialNumber')->middleware('logistique');
	Route::post('/user/ravitailler/{commande}/abort-transfert','LogistiqueController@abortTransfert')->middleware('logistique');
	// INVENTAIRE
	Route::get('/user/inventory','LogistiqueController@inventory')->middleware('logistique');
	Route::post('/user/inventory','LogistiqueController@getListMaterialByVendeurs')->middleware('logistique');

	// VENDEURS
	Route::get('/user/my-inventory','LogistiqueController@inventoryVendeur')->middleware('vendeur');
	Route::post('/user/my-inventory/serials','VendeurController@SerialForVendeur')->middleware('vendeur');
	Route::post('/user/my-inventory','LogistiqueController@getListMaterialByVendeurs');
	Route::post('/user/my-history-ravitaillement','LogistiqueController@getHistoryRavitaillement')->middleware('vendeur');

	Route::get('/user/my-history-ravitaillement','LogistiqueController@historyRavitaillement')->middleware('vendeur');

	Route::get('/user/new-command','CommandController@addCommand')->middleware('vendeur');
	// ========= LISTE DES COMMANDES
	Route::get('/user/list-command','CommandController@getList')->middleware('vendeur');
	Route::post('/user/list-command','CommandController@getRequestList')->middleware('vendeur');
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
	Route::post('/user/add-client','VendeurController@makeAddClient')->middleware('vendeur');
	Route::get('/user/list-client/','VendeurController@listClient')->middleware('vendeur');
	Route::post('/user/get-client/','VendeurController@getListClient')->middleware('vendeur');
	// GESTIONNAIRE CGA
	Route::get('/user/cga-credit/','CreditController@crediterVendeur')->middleware('cga');
	Route::get('/user/credit-cga/commandes','CreditController@commandCredit')->middleware('cga');
	Route::post('user/credit-cga/commandes','CreditController@getListCommandGcga')->middleware('cga');
	Route::post('/user/send-cga','CreditController@sendCga')->middleware('cga');
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
	Route::post('/user/afrocash/transaction','CreditController@sendDepot')->middleware("vendeur");
	Route::get('/user/afrocash/transactions','CreditController@allTransactionAfrocash')->middleware('cga');
	Route::get('/user/afrocash/all-transactions','CreditController@allTransactionAfrocashVendeur')->middleware('vendeur');
	Route::post("/user/afrocash/all-transactions",'CreditController@getAllTransactionAfrocashVendeur')->middleware('vendeur');
	Route::post('/user/afrocash/filter-transactions','CreditController@filterTransactionAfrocash')->middleware('vendeur');
	// RECOUVREMENTS
	Route::get('user/recouvrement','RecouvrementController@operations')->middleware('coursier');
	Route::post('/user/recouvrement/add','RecouvrementController@addRecouvrement')->middleware('coursier');
	Route::post('/user/recouvrement/all-transactions','RecouvrementController@allTransactions')->middleware('coursier');
	Route::post('/user/recouvrement/get-montant-du','RecouvrementController@getMontantDuRecouvrement')->middleware('coursier');
	Route::post('/user/recouvrement/all-recouvrement','RecouvrementController@allRecouvrement')->middleware('coursier');

	// ENVOI DE LA DEMANDE DE PAIEMENT DE COMMISSION
	Route::post('/user/rapport-ventes/pay-commission','RapportControlleur@payCommission')->middleware('vendeur');
	Route::post('/user/rapport-ventes/get-pay-commission','RapportControlleur@PayCommissionList');
	Route::post('/user/rapport-ventes/validate-pay-commission','RapportControlleur@validatePayComission')->middleware('cga');
	// NOTIFICATIONS
	Route::post('/user/notification/getlist','NotificationController@getList');
	Route::post('/user/notification/mark-as-read','NotificationController@markAsRead');
	// CHAT SERVICES
	Route::get('/user/chat-service/users-list','ChatController@UserList');
});

Route::middleware(['auth'])->group(function () {
	Route::get('/no-permission','UtilisateurSimple@noPermission');
});
