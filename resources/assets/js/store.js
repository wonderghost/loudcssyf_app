
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
  state : {
    users : [],
    soldeVendeur : [],
    filtedredUser : [],
    searchText : "",
    searchState : true,
    commandMaterial :[],
    livraisonMaterial :[],
    commandCredit :[],
    typeCommand : 'en attente',
    statusLivraison : 'unlivred',
    statusCommandCredit : 'unvalidated',
    typeUser : document.querySelector("input[id=user-type]").value,
    myToken : document.querySelector("meta[name=csrf-token]").content,
    userLocalisation : document.querySelector("input[id=user-localisation]").value,
    userName : document.querySelector("input[id=username]").value
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
    },
    setSoldeVendeurs (state , data) {
      state.soldeVendeur = data
    }
  },
  actions : {

  }
})
export default store
