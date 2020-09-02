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
import allMaterial from './userComponents/InventoryDepotComponent.vue'
import inventaireReseaux from './userComponents/InventoryComponent.vue'
import commande from './adminComponents/CommandComponent.vue'
import addRapport from './adminComponents/AddRapportComponent.vue'
import listRapport from './userComponents/RapportComponent.vue'
import formule from './adminComponents/FormuleComponent.vue'
import editUser from './adminComponents/EditUserComponent.vue'
import payComission from './userComponents/PayComissionComponent.vue'
// VENDEURS
import objectifUser from './userComponents/ObjectifUserComponent.vue'
import addContact from './userComponents/AddContactComponent.vue'
import listContact from './userComponents/ContactComponent.vue'
import afrocashOperationUser from './userComponents/AfrocashComponent.vue'
import newCommand from './userComponents/NewCommandComponent.vue'
import profile from './userComponents/SettingComponent.vue'
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

Vue.use(VueRouter);

const router = new VueRouter({
    mode : 'hash',
    routes:[
        {
            path : '*',
            redirect : '/dashboard'
        },
        // GESTION DES UTILISATEURS
        {
            path : '/user/add',
            component : addUser
        },
        {
            path : '/user/list',
            component : listUser
        },
        {
            path : '/pdraf/list',
            component : creationPdraf

        },
        // DASHBOARD
        {
            path : '/dashboard',
            component : dashboard
        },
        {
            path : '/performances',
            component : performance
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
            component : promo
        },
        //compte credit
        {
            path : '/account',
            component : account
        },
        //AFROCASH ADMIN
        {
            path : '/afrocash/operation',
            component : afrocashOperationAdmin
        },
        {
            path : '/afrocash/all-transaction',
            component : transactionAfrocash
        },
        {
            path : '/afrocash/recouvrement',
            component : recouvrement
        },
        {
            path : "/material/entrepot",
            component : entrepot
        },
        {
            path : '/material/all-material',
            component : allMaterial
        },
        {
            path : '/inventory',
            component : inventaireReseaux
        },
        //COMMANDES
        {
            path : '/commandes',
            component : commande
        },
        // RAPPORT DE VENTE
        {
            path : '/rapport/add',
            component : addRapport            
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
            path : '/all-ventes',
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
            component : RetourAfrocash
        },
        {
            path : '/reabonnement-afrocash',
            component : ReaboAfrocash
        },
        {
            path : '/all-ventes-pdraf',
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
            component : newCommand
        },
        {
            path : '/command/list',
            component : commande
        },
        // PARAMETERS
        {
            path : '/setting/profile',
            component : profile
        },
        {
            path : '/setting/formule',
            component : formule
        },
        {
            path : '/depot/ravitailler',
            component : ravitaillerDepot
        },
        {
            path : '/ravitailler/vendeur/:id',
            component : ravitaillerVendeur
        },
        {
            path : '/commande-credit/all',
            component : commandCredit
        },  
        {
            path : '/livraison/all',
            component : livraisonMateriel
        },
        {
            path : '/user/edit/:id',
            component : editUser
        },
        {
            path : '/pay-comission',
            component : payComission
        }
    ]



})
export default router