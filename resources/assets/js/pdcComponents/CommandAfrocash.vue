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
            <li><span>Reseaux Afrocash</span></li>
            <li><span>Commande Materiel</span></li>
        </ul>

        <h3 class="uk-margin-top">Commande Materiel</h3>
        <hr class="uk-divider-small">

        <nav class="" uk-navbar>
          <div class="uk-navbar-left">
              <ul class="uk-navbar-nav">
                  <li class=""><router-link to="/pdc/command/list">Toutes les commandes</router-link></li>
              </ul>
          </div>
        </nav>

        <div class="uk-grid-small uk-grid-divider" uk-grid>
            <div class="uk-width-1-3@m">
                <!-- SOLDE AFROCASH -->
                <div class="uk-card uk-card-default uk-border-rounded" style="box-shadow : none !important ; border : solid 1px #ddd !important;">
                    <div class="uk-card-header">
                      <h3 class="uk-card-title">SOLDE AFROCASH (GNF)</h3>
                    </div>
                    <div class="uk-card-body uk-text-center">
                      <span class=" uk-card-title">{{ soldeAfrocash.solde | numFormat}}</span>
                    </div>
                </div>  
                <!-- // -->
            </div>
            <div class="uk-width-2-3@m">
              <!-- Erreor block -->
              <template v-if="errors.length">
                <div class="uk-alert-danger uk-border-rounded uk-width-2-3@m" v-for="(error,index) in errors" :key="index" uk-alert>
                  <a href="#" class="uk-alert-close" uk-close></a>
                  <p>{{error}}</p>
                </div>
              </template>

                <form @submit.prevent="sendCommandAfrocashRequest()">

                  <div class="uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
                    <div>
                        <!-- <label uk-tooltip="">Kit Complet</label> -->
                        <label for="">Article(s)</label>
                        <select @click="getData()" @change="getInfosByCommandMat()" v-model="commandDefaultValue" class="uk-select uk-border-rounded">
                          <option value="none">--Choisissez la commande --</option>
                          <option v-for="(cm,index) in commandParameters" :key="index" :value="cm.slug">{{cm.name}}</option>
                        </select>
                    </div>
                    <div id="">
                      <label>Qte <input min="1" required @keyup="chooseQuantite()" @change="chooseQuantite()" type="number" v-model="formData.quantite"  class="uk-input uk-border-rounded" placeholder="Quantite"> </label>
                    </div>
                    <div>
                      <label>Prix TTC (GNF)</label>
                      <input type="text" :value="material.ttc | numFormat"  disabled  class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                      <label>HT (GNF)</label>
                      <input type="text" :value="material.ht | numFormat" disabled  class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                      <label>TVA (18%) (GNF)</label>
                      <input type="text" :value="material.tva | numFormat" disabled  class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                      <label>Montant TTC (GNF)</label>
                      <input type="text" :value="montantTtc | numFormat"  disabled  class="uk-input uk-border-rounded" >
                    </div>
                    <!-- SUBVENTION -->
                    <div></div>
                    <div><label>Subvention</label></div>
                    <div>
                    <input type="text" disabled :value="subvention.ttc | numFormat"  class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                    <input type="text" disabled :value="subvention.ht | numFormat"  class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                      <input type="text" disabled :value="subvention.tva | numFormat"  class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                      <input type="text" disabled :value="montantTtcSubv | numFormat"  class="uk-input uk-border-rounded" >
                    </div>
                    <!-- MARGE -->
                    <div></div>
                    <div><label>Marge</label></div>
                    <div>
                      <input type="text" disabled :value="marge.ttc | numFormat" class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                      <input type="text" disabled :value="marge.ht | numFormat" class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                      <input type="text" disabled :value="marge.tva | numFormat" class="uk-input uk-border-rounded" >
                    </div>
                    <div>
                      <input type="text" disabled  class="uk-input uk-border-rounded" >
                    </div>
                  </div>

                  <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
                    <div>
                        <label uk-tooltip="">Migration</label>
                    </div>
                    <div id="">
                      <label>Qte<input type="number" :value="material.migration" disabled  class="uk-input uk-border-rounded"></label>
                    </div>
                  </div>
                  <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
                    <div>
                        <label uk-tooltip="">Paraboles dûes</label>
                    </div>
                    <div id="">
                      <label>Qte<input type="number" :value="material.parabole_du" disabled  class="uk-input uk-border-rounded"></label>
                    </div>
                  </div>

                  <hr class="uk-divider-small">
                  <div class="uk-grid-collapse" uk-grid>
                    <div class="uk-width-5-6@m">
                      <span>TOTAL TTC-MATERIEL (GNF)</span>
                    </div>
                    <div class="uk-width-1-6@m">
                      <input type="text" disabled :value="montantTtc | numFormat" class="uk-input uk-border-rounded" >
                    </div>
                  </div>
                  <div class="uk-grid-collapse" uk-grid>
                    <div class="uk-width-5-6@m">
                      <span>TOTAL SUBVENTION (GNF)</span>
                    </div>
                    <div class="uk-width-1-6@m">
                      <input type="text" disabled :value="montantTtcSubv | numFormat" class="uk-input uk-border-rounded" >
                    </div>
                  </div>
                  <div class="uk-grid-collapse uk-text-lead" uk-grid>
                    <div class="uk-width-5-6@m">
                      <span>TOTAL A PAYER (GNF)</span>
                    </div>
                    <div class="uk-width-1-6@m">
                      <input type="text" disabled :value="formData.prix_achat | numFormat" class="uk-input uk-border-rounded" >
                    </div>
                  </div>
                  <hr class="uk-divider-small">
                  <button type="submit" class="uk-button uk-button-small uk-button-primary uk-box-shadow-small uk-border-rounded uk-box-shadow-small">valider<span uk-icon="icon:check;ratio:.8"></span></button>
                </form>
            </div>
        </div>

    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components : {
            Loading
        },
        mounted() {
          this.getData()
        },
        data() {
            return {
                commandDefaultValue : "none",
                commandParameters : [],
                isLoading : false,
                fullPage : true,
                material : {},
                marge : {},
                subvention : {},
                montantTtc : 0,
                montantTtcSubv : 0,
                formData : {
                quantite : "",
                prix_achat : 0,
                _token : "",
                reference_material : "",
                promo_id : "",
                parabole_du : 0,
                },
                errors : [],
                soldeAfrocash : {}
            }
        },
        methods : {
            getData : async function () {
                try {
                    let response = await axios.get('/pdc/command/new')
                    let theResponse = await axios.get('/user/pdc/get-solde')
                    if(response && theResponse) {
                      this.commandParameters = response.data
                      this.soldeAfrocash = theResponse.data
                    }

                }
                catch(error) {
                    alert(error)
                }
            },
            getInfosByCommandMat : async function () {
            try {

              let response = await axios.get('/user/new-command/get-infos-material/'+this.commandDefaultValue)
              if(response) {

                this.material = response.data
                this.subvention = {
                  ttc : this.material.subvention,
                  ht : Math.round(this.material.subvention/1.18),
                  tva : Math.round(this.material.subvention - (this.material.subvention/1.18))
                }
                this.marge = {
                  ttc : this.material.marge,
                  ht : Math.round(this.material.marge/1.18),
                  tva : Math.round(this.material.marge - (this.material.marge/1.18))
                }

                this.chooseQuantite()
              }
            }
            catch(error) {
              if(error.response.data.errors) {
                let errorTab = error.response.data.errors
                for (var prop in errorTab) {
                  this.errors.push(errorTab[prop][0])
                }
              } else {
                  this.errors.push(error.response.data)
              }
            }
          },
          chooseQuantite : function () {
            this.montantTtc = parseInt(this.formData.quantite) * this.material.ttc
            this.montantTtcSubv = parseInt(this.formData.quantite) * this.material.subvention
            this.material.parabole_du = parseInt(this.formData.quantite) - (this.material.migration - this.material.compense)

            if(this.promoState) {
              // la promo est active
              this.formData.prix_achat = parseInt(this.formData.quantite) * ((this.material.prix_vente - (this.marge.ht)) - this.promo.subvention)
            } 
            else {
              // la promo est inactive
              this.formData.prix_achat = parseInt(this.formData.quantite) * (this.material.prix_vente - (this.marge.ht))  
            }

          },
          sendCommandAfrocashRequest : async function () {
            try {
              this.errors = []
              this.isLoading = true
              this.formData._token = this.myToken
              this.formData.reference_material = this.material.reference

              let response = await axios.post('/pdc/command/new',this.formData)
              if(response && response.data == 'done') {
                alert("Success!")
                Object.assign(this.$data,this.$options.data())
                this.getData()
                this.isLoading = false
              }
            }
            catch(error) {
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
          myToken() {
            return this.$store.state.myToken
          }
        }
    }
</script>
