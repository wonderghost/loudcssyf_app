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
    statusLivraison : 'non_confirmer',
    statusCommandCredit : 'unvalidated',
    typeUserFilter : document.querySelector("input[id=user-type]") ? document.querySelector("input[id=user-type]").value : '',
    typeUser : document.querySelector("input[id=user-type]") ? document.querySelector("input[id=user-type]").value : '',
    myToken : document.querySelector("meta[name=csrf-token]").content,
    userLocalisation : document.querySelector("input[id=user-localisation]") ? document.querySelector("input[id=user-localisation]").value : '',
    userName : document.querySelector("input[id=username]") ? document.querySelector("input[id=username]").value : '',
    rapportVentes : [],
    payComissionList : [],
    serialNumberList : [],
  },
  mutations : {
    searchText (state , word) {
      state.searchState = true
      state.searchText = word
    },
    filterUsers (state , type ) {
      state.searchState = false
      state.typeUserFilter = type
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
    },
    setRapportVente (state , data) {
      state.rapportVentes = data
    },
    setPayComissionList (state , data) {
      state.payComissionList = data
    },
    setSerialNumberList (state,data) {
      state.serialNumberList = data
    }
  },
  actions : {

  }
})
export default store
