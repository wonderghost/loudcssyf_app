
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue');
window.axios = require('axios')

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('user-component',require('./adminComponents/UserComponent.vue').default)
Vue.component('filter-user-component',require('./adminComponents/FilterComponentUser.vue').default)
Vue.component('account',require('./adminComponents/AccountComponent.vue').default)
Vue.component('command',require('./adminComponents/CommandComponent.vue').default)
Vue.component('credit-component',require('./adminComponents/CreditComponent.vue').default)
Vue.component('home-component',require('./userComponents/homeComponent.vue').default)
Vue.component('add-user-component',require('./adminComponents/AddUserComponent.vue').default)
Vue.component('rapport-component',require('./userComponents/RapportComponent.vue').default)
Vue.component('pay-comission-component',require('./userComponents/PayComissionComponent.vue').default)
Vue.component('inventory',require('./userComponents/InventoryComponent.vue').default)
Vue.component('new-command',require('./userComponents/NewCommandComponent.vue').default)
Vue.component('ravitaillement-command',require('./userComponents/RavitaillementComponent.vue').default)
Vue.component('livraison',require('./userComponents/LivraisonComponent.vue').default)
Vue.component('inventory-depot',require('./userComponents/InventoryDepotComponent.vue').default)
Vue.component('add-rapport',require('./adminComponents/AddRapportComponent.vue').default)
Vue.component('afrocash',require('./userComponents/AfrocashComponent.vue').default)
Vue.component('ravitaillement-depot',require('./adminComponents/RavitaillementDepotComponent.vue').default)
Vue.component('transaction-afrocash',require('./userComponents/TransactionAfrocashComponent.vue').default)
Vue.component('recouvrement',require('./userComponents/RecouvrementComponent.vue').default)
Vue.component('serial-search',require('./userComponents/SerialSearchComponent.vue').default)
Vue.component('dashboard',require('./adminComponents/DashboardComponent.vue').default)
Vue.component('tools',require('./userComponents/ToolsComponent.vue').default)
Vue.component('login',require('./userComponents/LoginComponent.vue').default)
Vue.component('promo',require('./adminComponents/PromoComponent.vue').default)
Vue.component('entrepot',require('./adminComponents/EntrepotComponent.vue').default)
Vue.component('afrocash-central',require('./adminComponents/AfrocashCentral.vue').default)
Vue.component('setting-view',require('./userComponents/SettingComponent.vue').default)
Vue.component('perform-objectif',require('./adminComponents/PerformObjectifComponent.vue').default)
Vue.component('historique-ravitaillement-depot',require('./adminComponents/HistoriqueRavitaillementDepot.vue').default)
Vue.component('objectif-component',require('./adminComponents/ObjectifComponent.vue').default)
Vue.component('transfert-material-depot',require('./adminComponents/TransfertMaterielDepot.vue').default)
Vue.component('all-objectif',require('./adminComponents/AllObjectifComponents.vue').default)
Vue.component('visual-objectif',require('./adminComponents/VisualObjectifComponent.vue').default)
Vue.component('deblocage-cga',require('./adminComponents/DeblocageCgaComponent.vue').default)
Vue.component('feedback',require('./userComponents/FeedBackComponent.vue').default)
Vue.component('objectif-user',require('./userComponents/ObjectifUserComponent.vue').default)
Vue.component('formule-component',require('./adminComponents/FormuleComponent.vue').default)
Vue.component('add-formule-component',require('./adminComponents/addFormuleComponent.vue').default)
Vue.component('recrutement-component',require('./adminComponents/RecrutementComponent.vue').default)
Vue.component('reabonnement-component',require('./adminComponents/ReabonnementComponent.vue').default)
Vue.component('detail-rapport',require('./userComponents/DetailRapportComponent.vue').default)
Vue.component('alert-abonnement',require('./userComponents/AlertComponent.vue').default)
Vue.component('add-contact',require('./userComponents/AddContactComponent.vue').default)
Vue.component('affectation-materiel',require('./adminComponents/AffectationMaterielComponent.vue').default)
Vue.component('view-contact',require('./userComponents/ContactComponent.vue').default)

import Vue from 'vue'
import Vuex from 'vuex'
import Vuikit from 'vuikit'
import numeral from 'numeral';
import numFormat from 'vue-filter-number-format'
import store from './store'
Vue.use(Vuikit)
Vue.use(Vuex)
Vue.filter('numFormat', numFormat(numeral))

const app = new Vue({
    el: '#app',
    store
});
