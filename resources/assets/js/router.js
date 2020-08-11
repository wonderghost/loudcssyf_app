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

Vue.use(VueRouter);

const router = new VueRouter({
    mode : 'hash',
    routes:[
        // {
        //     path : '/',
        //     redirect : '/admin/dashboard'
        // },
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
            path : '/admin/dashboard',
            component : dashboard
        },
        {
            path : '/admin/performances',
            component : performance
        },
        {
            path : '/admin/objectifs/visu',
            component : objectifs
        },
        {
            path : '/admin/objectifs/new',
            component : newObjectif
        },
        {
            path : "/admin/objectifs/all",
            component : allObjectif
        },
        // PROMO 
        {
            path : '/admin/promo',
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
        }
    ]



})
export default router