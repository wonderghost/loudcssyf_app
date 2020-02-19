<template>
<div class="">
  <loading :active.sync="isLoading"
      :can-cancel="false"
      :is-full-page="fullPage"></loading>

      <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit CGA</a></li>
        <template v-if="typeUser == 'v_standart'" id="">
          <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit REX</a></li>
          <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">AFROCASH GROSSISTE</a></li>
      </template>
      </ul>
      <!-- Erreor block -->
            <template v-if="errors.length" v-for="error in errors">
            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
              <a href="#" class="uk-alert-close" uk-close></a>
              <p>{{error}}</p>
            </div>
          </template>
          <template id="" v-if="success">
            <div class="uk-alert-success uk-border-rounded uk-box-shadow-hover-small" uk-alert>
              <a href="#" class="uk-alert-close" uk-close></a>
              <p>{{success}}</p>
            </div>
          </template>
      <ul class="uk-switcher uk -margin">
          <!-- ENVOI DE COMMANDE MATERIEL -->
          <li>
            <!-- COMMANDE MATERIEL -->
            <form @submit.prevent="sendCommandMaterial()">

            <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
              <div>
                  <label uk-tooltip="">Kit Complet</label>
              </div>
              <div id="">
                <label>Qte <input min="1" required @keyup="chooseQuantite()" @change="chooseQuantite()" type="number" v-model="formData.quantite"  class="uk-input uk-border-rounded"   > </label>
              </div>
              <div>
                <label>Prix TTC (GNF)<input type="text" :value="material.ttc | numFormat"  disabled  class="uk-input uk-border-rounded" ></label>
              </div>
              <div>
                <label>HT (GNF)<input type="text" :value="material.ht | numFormat" disabled  class="uk-input uk-border-rounded" ></label>
              </div>
              <div>
                <label>TVA (18%) (GNF)<input type="text" :value="material.tva | numFormat" disabled  class="uk-input uk-border-rounded" ></label>
              </div>
              <div>
                <label>Montant TTC (GNF)<input type="text" :value="montantTtc | numFormat"  disabled  class="uk-input uk-border-rounded" ></label>
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
          </li>
          <!-- // -->
        <li>
          <!-- COMMANDE CREDIT CGA -->
          <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-3@m">
              <form @submit.prevent="sendCommandCga()">
                <div class="uk-margin-small">
                  <label for="">Montant</label>
                  <input type="number" v-model="formDataCga.montant" class="uk-input uk-border-rounded">
                </div>
                <button type="submit"  class="uk-button uk-button-small uk-button-primary uk-border-rounded">validez</button>
              </form>
            </div>
          </div>
          <!-- // -->
        </li>
        <template id="" v-if="typeUser == 'v_standart'">
          <li>
            <!-- COMMAND CREDIT REX -->
          </li>
          <li>
            <!-- COMMAND AFROCASH GROSSISTE -->

          </li>
        </template>
      </ul>

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
          this.getInfosMaterial()
        },
        data () {
          return {
            isLoading : false,
            fullPage : true,
            material : {},
            marge : {},
            subvention : {},
            montantTtc : 0,
            montantTtcSubv : 0,
            formData : {
              quantite : 0,
              prix_achat : 0,
              _token : "",
              reference_material : ""
            },
            errors : [],
            success : "",
            formDataCga : {
              _token : "",
              montant : 0
            }
          }
        },
        methods : {
          getInfosMaterial : async function () {
            try {
              let response = await axios.get('/user/new-command/get-infos-material')
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
            } catch (e) {
              alert(e)
            }
          },
          chooseQuantite : function () {
            this.montantTtc = parseInt(this.formData.quantite) * this.material.ttc
            this.montantTtcSubv = parseInt(this.formData.quantite) * this.material.subvention
            this.material.parabole_du = parseInt(this.formData.quantite) - (this.material.migration - this.material.compense)
            if(this.typeUser == 'v_da') {
              this.formData.prix_achat = parseInt(this.formData.quantite) * (this.material.prix_vente - (this.marge.ht))
            } else {
              this.formData.prix_achat = parseInt(this.formData.quantite) * this.material.prix_vente
            }
          },
          sendCommandMaterial : async function () {
            this.isLoading = true
            try {
              this.formData._token = this.myToken
              this.formData.reference_material = this.material.reference
              let response = await axios.post('/user/new-command/material',this.formData)
              if(response.data == 'done') {
                this.success = "Votre commande a ete envoye :-)"
                this.isLoading = false
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
          },
          sendCommandCga : async function () {
            this.isLoading = true
            try {
              this.formDataCga._token = this.myToken
              let response = await axios.post('/user/new-command/cga',this.formDataCga)
              if(response.data == 'done') {
                this.isLoading = false
                UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Votre commande a ete envoye :-)</div>")
                  .then(function () {
                    location.reload()
                  })
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
          typeUser () {
            return this.$store.state.typeUser
          },
          myToken () {
            return this.$store.state.myToken
          }
        }

    }
</script>
