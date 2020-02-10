<template>

<div class="">
  <loading :active.sync="isLoading"
      :can-cancel="false"
      :is-full-page="fullPage"></loading>

<!-- Erreor block -->
      <template v-if="errors.length" v-for="error in errors">
      <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
        <a href="#" class="uk-alert-close" uk-close></a>
        <p>{{error}}</p>
      </div>
    </template>

  <form>
    <div class="uk-alert-warning uk-border-rounded uk-box-shadow-small" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p> <span uk-icon="icon : warning"> </span> (*) Champs obligatoires !</p>
    </div>
    <div class="uk-child-width-1-2@m" uk-grid>
      <div class="">
        <!-- champs obligatoires -->
        <div class="">
          <label for="">Email *</label>
          <input v-model="email" type="email" name="" :class="inputClass" value="" placeholder="ex : xyz@gmail.com">
        </div>
        <div class="">
          <label for="">Telephone *</label>
          <input v-model="phone"  type="text" name="" :class="inputClass" value="" placeholder="ex : 666 000 000">
        </div>
        <div class="">
          <label>Agence *</label>
          <input v-model="agence" type="text" name="" :class="inputClass" value="" placeholder="Entrez le nom de l'agence">
        </div>
        <div class="">
          <label for="">Niveau d'access *</label>
          <select v-model="access" class="uk-select uk-border-rounded uk-box-shadow-hover-small uk-margin-small" name="">
            <option value="">-- Niveau d'access --</option>
            <option v-for="(type , name) in typeAccess" :value="name"> {{type}}</option>
          </select>
        </div>
      </div>
      <div class="">
        <!-- champs facultatif -->
        <div class="">
          <label for="">NumDist</label>
          <input v-model="numdist" type="text" name="" value="" :class="inputClass" placeholder="Numero Distributeur">
        </div>
        <div class="">
          <label for="">Societe</label>
          <input v-model="societe" type="text" name="" value="" :class="inputClass" placeholder="Nom de l'entreprise">
        </div>
        <div class="">
          <label for="">Rccm</label>
          <input v-model="rccm" type="text" name="" value="" :class="inputClass" placeholder="Numero Registre du commerce">
        </div>
        <div class="">
          <label for="">Ville</label>
          <input v-model="ville" type="text" name="" value="" :class="inputClass" placeholder="ex : Conakry">
        </div>
        <div class="">
          <label for="">Adresse</label>
          <input v-model="adresse" type="text" name="" value="" :class="inputClass" placeholder="Quartier">
        </div>
      </div>
    </div>
    <div class="uk-margin-small">
      <button @click="addUser()" type="button" name="button" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-sall">
         validez
         <span uk-icon="icon : check"></span>
       </button>
    </div>
  </form>
</div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        mounted() {

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
              technicien : 'Technicien'
            },
            inputClass : "uk-input uk-border-rounded uk-box-shadow-hover-small uk-margin-small",
            isLoading : false,
            fullPage : true,
            // form data
            email : "",
            phone : "",
            agence : "",
            access : "",
            numdist : "",
            societe : "",
            rccm : "",
            ville : "",
            adresse : "",
            // error
            errors : []
          }
        },
        methods : {
          addUser : async function() {
            try {
              this.isLoading = true
              let response = await axios.post("/admin/add-user",{
                _token : this.myToken,
                email : this.email,
                phone : this.phone,
                localisation : this.agence,
                type : this.access,
                num_dist : this.numdist,
                societe : this.societe,
                rccm : this.rccm,
                ville : this.ville,
                adresse : this.adresse
              })
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
          }
        }
    }
</script>
