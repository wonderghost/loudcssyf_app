import Vue from 'vue'
import VueRouter from 'vue-router'
import AfrocashOperation from './pdcComponents/AfrocashOperation.vue'
import PdcTransaction from './pdcComponents/PdcTransaction.vue'
import InventairePdraf from './pdcComponents/InventairePdraf.vue'
import AddPdraf from './pdcComponents/AddPdraf.vue'
import ListPdraf from './pdcComponents/ListPdraf.vue'
import CreatePdraf from './pdcComponents/CreatePdraf.vue'

// PDRAF IMPORT
import TransfertCourant from './pdrafComponents/TransfertCourant.vue'
import PdrafTransaction from './pdrafComponents/PdrafTransaction.vue'
import RetourAfrocash from './pdrafComponents/RetourAfrocash.vue'
import ReaboAfrocash from './pdrafComponents/ReaboAfrocash.vue'
import AllVenteReabo from './pdrafComponents/AllVenteReaboAfrocash.vue'
import upgradeAfrocash from './pdrafComponents/UpgradeAfrocash.vue'
import reactivationMateriel from './pdrafComponents/ReactivationComponent.vue'
import commandAfrocash from './pdcComponents/CommandAfrocash.vue'
import commandAfrocashList from './pdcComponents/CommandAfrocashList.vue'
import confirmCommandAfrocash from './pdcComponents/ConfirmCommandAfrocash.vue'
import inventoryAfrocashMateriel from './pdcComponents/InventoryAfrocashComponent.vue'
import InterventionTechnique from './pdrafComponents/InterventionComponent.vue'
// 

// ADMIN ROUTER
import addUser from './adminComponents/AddUserComponent.vue'
import listUser from './adminComponents/UserComponent.vue'
import dashboard from './adminComponents/DashboardComponent.vue'
import creationPdraf from './adminComponents/CreationPdraf.vue'
import performance from './adminComponents/PerformObjectifComponent.vue'
import objectifs from './adminComponents/VisualObjectifComponent.vue'
import newObjectif from './adminComponents/ObjectifComponent.vue'
import allObjectif from './adminComponents/AllObjectifComponents.vue'
import promo from './adminComponents/PromoComponent.vue'
import account from './adminComponents/AccountComponent.vue'
import afrocashOperationAdmin from './adminComponents/AfrocashCentral.vue'
import transactionAfrocash from './userComponents/TransactionAfrocashComponent.vue'
import recouvrement from './userComponents/RecouvrementComponent.vue'
import entrepot from './adminComponents/EntrepotComponent.vue'
import editProduit from './adminComponents/EditProduit.vue'

import setRapportParametre from './adminComponents/SetRapportParametre.vue'

import allMaterial from './userComponents/InventoryDepotComponent.vue'
import inventaireReseaux from './userComponents/InventoryComponent.vue'
import commande from './adminComponents/CommandComponent.vue'
import addRapport from './adminComponents/AddRapportComponent.vue'
import listRapport from './userComponents/RapportComponent.vue'
import formule from './adminComponents/FormuleComponent.vue'
import editUser from './adminComponents/EditUserComponent.vue'
import payComission from './userComponents/PayComissionComponent.vue'
import affectationMateriel from './adminComponents/AffectationMaterielComponent.vue'
import affectationDepotVendeur from './adminComponents/AffectationDepotVendeur.vue'
import affectationPdrafPdc from './adminComponents/AffectationPdrafComponent.vue'
import acteReabonnement from './adminComponents/ActeReabonnement.vue'
import editFormuleOption from './adminComponents/EditFormuleOptionComponent.vue'

import commandSettings from './adminComponents/CommandSetting.vue'
import exportRapportData from './adminComponents/ExportRapportDataComponent.vue'

// VENDEURS
import objectifUser from './userComponents/ObjectifUserComponent.vue'
import addContact from './userComponents/AddContactComponent.vue'
import listContact from './userComponents/ContactComponent.vue'
import afrocashOperationUser from './userComponents/AfrocashComponent.vue'
import newCommand from './userComponents/NewCommandComponent.vue'
import profile from './userComponents/SettingComponent.vue'
import Recrutement from './userComponents/Recrutement.vue'
import Reabonnement from './userComponents/Reabonnement.vue'
import Upgrade from './userComponents/Upgrade.vue'
import historiqueVente from './userComponents/HistoriqueVente.vue'
// 

// LOGISTIQUE
import ravitaillerDepot from './adminComponents/RavitaillementDepotComponent.vue'
import ravitaillerVendeur from './userComponents/RavitaillementComponent.vue'
// 

// GESTIONNAIRE CREDIT
import commandCredit from './adminComponents/CreditComponent.vue'
// 
// GESTIONNAIRE DEPOT
import livraisonMateriel from './userComponents/LivraisonComponent.vue'
import store from './store.js'


import alertAbonnement from './userComponents/AlertComponent.vue'
import { mapActions } from 'vuex'

import RetraitAfrocash from './afrocashcomponents/RetraitComponent.vue'
import DepotAfrocash from './afrocashcomponents/DepotComponent.vue'
import HistoriqueRetraitAfrocash from './afrocashcomponents/HistoriqueRetraitComponent.vue'
import HistoriqueDepotAfrocash from './afrocashcomponents/HistoriqueDepotComponent.vue'
import InventaireStockPdraf from './pdcComponents/inventaireStockPdraf.vue'
import InventaireStockAfrocash from './afrocashcomponents/InventaireStockAfrocash.vue'
import InstallationTechnicien from './userComponents/InstallationTechnicien.vue'
import DetailReaboAfrocash from './pdrafComponents/DetailReaboAfrocash.vue'
import RecrutementEasyTv from './userComponents/RecrutementEasy.vue'
import MigrationGratuite from './adminComponents/MigrationGratuite.vue'

import Map from './adminComponents/MapComponent.vue'
import RappEasy from './adminComponents/RapportEasy.vue'
import AfrocashRetour from './afrocashcomponents/RetourAfrocash.vue'
import Migration from './userComponents/Migration.vue'
import RetourMateriel from './adminComponents/RetourMateriel.vue'
import VenteGrandCompte from './adminComponents/VenteGrandCompte.vue'

Vue.use(VueRouter);

const router = new VueRouter({
    mode : 'hash',
    routes:[
        {
            path : '*',
            redirect : '/dashboard',
        },
        // GESTION DES UTILISATEURS
        {
            path : '/user/add',
            component : addUser,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin') {
                    alert("action non autorise !")
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/user/list',
            component : listUser,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'commercial') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/pdraf/list',
            component : creationPdraf,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'commercial') {
                    alert("action non autorise !")
                    next('/')
                }
                next()
            }

        },
        // DASHBOARD
        {
            path : '/dashboard',
            component : dashboard
        },
        {
            path : '/performances',
            component : performance,
            beforeEnter : (to,from,next) => {
                if(
                    store.state.typeUser != 'admin' &&
                    store.state.typeUser != 'commercial' &&
                    store.state.typeUser != 'v_da' &&
                    store.state.typeUser != 'v_standart'    
                ) {
                    alert('action non autorisee !')
                    next('/')
                }

                next()
            }
        },
        {
            path : '/set-rapport-parametre',
            component : setRapportParametre,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                }
                next()
            }
        },
        {
            path : '/objectifs/visu',
            component : objectifs
        },
        {
            path : '/objectifs/new',
            component : newObjectif
        },
        {
            path : "/objectifs/all",
            component : allObjectif
        },
        // PROMO 
        {
            path : '/promo',
            component : promo,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'commercial' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        //compte credit
        {
            path : '/account',
            component : account,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'commercial' && store.state.typeUser != 'gcga') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        //AFROCASH ADMIN
        {
            path : '/afrocash/operation',
            component : afrocashOperationAdmin,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action  non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/afrocash/all-transaction',
            component : transactionAfrocash
        },
        {
            path : '/afrocash/recouvrement',
            component : recouvrement,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'coursier') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : "/material/entrepot",
            component : entrepot,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                }
                next()
            }
        },
        {
            path : '/material/edit/:id',
            component : editProduit,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                }
                next()
            }
        },
        {
            path : '/material/all-material',
            component : allMaterial,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'gdepot' && store.state.typeUser != 'logistique') {
                    alert('action non autorisee !')
                    next('/')
                }
                next()
            }
        },
        {
            path : '/inventory',
            component : inventaireReseaux
        },
        //COMMANDES
        {
            path : '/commandes',
            component : commande,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart' && store.state.typeUser != 'logistique' &&
                  store.state.typeUser != 'commercial') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
            
        },
        // RAPPORT DE VENTE
        {
            path : '/rapport/add',
            component : addRapport,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'controleur') {
                    alert('action non autorise')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/rapport/list/export',
            component : exportRapportData,
            beforeEnter: (to , from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                }
                next()
            }
        },
        {
            path : '/rapport/list',
            component : listRapport
        },
        // USER DASHBOARD 
        {
            path : '/objectifs-user',
            component : objectifUser
        },
        {
            path:"/user/pdc/",
            redirect : '/operation'
        },
        {
            path : '/operation',
            component : AfrocashOperation
        },
        {
            path : '/transactions',
            component : PdcTransaction
        },
        {
            path : '/inventaire-pdraf',
            component : InventairePdraf
        },
        {
            path : '/user/pdc/*',
            redirect : '/user/pdc/operation'
        },
        {
            path : '/add-pdraf',
            component : AddPdraf
        },
        {
            path : '/all-users',
            component : ListPdraf
        },
        {
            path : '/all-make-pdraf',
            component : CreatePdraf
        },
        {
            path : '/all-ventes-pdraf',
            component: AllVenteReabo
        }
        // PDRAF ROUTERS
        ,
        {
            path : '/transfert-courant',
            component : TransfertCourant
        },
        {
            path : "/all-transaction-pdraf",
            component : PdrafTransaction
        },
        {
            path : '/retour-afrocash',
            component : RetourAfrocash,
            beforeEach : (to,from,next) => {
                if(store.state.typeUser != 'pdraf') {
                    alert('action non autorisee !')
                    next('/')
                }
                next()
            }
        },
        {
            path : '/reabonnement-afrocash',
            component : ReaboAfrocash,
            beforeEach : (to,from,next) => {
                if(store.state.typeUser != 'pdraf') {
                    alert('action non autorisee !')
                    next('/')
                }
                next()
            }
        },
        {
            path : '/recrutement-afrocash',
            component : ReaboAfrocash,
            beforeEach : (to,from,next) => {
                if(store.state.typeUser != 'pdraf') {
                    alert('action non autorisee !')
                    next('/')
                }
                next()
            }
        },
        {
            path : '/all-ventes-pdraf',
            component : AllVenteReabo
        },
        {
            path : "/all-ventes-pdraf/recrutement-afrocash",
            component : AllVenteReabo
        },
        // REPERTOIRE DE CONTACT
        {
            path : '/client/add',
            component : addContact
        },
        {
            path : '/client/list',
            component : listContact
        },
        {
            path : '/afrocash/user-operation',
            component : afrocashOperationUser
        },
        {
            path : '/afrocash/transactions',
            component : transactionAfrocash
        },
        // NEW COMMANDE 
        {
            path : '/command/new',
            component : newCommand,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/command/list',
            component : commande,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart' && store.state.typeUser != 'logistique') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        // PARAMETERS
        {
            path : '/setting/profile',
            component : profile
        },
        {
            path : '/setting/formule',
            component : formule,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/depot/ravitailler',
            component : ravitaillerDepot,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'logistique') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/ravitailler/vendeur/:id',
            component : ravitaillerVendeur,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'logistique') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/commande-credit/all',
            component : commandCredit,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'gcga' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart' &&
                    store.state.typeUser != 'commercial') {
                        alert('action non autorise !')
                        next('/')
                }
                else {
                    next()
                }
            }
        },  
        {
            path : '/livraison/all',
            component : livraisonMateriel,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'gdepot' && store.state.typeUser != 'v_da' && store.state.typeUser != 'admin' 
                    && store.state.typeUser != 'logistique' && store.state.typeUser != 'v_standart') {
                        alert('action non autorise !')
                        next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/user/edit/:id',
            component : editUser,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/pay-comission',
            component : payComission,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'gcga') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/alertes/abonnement',
            component : alertAbonnement,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'commercial' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/material/affectation',
            component : affectationMateriel,
            beforeEnter : (to,from ,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/material/affectation/depot-vendeur',
            component : affectationDepotVendeur,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorise !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/reseaux/afrocash',
            component : affectationPdrafPdc,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorise!')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/performances/acte-reabonnement',
            component : acteReabonnement,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/upgrade-afrocash',
            component : upgradeAfrocash,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdraf') {
                    alert('action non autorisee !')       
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/reactivation-materiel',
            component : reactivationMateriel,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdraf') {
                    alert('action non autorisee!')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/setting/formule/:id/edit',
            component : editFormuleOption,
            props : {
                type : "formule"
            },
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/setting/option/:id/edit',
            component : editFormuleOption,
            props : {
                type : 'option'
            },
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                }
                else {
                    next()
                }
            }
        },
        {
            path : '/setting/command/',
            component : commandSettings,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/pdc/command/new',
            component : commandAfrocash,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdc') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/pdraf/command/new',
            component : commandAfrocash,
            beforeEnter : (to, from , next) => {
                if(store.state.typeUser != 'pdraf') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/pdc/command/list/:state',
            component : commandAfrocashList,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdc' && store.state.typeUser != 'admin' && store.state.typeUser != 'v_standart' && store.state.typeUser != 'pdraf') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            },
            props : true
        },
        {
            path : '/pdc/command/confirmation/:id',
            component : confirmCommandAfrocash,
            beforeEnter : (to,from,next)    =>  {
                if(store.state.typeUser != 'v_standart') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/pdraf/command/confirmation/:id',
            component : confirmCommandAfrocash,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdc') {
                    alert('action non autorisee!')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/pdc/material/inventory',
            component : inventoryAfrocashMateriel,
            beforeEnter : (to,from, next) => {
                if(store.state.typeUser != 'pdc') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/pdraf/materiel/inventory',
            component : inventoryAfrocashMateriel,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdraf') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/technique/installation',
            component : InterventionTechnique,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdraf') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            } 
        },
        // AFROCASH TRANSACTION ROUTING
        {
            path : '/afrocash/retrait',
            component : RetraitAfrocash,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdraf' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/afrocash/depot',
            component : DepotAfrocash,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdraf' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/afrocash/historique/retrait',
            component : HistoriqueRetraitAfrocash,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdraf' && store.state.typeUser != 'pdc' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart' && store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/afrocash/historique/depot',
            component : HistoriqueDepotAfrocash,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdraf' && store.state.typeUser != 'pdc' && store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart' && store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/materiel/inventory/pdraf',
            component : InventaireStockPdraf,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'pdc') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/afrocash/inventaire',
            component : InventaireStockAfrocash,
            beforeEnter : (to,from ,next) => {
                if(store.state.typeUser != 'admin') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/technicien/installation',
            component : InstallationTechnicien,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'v_da' && store.state.typeUser != 'v_standart') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/vente-afrocash/:id',
            component : DetailReaboAfrocash,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'admin' && store.state.typeUser != 'gcga' && store.state.typeUser != 'commercial') {
                    alert('action non autorisee !')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/vente/recrutement',
            component : Recrutement,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'v_standart') {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/vente/reabonnement',
            component : Reabonnement,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'v_standart') {
                    alert('action non autorisee.')
                    next('/')                    
                    return 0
                }
                next()
            }
        },
        {
            path : "/vente/upgrade",
            component : Upgrade,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser != 'v_standart') {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : "/ventes",
            component : historiqueVente,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser !== 'v_standart' && store.state.typeUser !== 'admin') {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/migration-gratuite',
            component : MigrationGratuite,
            beforeEnter : (to,from , next) => {
                if(store.state.typeUser != 'logistique') {
                    alert('action non autorisee')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/vente/recrutement-easytv',
            component : RecrutementEasyTv,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser !== 'v_standart')
                {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/user/maps',
            component : Map,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser !== 'admin') {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/rapport/easy/add',
            component : RappEasy,
            beforeEnter : (to,from ,next) => {
                if(store.state.typeUser !== 'admin')
                {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/afrocash/retour-afrocash',
            component : AfrocashRetour,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser !== 'v_da')
                {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/vente/migration',
            component : Migration,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser !== 'v_standart')
                {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/material/retour-materiel',
            component : RetourMateriel,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser !== 'admin')
                {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        },
        {
            path : '/vente-grand-compte',
            component : VenteGrandCompte,
            beforeEnter : (to,from,next) => {
                if(store.state.typeUser !== 'admin')
                {
                    alert('action non autorisee.')
                    next('/')
                    return 0
                }
                next()
            }
        }
    ]
})

router.afterEach( (to,from) => {
    if(UIkit.offcanvas($("#side-nav"))) {

        UIkit.offcanvas($("#side-nav")).hide();
    }
})

export default router