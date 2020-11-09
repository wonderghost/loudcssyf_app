<template>
<div class="uk-container uk-container-large">
  <loading :active.sync="isLoading"
      :can-cancel="false"
      :is-full-page="fullPage"
      loader="bars"
      :opacity="1"
      color="#1e87f0"
      background-color="#fff"></loading>

      <h3>Nouvel Commande</h3>
      <hr class="uk-divider-small">

      <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit CGA</a></li>
        <template v-if="typeUser == 'v_standart'" id="">
          <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit REX</a></li>
          <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">AFROCASH GROSSISTE</a></li>
      </template>
      </ul>
      <!-- Erreor block -->
      <template v-if="errors.length">
        <div class="uk-alert-danger uk-border-rounded uk-width-1-1@m" v-for="(error,index) in errors" :key="index" uk-alert>
          <a href="#" class="uk-alert-close" uk-close></a>
          <p>{{error}}</p>
        </div>
      </template>
       <template v-if="!remboursementPromoState">
        <ul class="uk-switcher uk -margin">
            <!-- ENVOI DE COMMANDE MATERIEL -->
            <li>
              <!-- COMMANDE MATERIEL -->
              <form @submit.prevent="sendCommandMaterial()">

              <div class="uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
                <div>
                    <!-- <label uk-tooltip="">Kit Complet</label> -->
                    <label for="">Article(s)</label>
                    <select @change="getInfosByCommandMat()" v-model="commandDefaultValue" class="uk-select uk-border-rounded">
                      <option value="none">--Choisissez la commande --</option>
                      <option v-for="(cm,index) in commandParameters" :key="index" :value="cm.id">{{cm.name}}</option>
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
            </li>
            <!-- // -->
          <li>
            <!-- COMMANDE CREDIT CGA -->
            <div class="uk-grid-small" uk-grid>
              <div class="uk-width-1-3@m">
                <form @submit.prevent="sendCommandCga()">
                  <div class="uk-margin-small">
                    <label for="">Montant</label>
                    <input type="number" v-model="formDataCga.montant" class="uk-input uk-border-rounded" placeholder="Montant">
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
              <form @submit.prevent="sendCommandAfrocash()">
                <div class="uk-width-1-3@m">
                  <div class="uk-margin-small">
                    <label for="">Montant</label>
                    <input type="number" class="uk-input uk-border-rounded"  v-model="formDataAfrocash.montant">
                  </div>
                  <div class="uk-margin-small">
                    <label for="">Numero recu</label>
                    <input type="text" class="uk-input uk-border-rounded" v-model="formDataAfrocash.numero_recu" >
                  </div>
                  <!-- <div class="uk-margin-small">
                    <input type="file"  @change="fileUpload">
                  </div> -->
                <button type="submit" class="uk-button uk-button-small uk-border-rounded uk-button-primary">validez</button>
              </div>
            </form>
            </li>
          </template>
        </ul>
       </template>   
       <template v-else>
         <div class="uk-alert-danger uk-border-rounded" uk-alert>
            <p class="uk-text-center">
              Veuillez proceder a la regularisation promo avant de pouvoir continuer  , en cliquant sur <i class="material-icons ">payment</i>
              <br> en haut de votre ecran!
            </p>
         </div>
       </template>

</div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
      components : {
        Loading
      },
      created () {
        
      },
        mounted() {
          UIkit.offcanvas($("#side-nav")).hide();
          this.getData()
          this.getInfosMaterial()
        },
        data () {
          return {
            // 
            commandDefaultValue : "none",
            commandParameters : [],
            // 
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
              promo_id : ""
            },
            errors : [],
            success : "",
            formDataCga : {
              _token : "",
              montant : ""
            },
            formDataAfrocash : {
              _token : "",
              montant : 0,
              numero_recu : "",
              piece_jointe : ""
            },
            promo : {},
            promoState : false,
            remboursementPromoState : false
          }
        },
        methods : {
          testRemboursementAfterPromo : async function () {
            try {
                let response = await axios.get('/user/promo/infos-remboursement')
                
                if(response.data == false) {
                    this.remboursementPromoState = true
                } else {
                    this.remboursementPromoState = false
                }

                if(this.typeUser == 'v_standart') {
                  this.remboursementPromoState = false
                }
            } catch(error) {
                alert(error)
            }
          },
          // Initialisation des informations du components
          getData : async function () {
            try {
              let response = await axios.get('/user/new-command/get-data')
              if(response) {
                this.commandParameters = response.data
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
          getInfosMaterial : async function () {
            try {
              this.isLoading = true
              // 

              let response  = await axios.get('/user/promo/list')
              this.testRemboursementAfterPromo()
              if(response.data == 'fail') {
                // la promo n'existe pas
                this.promoState = false
              } else {
                  this.promoState = true
                  this.promo = response.data
                  this.formData.promo_id = this.promo.id
              }
              this.isLoading = false
            } catch (e) {
              alert(e)
            }
          },
          chooseQuantite : function () {
            this.montantTtc = parseInt(this.formData.quantite) * this.material.ttc
            this.montantTtcSubv = parseInt(this.formData.quantite) * this.material.subvention
            this.material.parabole_du = parseInt(this.formData.quantite) - (this.material.migration - this.material.compense)

            if(this.promoState) {
              // la promo est active
              if(this.typeUser == 'v_da') {
                  // distributeur agree
                  this.formData.prix_achat = parseInt(this.formData.quantite) * ((this.material.prix_vente - (this.marge.ht)) - this.promo.subvention)
              } else {
                  // vendeurs standart
                  this.formData.prix_achat = parseInt(this.formData.quantite) * (this.material.prix_vente - this.promo.subvention)
              }
            } else {
                // la promo est inactive
                if(this.typeUser == 'v_da') {
                  this.formData.prix_achat = parseInt(this.formData.quantite) * (this.material.prix_vente - (this.marge.ht))
                } 
                else {
                  this.formData.prix_achat = parseInt(this.formData.quantite) * this.material.prix_vente
                }
            }

          },
          sendCommandMaterial : async function () {
            // this.isLoading = true
            try {
              this.formData._token = this.myToken
              this.formData.reference_material = this.material.reference
              let response = await axios.post('/user/new-command/material',this.formData)

              if(response && response.data == 'done') {
                this.formData.quantite = ""
                this.isLoading = false
                alert("Success !")
                this.$router.push('/command/list')
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
                alert("Success !")
                Object.assign(this.$data,this.$options.all())
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
          },
          fileUpload : function (e) {
            this.formDataAfrocash.piece_jointe = e.target.files[0]
          },
          sendCommandAfrocash : async function () {
            this.isLoading = true
            this.formDataAfrocash._token = this.myToken
            var form = new FormData()
            form.append('montant',this.formDataAfrocash.montant)
            form.append('numero_recu',this.formDataAfrocash.numero_recu)
            // form.append('piece_jointe',this.formDataAfrocash.piece_jointe)
            form.append('_token',this.myToken)

            try {
              let response = await axios.post('/user/new-command/afrocash-sg',form,{
                headers : {
                  'content-type' : 'multipart/form-data'
                }
              })
              if(response.data == 'done') {
                this.isLoading = false
                alert("Success !")
                Object.assign(this.$data,this.$options.all())
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
