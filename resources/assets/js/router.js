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

Vue.use(VueRouter);

const router = new VueRouter({
    mode : 'hash',
    routes:[
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