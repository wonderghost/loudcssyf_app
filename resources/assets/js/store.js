
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
  state : {
    users : [],
    filtedredUser : [],
    searchText : ""
  },
  mutations : {
    searchText (state , word) {
      state.searchText = word
    }
  },
  actions : {

  }
})

export default store
