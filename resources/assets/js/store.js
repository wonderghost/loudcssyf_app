
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
  state : {
    users : [],
    filtedredUser : [],
    searchText : "",
    typeUser : "",
    searchState : true
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
