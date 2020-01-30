
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

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('user-component',require('./components/UserComponent.vue').default)
Vue.component('filter-user-component',require('./components/FilterComponentUser.vue').default)

import Vue from 'vue'
import Vuex from 'vuex'
import store from './store'

Vue.use(Vuex)

const app = new Vue({
    el: '#app',
    store
});
