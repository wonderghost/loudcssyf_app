<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        color="#1e87f0"
        background-color="#fff"></loading>
        <h3><router-link to="/command/list"><button class="uk-button uk-button-default uk-button-small uk-border-rounded" uk-tooltip="Retour"><span uk-icon="arrow-left"></span></button></router-link> Ravitailler un vendeur</h3>
        <hr class="uk-divider-small">
        <!-- Erreor block -->
          <template v-if="errors">
          <div  v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
          </div>
        </template>
    <div class="uk-grid-small uk-grid-divider" uk-grid>
      <div class="uk-width-1-3@m">
        <form @submit.prevent="sendRavitaillement()">
          <!-- SELECT VENDEURS -->
          <div class="uk-grid-small" uk-grid>
              <div class="uk-width-1-2@m">
                <label>Vendeur</label>
                <input type="text" disabled class="uk-input uk-border-rounded" :value="commande.vendeurs_localisation">
              </div>
              <div class="uk-width-1-2@m">
                <label>Parabole du</label>
                <input type="text" class="uk-input uk-border-rounded" disabled :value="commande.parabole_du">
              </div>
              <div class="uk-width-1-2@m">
                  <label>Terminal restant</label>
                  <input type="text" class="uk-input uk-border-rounded" disabled :value="commande.restant_ravit.terminal">
              </div>
              <div class="uk-width-1-2@m">
                <label for="">Parabole restant</label>
                <input type="text" class="uk-input uk-border-rounded" disabled :value="commande.restant_ravit.accessoire">
              </div>
            <!-- SELECT MATERIAL -->
            <div class="uk-width-1-2@m">
              <label for="">Materiel</label>
              <select  class="uk-select uk-border-rounded" v-model="formData.produit">
                <option value="">--Materiel--</option>
                <option v-for="p in commande.materials" :key="p.reference" :value="p.reference"> {{p.libelle}}</option>
              </select>
            </div>
            <!-- SELECT DEPOT -->
            <div class="uk-width-1-2@m">
              <label for="">Depot</label>
              <select id="depot" class="uk-select uk-border-rounded" v-model="formData.depot">
                <option value="">-- Choisissez un depot --</option>
                <option :value="dep.localisation" :key="dep.localisation"  v-for="dep in depots">{{dep.localisation}}</option>
              </select>
            </div>
          <div class="uk-width-1-2@m">
            <label>Quantite</label>
            <input type="number" required min="0" class="uk-input uk-border-rounded" v-model="formData.quantite">
          </div>
          <div class="uk-width-1-2@m">
            <label>Compense</label>
            <input type="text" class="uk-input uk-border-rounded" disabled v-model="formData.compense">
          </div>
          <div class="uk-width-1-1@m">
            <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small">valider <span uk-icon="icon:check;ratio:.8"></span></button>
          </div>
          </div>
        </form>
      </div>
      <div class="uk-width-2-3@m">
        <!-- INVENTAIRE PAR DEPOT  DES PRODUITS-->
        <div class="uk-grid-small" uk-grid>
          <div v-for="m in depots"  class="uk-width-1-2@m" :key="m.localisation">
            <div class="uk-card uk-border-rounded uk-card-body uk-background-muted uk-padding">
              <div class="uk-card-title">{{m.localisation}}</div>
              <p>
                <ul class="uk-list uk-list-divider">
                  <li v-for="p in m.produits" :key="p.infos.reference">
                    <span class="">{{ p.infos.libelle }} : {{p.quantite}}</span> ,
                  </li>
                </ul>
              </p>
            </div>
          </div>
        </div>
        <!-- // -->
      </div>
    </div>
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
          UIkit.offcanvas($("#side-nav")).hide();
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
                alert(error)
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
              this.errors = []
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
