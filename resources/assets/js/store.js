
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
    commandMaterial : {
      confirmed : [],
      unconfirmed : []
    },
    livraison : {
      livred : [],
      unlivred : []
    },
    commandCredit : {
      confired : [],
      unconfirmed : [],
      aborted : []
    }
  },
  mutations : {
    searchText (state , word) {
      state.searchState = true
      state.searchText = word
    },
    filterUsers (state , type ) {
      state.searchState = false
      state.typeUser = type
    }
  },
  actions : {

  }
})
export default store
