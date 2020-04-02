<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>

  <!-- Error block -->
        <template v-if="errors.length" v-for="error in errors">
        <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
          <a href="#" class="uk-alert-close" uk-close></a>
          <p>{{error}}</p>
        </div>
      </template>
      <!-- // -->
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
                <option v-for="u in users" :value="u.username">{{u.localisation}}</option>
              </select>
            </div>
          </div>
        </div>

        <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
            <li><a @click="with_serial = true" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Recrutement</a></li>
            <li><a @click="with_serial = false" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Reabonnement</a></li>
            <li><a @click="with_serial = true" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Migration</a></li>
        </ul>

        <ul class="uk-switcher uk-margin">
          <li>
  					<form @submit.prevent="sendRapport('recrutement')" class="uk-width-1-2@m">
              <div class="uk-margin-small">
                <label for="">Quantite Materiel</label>
                <input type="number" min="1" required v-model="formData.quantite_materiel" class="uk-input uk-border-rounded" value="">
              </div>
              <!-- SERIAL NUMBERS -->
              <div v-if="with_serial" v-for="input in parseInt(formData.quantite_materiel)" class="uk-margin-small">
                <input type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[input-1]" required :placeholder="'Serial Number '+input">
              </div>
              <!-- // -->
              <div class="uk-margin-small">
                <label for=""> <span uk-icon="icon : credit-card"></span> Montant TTC</label>
                <input type="number" v-model="formData.montant_ttc" class="uk-input uk-border-rounded" value="">
              </div>
  					<button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
          </form>
          </li>
          <li>
            <!-- REABONNEMENT -->
  					<form @submit.prevent="sendRapport('reabonnement')" class="uk-width-1-2@m">
                <div class="uk-margin-small">
                  <label for=""> <span uk-icon="icon : credit-card"></span> Montant TTC</label>
                  <input type="number" v-model="formData.montant_ttc" class="uk-input uk-border-rounded">
                </div>
    					<div class="uk-margin-small">
    						<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
    	            <label>
    								<input type="radio" name="type_credit" value="cga" checked> CGA
    							</label>
    	            <label>
    								<input type="radio" name="type_credit" value="rex"> REX
    							</label>
    	        </div>
    				</div>
    				<button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
          </form>
            <!-- // -->
          </li>
          <li>
            <!-- MIGRATION -->
            <form @submit.prevent="sendRapport('migration')" class="uk-width-1-2@m">
              <div class="uk-margin-small">
                <label for="">Quantite Materiel</label>
                <input type="number" v-model="formData.quantite_materiel" min="1" class="uk-input uk-margin-small uk-border-rounded">
              </div>
              <!-- SERIAL NUMBERS -->
              <div v-if="with_serial" v-for="input in parseInt(formData.quantite_materiel)" class="uk-margin-small">
                <input type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[input-1]" required :placeholder="'Serial Number '+input">
              </div>
              <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
            </form>
            <!-- // -->
          </li>
        </ul>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
  export default {
    components : {
      Loading
    },
    created (){
      this.isLoading = true
    },
    mounted() {
      this.getUsers()
    },
    data () {
      return {
        isLoading : false,
        fullPage : true,
        users : [],
        with_serial : true,
        formData : {
          quantite_materiel : 0,
          vendeurs : "",
          date : "",
          montant_ttc : 0,
          serial_number : [],
          type_credit : "cga"
        },
        errors : []
      }
    },
    methods : {
      getUsers : async function () {
        try {
          let response = await axios.get('/user/all-vendeurs')
          this.users = response.data
          this.isLoading = false
        } catch (e) {
            alert(e)
        }
      },
      sendRapport : async function (type) {
        this.isLoading = true

        this.formData._token = this.myToken
        try {
          if(type == 'recrutement') {
            if(this.typeUser == 'admin') {
              var response = await axios.post('/admin/send-rapport/recrutement',this.formData)
            }
            else {
              var response = await axios.post('/user/send-rapport/recrutement',this.formData)
            }
            if(response.data == 'done') {
              this.isLoading = false
              UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Rapport ajoute :)</div>")
                .then(function () {
                  location.reload()
                })
            }
          }
          else if(type == 'reabonnement') {
            if(this.typeUser == 'admin') {
              var response = await axios.post('/admin/send-rapport/reabonnement',this.formData)
            }
            else {
              var response = await axios.post('/user/send-rapport/reabonnement',this.formData)
            }
            if(response.data == 'done') {
              this.isLoading = false
              UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Rapport ajoute :)</div>")
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
