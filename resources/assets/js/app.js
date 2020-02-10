
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue');
window.axios = require('axios')
window.$ = window.jQuery = require('jquery')

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
// admin front end
Vue.component('user-component',require('./adminComponents/UserComponent.vue').default)
Vue.component('filter-user-component',require('./adminComponents/FilterComponentUser.vue').default)
Vue.component('account',require('./adminComponents/AccountComponent.vue').default)
Vue.component('command',require('./adminComponents/CommandComponent.vue').default)
Vue.component('credit-component',require('./adminComponents/CreditComponent.vue').default)
Vue.component('home-component',require('./userComponents/homeComponent.vue').default)
Vue.component('add-user-component',require('./adminComponents/AddUserComponent.vue').default)
// /
import Vue from 'vue'
import Vuex from 'vuex'
import Vuikit from 'vuikit'
import numeral from 'numeral';
import numFormat from 'vue-filter-number-format'
import store from './store'

Vue.use(Vuikit)
Vue.use(Vuex)
Vue.filter('numFormat', numFormat(numeral));

const app = new Vue({
    el: '#app',
    store
});
