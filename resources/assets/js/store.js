
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
  state : {
    users : [],
    soldeVendeur : [],
    filtedredUser : [],
    searchText : "",
    typeUser : document.querySelector("input[id=user-type]").value,
    searchState : true,
    myToken : document.querySelector("meta[name=csrf-token]").content,
    commandMaterial :[],
    livraisonMaterial :[],
    commandCredit :[],
    typeCommand : 'en attente',
    statusLivraison : 'unlivred',
    statusCommandCredit : 'unvalidated'
  },
  mutations : {
    searchText (state , word) {
      state.searchState = true
      state.searchText = word
    },
    filterUsers (state , type ) {
      state.searchState = false
      state.typeUser = type
    },
    setTypeCommand (state , type) {
      state.typeCommand = type
    },
    setCommandMaterial (state,data) {
      state.commandMaterial = data
    },
    setLivraisonMaterial (state,data) {
      state.livraisonMaterial = data
    },
    setCommandCredit (state , data) {
      state.commandCredit = data
    }
    ,
    setStateLivraison (state ,status) {
      state.statusLivraison = status
    },
    setStatusCommandCredit (state , type) {
      state.statusCommandCredit = type
    }
  },
  actions : {

  }
})
export default store
