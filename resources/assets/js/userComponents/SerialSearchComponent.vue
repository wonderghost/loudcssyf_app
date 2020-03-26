<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>
<!-- MODAL RECHERCHE FOR SERIAL NUMBER -->
    <div id="modal-search-serial" class="uk-modal-container" uk-modal="esc-close : false ; bg-close : false">
      <div class="uk-modal-dialog">
          
          <div class="uk-modal-header">
            <div class="uk-grid" uk-grid>
              <div class="uk-width-1-3@m"></div>
            <div class="uk-width-1-3@m">
              <form @submit.prevent="serialSearch()" class="uk-grid-small" uk-grid>
                <div class="uk-width-2-3@m uk-width-5-6@s">
                  <input type="search" class="uk-input uk-border-rounded" v-model="searchForm.dataSearch" placeholder="Trouvez un materiel ...">
                </div>
                <div class="uk-width-1-3@m uk-width-1-6@s">
                  <button type="submit" class="uk-button uk-button-link uk-border-rounded" style="margin-top : 6%"> <i class="material-icons">search</i> </button>
                </div>
            </form>
            </div>
            <div class="uk-width-1-3@m"></div>
            </div>
            
          </div>
          <div class="uk-modal-body">
            <div class="uk-grid uk-grid-divider" uk-grid>
              <div class="uk-width-1-2@m">
                <!-- Infos Materiel -->
                <h4>Infos Materiel</h4>
                <ul class="uk-list uk-list-divider">
                  <li><span>Numero Materiel</span> : <span>{{serialNumber.serial}}</span></li>
                  <li>
                    <span>Status</span> : 
                    <span v-if="serialNumber.status == 'inactif'" class="uk-alert-danger">{{serialNumber.status}}</span>
                    <span v-else class="uk-alert-success">{{serialNumber.status}}</span>
                  </li>
                  <li><span>Vendeurs</span> : <span>{{serialNumber.vendeurs}}</span></li>
                  <li><span>Origine</span> : <span>{{serialNumber.origine}}</span></li>
                </ul>
                <!-- // -->
              </div>
              <div class="uk-width-1-2@m">
                <!-- Infos Abonnement -->
                <h4>Infos Abonnement</h4>
                <!-- // -->
              </div>
            </div>
          </div>
          <div class="uk-modal-footer uk-text-right">
              <button class="uk-button uk-button-small uk-border-rounded uk-button-danger uk-modal-close" type="button">Fermer</button>
          </div>
      </div>
  </div>
<!-- // -->
  </div>
</template> 
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

  export default {
    components : {
      Loading
    },
    mounted() {
      this.searchForm._token = this.myToken
    },
    data() {
      return {
        isLoading : false,
        fullPage : true,
        searchForm : {
          _token : "",
          dataSearch : ""
        },
        serialNumber : {},
      }
    },
    methods : {
      serialSearch : async function() {
        this.isLoading = true
        UIkit.modal($("#modal-search-serial")).hide()
        try {
          if(this.searchForm.dataSearch == '') {
            throw "Tapez une recherche dans le champs vides!"
          }
          let response = await axios.get('/user/search/serial/'+this.searchForm.dataSearch)
          this.serialNumber = response.data
          this.isLoading = false
          UIkit.modal($("#modal-search-serial")).show()
        } catch (error) {
          this.isLoading = false
          var err
            if(error.response) {
              err = error.response.data
            } 
            else {
              err = error
            }
            UIkit.modal.alert("<div class='uk-alert-danger' uk-alert>"+err+"</div>")
              .then(function () {
                UIkit.modal($("#modal-search-serial")).show()
              })
        }
      }
    },
    computed : {
      myToken() {
        return this.$store.state.myToken
      }
    }
  }
</script>
