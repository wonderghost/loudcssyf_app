<template>
<div class="uk-container uk-container-large">
  <loading :active.sync="isLoading"
      :can-cancel="false"
      :is-full-page="fullPage"
      loader="bars"
      :opacity="1"
      color="#1e87f0"
      background-color="#fff"></loading>

      
  <ul class="uk-breadcrumb">
    <li><router-link uk-tooltip="Tableau de bord" to="/dashboard"><span uk-icon="home"></span></router-link></li>
    <li><span>Utilisateur</span></li>
    <li><span>Nouvel utilisateur</span></li>
  </ul>

  <h3 class="uk-margin-top">Ajoutez un Utilisateur</h3>
  <hr class="uk-divider-small">
      <!-- Erreor block -->
      <template v-if="errors">
        <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-width-1-3@m" uk-alert>
          <a href="#" class="uk-alert-close" uk-close></a>
          <p>{{error}}</p>
        </div>
      </template>

    <template v-if="typeUser == 'admin'">
      <form @submit.prevent="addUser()">
        <div class="uk-child-width-1-3@m" uk-grid>
          <div class="">
            <!-- champs obligatoires -->
            <div class="">
              <label for="">Email *</label>
              <input v-model="dataForm.email" type="email" name="" :class="inputClass" placeholder="ex : xyz@gmail.com">
            </div>
            <div class="">
              <label for="">Telephone *</label>
              <input v-model="dataForm.phone"  type="text" name="" :class="inputClass" placeholder="ex : 666 000 000">
            </div>
            <div class="">
              <label>Agence *</label>
              <input v-model="dataForm.agence" type="text" name="" :class="inputClass" placeholder="Entrez le nom de l'agence">
            </div>
            <div class="">
              <label for="">Niveau d'access *</label>
              <select @change="setRequireState()" v-model="dataForm.access" class="uk-select uk-border-rounded" name="">
                <option value="">-- Niveau d'access --</option>
                <option v-for="(type , name) in typeAccess" :key="name" :value="name"> {{type}}</option>
              </select>
            </div>
            <div v-if="dataForm.access == 'pdraf'">
              <label for="">Point de contact *</label>
              <select v-model="dataForm.pdc" class="uk-select uk-border-rounded">
                <option value="">--Point de Contact --</option>
                <option :value="p.username" v-for="p in pdcUsers" :key="p.username">{{ p.localisation }}</option>
              </select>
            </div>
            <div>
              <label for="">Confirmez votre mot de passe</label>
              <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe">
            </div>
          </div>
          <div v-if="dataForm.access == 'technicien'">
            <!-- PROFILE UTILISATEUR / OBLIGATOIRE SEULEMENT POUR LE TECHNICIEN -->
            <div>
              <label for="">Nom</label>
              <input v-model="dataForm.nom" type="text" class="uk-input uk-border-rounded">
            </div>
            <div>
              <label for="">Prenom</label>
              <input v-model="dataForm.prenom" type="text" class="uk-input uk-border-rounded">
            </div>
            <div>
              <label for="">Date de naissance</label>
              <input v-model="dataForm.date_naissance" type="date" class="uk-input uk-border-rounded">
            </div>
          </div>
          <div class="" v-if="requireState">
            <!-- champs facultatif -->
            <div class="">
              <label for="">NumDist</label>
              <input v-model="dataForm.numdist" type="text" name="" value="" :class="inputClass" placeholder="Numero Distributeur">
            </div>
            <div class="">
              <label for="">Societe <span v-if="dataForm.access == 'pdc'">*</span></label>
              <input v-model="dataForm.societe" type="text" name="" :class="inputClass" placeholder="Nom de l'entreprise">
            </div>
            <div class="">
              <label for="">Rccm</label>
              <input v-model="dataForm.rccm" type="text" name="" :class="inputClass" placeholder="Numero Registre du commerce">
            </div>
            <div class="">
              <label for="">Ville</label>
              <input v-model="dataForm.ville" type="text" name="" :class="inputClass" placeholder="ex : Conakry">
            </div>
            <div class="">
              <label for="">Adresse</label>
              <input v-model="dataForm.adresse" type="text" name="" :class="inputClass" placeholder="Quartier">
            </div>
          </div>
        </div>
        <div class="uk-margin-small">
          <button type="submit" name="button" class="uk-button uk-width-1-1@s uk-width-1-6@m uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-sall">
            validez
          </button>
        </div>
      </form>
    </template>
    <template v-else>
      <div class="uk-alert-warning" uk-alert>
        <p class="uk-text-center"> <span uk-icon="icon : warning"></span> Vous n'etes pas autorise a effecture cette action ! Contactez l'administrateur. </p>
      </div>
    </template>
</div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
  export default {
      mounted() {
        this.allPdc()
      },
      components : {
        Loading
      },
      data () {
        return {
          typeAccess : {
            v_da : 'Distributeur Agree',
            v_standart : 'Vendeur standart',
            logistique : "Responsable Logistique",
            controleur : 'Controleur',
            gcga : 'Gestionnaire Cga',
            grex : 'Gestionnaire Rex',
            coursier : "Coursier",
            gdepot : 'Gestionnaire depot',
            commercial : 'Responsable commercial',
            technicien : 'Technicien',
            pdc : 'Point de Contact',
            pdraf : 'Point de Retrait Afrocash',
            graf : 'Gestionnaire Reseaux Afrocash',
            g_compte : 'Grand Compte'
          },
          inputClass : "uk-input uk-border-rounded",
          isLoading : false,
          fullPage : true,
          // form data
          dataForm : {
            _token : "",
            email : "",
            phone : "",
            agence : "",
            access : "",
            numdist : "",
            societe : "",
            rccm : "",
            ville : "",
            adresse : "",
            password_confirmation : "",
            pdc : "",
            // infos profile obligatoires pour le technicien

            nom : "",
            prenom : "",
            date_naissance : ""
          },
          // error
          errors : [],
          requireState : true,
          pdcUsers : []
        }
      },
      methods : {
        allPdc : async function () {
          try {
              let response = await axios.get('/admin/pdc/list')
              if(response) {
                this.pdcUsers = response.data
              }
          } catch(error) {
              alert(error)
          }
        },
        setRequireState : function () {
          try {
              if(this.dataForm.access == 'v_da' || this.dataForm.access == 'pdc' || this.dataForm.access == "") {
                // set requireState to true
                this.requireState = true
              }
              else {
                // set requireState to false
                this.requireState = false
              }
          } catch(error) {
              alert(error)
          }
        },
        addUser : async function() {
          try {
            this.isLoading = true
            this.dataForm._token = this.myToken
            if(this.dataForm.access == 'pdc') {
              var response = await axios.post('/admin/pdc/add',this.dataForm)
            }
            else if(this.dataForm.access == 'pdraf') {
              var response = await axios.post('/admin/pdraf/add',this.dataForm)
            }
            else if(this.dataForm.access == 'technicien') {
              var response = await axios.post('/admin/add-technicien',this.dataForm)
            }
            else {
              var response = await axios.post("/admin/add-user",this.dataForm)
            }

            if(response && response.data == 'done') {
              this.isLoading = false
              alert("Operation success!")
              Object.assign(this.$data,this.$options.data())
              this.allPdc()
            }
          } 
          catch (error) {
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
        typeUser() {
          return this.$store.state.typeUser
        }
      }
  }
</script>
