<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>
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
              <div class="uk-width-1-3@m">
                <!-- Infos Materiel -->
                <h4>Infos Materiel</h4>
                <ul class="uk-list uk-list-divider">
                  <li><span>Numero Materiel</span> : <span>{{serialNumber.serial}} <sup v-if="serialNumber.etat && serialNumber.etat != '-'" class="uk-badge">{{serialNumber.etat}}</sup> </span></li>
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
              <div class="uk-width-1-3@m">
                <!-- Infos Abonnement -->
                <h4>Infos Activation</h4>
                <!-- // -->
                <ul v-if="serialNumber.rapport_vente" class="uk-list uk-list-divider">
                  <li>
                    <span>Active en : </span>
                    <span class="uk-text-bold">{{serialNumber.rapport_vente.type}}</span>
                  </li>
                  <li>
                    <span>Date d'activation : </span>
                    <span class="uk-text-bold">{{serialNumber.rapport_vente.date_rapport}}</span>
                  </li>
                  <li>
                    <span>Rapport enregistre le :</span>
                    <span class="uk-text-bold">{{serialNumber.rapport_vente.created_at}}</span>
                  </li>
                </ul>
              </div>
              <div class="uk-width-1-3@m">
                <h4>Infos Abonnement</h4>
                <div uk-slider>
                  <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1">
                      <ul class="uk-slider-items uk-grid-small uk-child-width-1-1@m">
                          <li v-for="a in serialNumber.abonnements" :key="a.id">
                              <div class="uk-card uk-card-default uk-padding-remove" style="box-shadow : none !important">
                                  <div class="uk-card-body uk-padding-remove">
                                     <ul class="uk-list">
                                       <li class="uk-padding-remove uk-margin-remove">
                                         <span>Formule : </span><span class="uk-text-bold">{{a.formule_name}}</span>
                                       </li>
                                       <li class="uk-padding-remove uk-margin-remove">
                                         <span>Duree : </span><span class="uk-text-bold">{{a.duree}}</span>
                                       </li>
                                       <li class="uk-padding-remove uk-margin-remove">
                                         <span>Option : </span><span class="uk-text-bold">
                                           <i v-for="op in a.option" :key="op.id">{{op.id_option}}</i>
                                         </span>
                                       </li>
                                       <li class="uk-padding-remove uk-margin-remove">
                                         <span>Debut : </span><span class="uk-text-bold">{{a.debut}}</span>
                                       </li>
                                       <li class="uk-padding-remove uk-margin-remove">
                                         <span>Fin : </span><span class="uk-text-bold">{{a.fin}}</span>
                                       </li>
                                       <li class="uk-margin-remove uk-padding-remove">
                                         <span>Distributeur : </span><span class="uk-text-bold">{{a.distributeur}}</span>
                                       </li>
                                       <li class="uk-margin-remove uk-padding-remove">
                                         <span>Status : </span>
                                       </li>
                                     </ul>
                                  </div>
                              </div>
                          </li>
                      </ul>
                  </div>
                  <div class="uk-margin-large-top">
                    <a class="uk-position-bottom-center uk-position-small uk-margin-large-right" href="#" uk-slidenav-previous uk-slider-item="previous"></a>
                    <a class="uk-position-bottom-center uk-position-small uk-margin-large-left " href="#" uk-slidenav-next uk-slider-item="next"></a>
                </div>
                </div>
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
