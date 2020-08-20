<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>
        <h3><router-link to="/command/list"><button class="uk-button uk-button-default uk-button-small uk-border-rounded" uk-tooltip="Retour"><span uk-icon="arrow-left"></span></button></router-link> Ravitailler un vendeur</h3>
        <hr class="uk-divider-small">
        <!-- Erreor block -->
          <template v-if="errors.length" v-for="error in errors">
          <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
          </div>
        </template>
    <template id="">
      <form @submit.prevent="sendRavitaillement()">
        <!-- SELECT VENDEURS -->
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-3@m">
              <label>Vendeur</label>
              <input type="text" disabled class="uk-input uk-border-rounded" :value="commande.vendeurs_localisation">
            </div>
            <div class="uk-width-1-6@m">
              <label>Parabole du</label>
              <input type="text" class="uk-input uk-border-rounded" disabled :value="commande.parabole_du">
            </div>
            <div class="uk-width-1-5@m">
                <label>Terminal restant</label>
                <input type="text" class="uk-input uk-border-rounded" disabled :value="commande.restant_ravit.terminal">
            </div>
            <div class="uk-width-1-5@m">
              <label for="">Parabole restant</label>
              <input type="text" class="uk-input uk-border-rounded" disabled :value="commande.restant_ravit.parabole">
            </div>
          <!-- SELECT MATERIAL -->
          <div class="uk-width-1-6@m">
            <label for="">Materiel</label>
            <select  class="uk-select uk-border-rounded" v-model="formData.produit">
              <option value="">--Materiel--</option>
              <option v-for="p in commande.materials" :key="p.reference" :value="p.reference"> {{p.libelle}}</option>
            </select>
          </div>
          <!-- SELECT DEPOT -->
          <div class="uk-width-1-3@m">
            <label for="">Depot</label>
            <select id="depot" class="uk-select uk-border-rounded" v-model="formData.depot">
              <option value="">-- Choisissez un depot --</option>
              <option :value="dep.localisation" :key="dep.localisation"  v-for="dep in depots">{{dep.localisation}} | Terminal : {{dep.terminal}} | Parabole: {{dep.parabole}}</option>
            </select>
          </div>
        <div class="uk-width-1-6@m">
          <label>Quantite</label>
          <input type="number" required min="0" class="uk-input uk-border-rounded" v-model="formData.quantite">
        </div>
        <div class="uk-width-1-6@m">
          <label>Compense</label>
          <input type="text" class="uk-input uk-border-rounded" disabled v-model="formData.compense">
        </div>
        <div class="uk-width-1-1@m">
          <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small">valider <span uk-icon="icon:check;ratio:.8"></span></button>
        </div>
        </div>
      </form>
    </template>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
      created () {
        this.isLoading = true
      },
        mounted() {
          this.getInfosCommande()
          this.commandStateTest()
        },
        components : {
          Loading
        },
        data () {
          return {
            isLoading : false,
            fullPage : true,
            commande : {
              id : this.$route.params.id,
              restant_ravit : ""
            },
            depots : [],
            errors : [],

            formData : {
              _token : "",
              vendeur : "",
              produit : "",
              depot : "",
              quantite : 0,
              compense : 0
            }
          }
        },
        methods : {
          commandStateTest : async function () {
            try {
                let response = await axios.get('/user/ravitailler/validate-test/'+this.$route.params.id)
                if(response) {
                  if(response.data == 'done') {
                    this.$router.push('/command/list')
                  }
                  else if(response.data == 'fail') {
                    this.getInfosCommande()
                  }
                }
            } catch(error) {
                alert('commanstate')
                console.log(error)
            }
          },
          getInfosCommande : async function () {
            try {
              this.isLoading = true
              let response = await axios.get("/logistique/ravitaillement/"+this.$route.params.id+"/infos")
              this.commande = response.data
              this.formData.compense = this.commande.parabole_du
              response = await axios.get("/logistique/ravitaillement/list-depot")
              this.depots = response.data
              this.formData._token = this.myToken
              this.formData.vendeur = this.commande.vendeurs
              this.isLoading = false

              // 
              
            } catch (e) {
              alert(e)
            }
          },
          sendRavitaillement : async function () {
            this.isLoading = true
            try {
              let response = await axios.post("/user/ravitailler/"+this.commande.id,this.formData)
              if(response.data == 'done') {
                alert("Success !")
                this.getInfosCommande()
                this.commandStateTest()
              }
            }catch (error) {
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
