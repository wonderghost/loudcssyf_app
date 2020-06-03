<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <!-- Error block -->
        <template v-if="errors.length" v-for="error in errors">
          <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
          </div>
        </template>
      <!-- // -->

        <form @submit.prevent="sendReabonnementRapport()" class="uk-width-1-1@m">
              <div class="uk-margin-small uk-width-1-6@m">
                <label for="">Quantite Materiel</label>
                <input type="number" min="1" required v-model="formData.quantite_materiel" class="uk-input uk-border-rounded" value="">
              </div>
              <!-- SERIAL NUMBERS -->
              <div v-for="input in parseInt(formData.quantite_materiel)" :key="input" class="uk-margin-small uk-width-1-1@m uk-grid-small uk-border-rounded uk-padding-small" uk-grid style="border:solid 1px #ddd !important;">
                <div class="uk-width-1-1@m uk-grid-small" v-if="upgradeState[input-1]" uk-grid>
                  
                  <div class="uk-width-1-6@m">
                    <label for="">Ancienne formule</label>
                    <select class="uk-select uk-border-rounded">
                      <option value=""></option>
                    </select>
                  </div>
                </div>
                <div class="uk-width-1-1@m uk-grid-small" uk-grid>
                  <div class="uk-width-1-6@m">
                    <label for="">Materiel - {{input}}</label>
                    <input type="text" @blur="searchSerialDebut(formData.serial_number[input-1],input)" class="uk-input uk-border-rounded" v-model="formData.serial_number[input-1]" required :placeholder="'Serial Number '+input">
                  </div>
                  <div class="uk-margin-medium-top">
                    <label for="">
                      Upgrade
                      <input v-model="upgradeState[input-1]" @change="upgradeStateAction(formData.serial_number[input-1],upgradeState[input-1],input-1)" type="checkbox" class="uk-checkbox">
                    </label>
                  </div>
                  <div class="uk-width-1-6@m">
                    <label for="">Formule</label>
                    <select @change="calculMontantTtc()" v-model="formData.formule[input-1]" class="uk-select uk-border-rounded">
                      <option value="">--Formule--</option>
                      <option v-for="f in formules" :key="f.nom" :data-price="f.prix" :value="f.nom">{{f.nom}}</option>
                    </select>
                  </div>
                  <div class="uk-width-1-6@m">
                    <label for="">Options</label>
                    <select @change="calculMontantTtc()" v-model="formData.options[input-1]" class="uk-select uk-border-rounded">
                      <option value="">--Option--</option>
                      <option v-for="o in options" :key="o.nom" :value="o.nom">{{o.nom}}</option>
                    </select>
                  </div>
                  <div class="uk-width-1-6@m">
                    <label for="">Duree</label>
                    <select @change="calculMontantTtc()" v-model="formData.duree[input-1]" class="uk-select uk-border-rounded">
                      <option value="">--duree--</option>
                      <option v-for="d in duree" :key="d" :value="d">{{d}}</option>
                    </select>
                  </div>
                  <div class="uk-width-1-6@m">
                    <label for="">Debut : </label>
                    <span class="uk-text-bold">{{debutSuggest}}</span>
                    <input v-model="formData.debut[input-1]" :id="'debut-'+input" type="date" class="uk-input uk-border-rounded">
                  </div>
                </div>
              </div>
              <!-- // -->
              <div class="uk-margin-small uk-width-1-6@m">
                <label for=""> <span uk-icon="icon : credit-card"></span> Montant TTC</label>
                <span class="uk-input uk-text-bold uk-border-rounded">{{formData.montant_ttc | numFormat}}</span>
              </div>
  					<button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
          </form>

    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        components : {
            Loading
        },
        props : {
            rappDate : String,
            rappVendeur : String,
            promoId : String
        },
        mounted() {
            
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                formData : {
                  _token : "",
                  quantite_materiel : 1,
                  vendeurs : "",
                  date : "",
                  montant_ttc : 0,
                  promo_id : "",
                  serial_number : [""],
                  formule : [""],
                  debut : [""],
                  duree : [""],
                  options : [""],
                  type_credit : "cga",
                  upgrade_data : [{
                    old_abonnement  : {},
                    new_abonnement : {}
                  }]
              },
              upgradeData : [],
              duree : [1,2,3,6,12,24],
              amount_ttc : [],
              currentF : "",
              currentO : "",
              errors : [],
              // 
              upgradeState : [false],
              debutSuggest : "",
              failState : [false]

            }
        },
        methods : {
          upgradeStateAction : async function (serial,stateUpgrade,index) {
            try {
                if(!stateUpgrade) {
                  return 0
                }
                let response = await axios.post('/user/check-serial-upgrade/',{
                  _token : this.myToken,
                  serial_number : serial
                })
                
                if(response.data == 'fail') {
                  // sortir une ligne vierge
                    this.failState[index] = true
                } else {
                    this.failState[index] = false
                }
            } catch(error) {
                alert(error)
            }
          },
          searchSerialDebut : async function (serial,index) {
            try {
                let response = await axios.post('/user/rapport/check-serial-debut-date',{
                  _token : this.myToken,
                  serial_materiel : serial
                })
                if(response.data) {
                  this.debutSuggest = response.data
                }
            } catch(error) {
                alert(error)
            }
          },
          calculMontantTtc : function () {
            try {
                var ttc_montant = 0
                var i = 0 
                this.formData.formule.forEach((f) => {
                  this.currentF = f
                  if(this.formData.options[i]) {
                    this.currentO = this.formData.options[i]
                    var tmp  = (parseFloat(this.currentFormule[0].prix) + parseFloat(this.currentOption[0].prix)) * parseInt(this.formData.duree[i])
                  } else {
                      if(this.formData.formule[i] && this.formData.duree[i]) {
                        var tmp = parseFloat(this.currentFormule[0].prix) * parseInt(this.formData.duree[i])
                      } else {
                        //  throw "Veuillez choisir une Formule et une duree!"
                      }
                  }
                  ttc_montant += tmp
                  i++
                })
                this.formData.montant_ttc = ttc_montant
            } catch(error) {
                alert(error)
            }
          },
          sendReabonnementRapport : async function () {
            try {
              this.isLoading = true
              this.formData._token = this.myToken
              this.formData.promo_id = this.promoId
              this.formData.vendeurs = this.rappVendeur
              this.formData.date = this.rappDate

              if(this.typeUser === 'admin') {
                  var response = await axios.post('/admin/send-rapport/reabonnement',this.formData)
              } else {
                  var response = await axios.post('/user/send-rapport/reabonnement',this.formData)
              }
              if(response.data == 'done') {
                  this.isLoading = false
                  UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Operation effectuee avec success !</div>")
                    .then(function () {
                      location.reload()
                    })
                }
            } catch(error) {
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
          currentOption() {
            return this.options.filter((o) => {
              return o.nom === this.currentO
            })
          },
          currentFormule() {
            return this.formules.filter((f) => {
              return f.nom === this.currentF
            })
          },
          myToken() {
              return this.$store.state.myToken
          },
          typeUser() {
              return this.$store.state.typeUser
          },
          formules() {
              return this.$store.state.formulesList
          },
          options() {
              return this.$store.state.optionsList
          }
        }
    }
</script>
