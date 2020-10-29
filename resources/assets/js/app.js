
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

Vue.component('filter-user-component',require('./adminComponents/FilterComponentUser.vue').default)
Vue.component('home-component',require('./userComponents/homeComponent.vue').default)
Vue.component('livraison',require('./userComponents/LivraisonComponent.vue').default)
Vue.component('serial-search',require('./userComponents/SerialSearchComponent.vue').default)
Vue.component('tools',require('./userComponents/ToolsComponent.vue').default)
Vue.component('login',require('./userComponents/LoginComponent.vue').default)
Vue.component('setting-view',require('./userComponents/SettingComponent.vue').default)
Vue.component('deblocage-cga',require('./adminComponents/DeblocageCgaComponent.vue').default)
Vue.component('feedback',require('./userComponents/FeedBackComponent.vue').default)
Vue.component('detail-rapport',require('./userComponents/DetailRapportComponent.vue').default)
Vue.component('set-rapport-parametre',require('./adminComponents/SetRapportParametre.vue').default)
Vue.component('download-to-excel',require('./userComponents/DownloadExcelComponent.vue').default)
Vue.component('historique-reactivation-materiel',require('./pdrafComponents/HistoriqueReactivationComponent.vue').default)


// 

import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import numeral from 'numeral';
import numFormat from 'vue-filter-number-format'
import store from './store'
import Router from './router.js'


Vue.use(Vuex)
Vue.filter('numFormat', numFormat(numeral))
Vue.use(VueRouter)

const app = new Vue({
    el: '#app',
    store,
    router : Router
});
