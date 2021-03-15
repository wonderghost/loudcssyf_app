<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <h3>Nouveau Rapport</h3>
        <hr class="uk-divider-small">

        <template v-if="typeUser == 'admin' || typeUser == 'controleur'">
          <template v-if="percentComExist">
            <div class="uk-grid" uk-grid>
              <div class="uk-width-1-2@m">
                <div class="uk-margin-small">
                  <label for=""> <span uk-icon="icon : calendar"></span> Date</label>
                  <input type="date" v-model="formData.date" class="uk-input uk-border-rounded" value="">
                </div>
                <div class="uk-margin-small">
                  <label for=""> <span uk-icon="icon : users"></span> Vendeurs</label>
                  <select class="uk-select uk-border-rounded" v-model="formData.vendeurs">
                    <option value="">--Choisir un Vendeurs--</option>
                    <option v-for="u in users" :key="u.username" :value="u.username">{{u.localisation}}</option>
                  </select>
                </div>
                <div class="uk-margin-small">
                  <div class="uk-alert-info uk-border-rounded" uk-alert>
                    <p>
                      <span uk-icon="icon : info"></span> Ce champs n'est pas obligatoire . activez le en cas de rapport promo !
                    </p>
                  </div>
                  <select v-model="formData.promo_id" class="uk-select uk-border-rounded">
                    <option value="none"> -- Promo --</option>
                    <option :value="lp.id" v-for="lp in listingPromo" :key="lp.id">{{lp.intitule}}</option>
                  </select>
                </div>
              </div>
            </div>

            <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
                <li><a @click="with_serial = true" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Recrutement</a></li>
                <li><a @click="with_serial = false" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Reabonnement</a></li>
                <li><a @click="with_serial = true" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Migration</a></li>
                <li><a  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Migration Gratuite</a></li>
            </ul>

            <ul class="uk-switcher uk-margin">
              <li>
                <recrutement-component 
                  :rapp-date="formData.date"
                  :rapp-vendeur="formData.vendeurs"
                  :promo-id="formData.promo_id"></recrutement-component>
              </li>
              <li>
                <!-- REABONNEMENT -->
                <reabonnement-component
                  :rapp-date="formData.date"
                  :rapp-vendeur="formData.vendeurs"
                  :promo-id="formData.promo_id"></reabonnement-component>
                <!-- // -->
              </li>
              <li>
                <!-- MIGRATION -->
                <!-- Error block -->
                <template v-if="errors.length">
                  <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{error}}</p>
                  </div>
                </template>
              <!-- // -->
                <form @submit.prevent="sendRapport('migration')" class="uk-width-1-2@m">
                  <div class="uk-margin-small">
                    <label for="">Quantite Materiel</label>
                    <input type="number" v-model="formData.quantite_materiel" min="1" class="uk-input uk-margin-small uk-border-rounded">
                  </div>
                  <!-- SERIAL NUMBERS -->
                  <template v-if="with_serial">
                    <div v-for="input in parseInt(formData.quantite_materiel)" :key="input" class="uk-margin-small">
                      <input type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[input-1]" required :placeholder="'Serial Number '+input">
                    </div>
                  </template>
                  <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                </form>
                <!-- // -->
              </li>
              <li>
                <!-- MIGRATION GRATUITE -->
                <template v-if="errors.length">
                  <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{error}}</p>
                  </div>
                </template>
              <!-- // -->
                <form @submit.prevent="sendRapport('migration_gratuite')" class="uk-width-1-2@m">
                  <div class="uk-margin-small">
                    <label for="">Quantite Materiel</label>
                    <input type="number" v-model="formData.quantite_materiel" min="1" class="uk-input uk-margin-small uk-border-rounded">
                  </div>
                  <!-- SERIAL NUMBERS -->
                  <template v-if="with_serial">
                    <div v-for="input in parseInt(formData.quantite_materiel)" :key="input" class="uk-margin-small">
                      <input type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[input-1]" required :placeholder="'Serial Number '+input">
                    </div>
                  </template>
                  <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                </form>
                <!-- // -->
              </li>
            </ul>
          </template>
          <template v-else>
            <div class="uk-alert-warning" uk-alert>
              <p class="uk-text-center">
                <span uk-icon="icon : warning"></span>
                Les pourcentages des comissions ne sont pas definis , vous ne pouvez donc pas ajouter de rapports !
                Contactez l'administrateur.
              </p>
            </div>
          </template>
        </template>
        <template v-else>
          <div class="uk-alert-warning" uk-alert>
            <p class="uk-text-center">
              <span uk-icon="icon : warning"></span>
              vous n'etes pas autorise a effectuer cette action ! Contactez l'administrateur
            </p>
          </div>
        </template>

  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import recrutementComp from './RecrutementComponent.vue'
import reabo from './ReabonnementComponent.vue'

  export default {
    components : {
      Loading,
      'recrutement-component' : recrutementComp,
      'reabonnement-component' : reabo
    },
    mounted() {
      UIkit.offcanvas($("#side-nav")).hide();
      this.getUsers()
      // 
    },
    updated() {
      if(Object.keys(this.percentComission).length) {
        this.percentComExist = true
      }
    },
    data () {
      return {
        isLoading : false,
        fullPage : false,
        users : [],
        with_serial : true,
        formData : {
          quantite_materiel : 0,
          vendeurs : "",
          date : "",
          montant_ttc : 0,
          promo_id : 'none',
          serial_number : [],
          formule : [],
          debut : [],
          duree : [],
          options : [],
          type_credit : "cga"
        },
        errors : [],
        listingPromo : [],
        percentComExist : false
      }
    },
    methods : {
      getUsers : async function () {
        try {
          this.isLoading = true
          let response = await axios.get('/user/all-vendeurs')
          this.users = response.data

          if(this.typeUser == 'admin') {
            response = await axios.get('/admin/formule/list')
          } else {
              response = await axios.get('/user/formule/list')
          }
          this.$store.commit('setFormuleList',response.data.formules)
          this.$store.commit('setOptionList',response.data.options)

          response = await axios.get('/user/promo/listing')
          this.listingPromo = response.data

          response = await axios.get('/admin/get-rapport-parameters')
          if(response) {
            this.percentComission = response.data
          }

          this.isLoading = false
        } catch (e) {
            alert(e)
        }
      },
      sendRapport : async function (type) {
        // this.isLoading = true
        this.formData._token = this.myToken
        try {
          if(type == 'reabonnement') {
            if(this.typeUser == 'admin') {
              var response = await axios.post('/admin/send-rapport/reabonnement',this.formData)
            }
            else {
              var response = await axios.post('/user/send-rapport/reabonnement',this.formData)
            }
            if(response.data == 'done') {
              this.isLoading = false
              alert("Success")
              location.reload()
            }
          }
          else if(type == 'migration_gratuite') {
            if(this.typeUser == 'admin') {

              var response = await axios.post('/admin/send-rapport/migration-gratuite',this.formData)
            }
            else {
              var response = await axios.post('/user/send-rapport/migration-gratuite',this.formData)
            }
            if(response.data == 'done') {
              
              UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Rapport ajoute :)</div>")
                .then(function () {
                  location.reload()
                })
            }
          }
          else {
            // ENVOI DU RAPPORT DE MIGRATION
            if(this.typeUser == 'admin') {
                var response = await axios.post('/admin/send-rapport/migration',this.formData)
            } else {
                var response = await axios.post('/user/send-rapport/migration',this.formData)
            }
            if(response.data == 'done') {
              UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Rapport ajoute :)</div>")
                .then(function () {
                  location.reload()
                })
            }
          }
        } catch (error) {
            this.isLoading = false
            if(error.response.data.errors) {
              let errorTab = error.response.data.errors
              for (var prop in errorTab) {
                this.errors.push(errorTab[prop][0])
              }
            } else {
                this.errors.push(error.response.data)
            }
        }
      }
    },
    computed : {
      myToken () {
        return this.$store.state.myToken
      },
      typeUser () {
        return this.$store.state.typeUser
      }
    }
  }
</script>
