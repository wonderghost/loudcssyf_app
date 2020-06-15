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
                <input @keyup="ajusteQuantiteInput()" @change="ajusteQuantiteInput()" type="number" min="1" required v-model="formData.quantite_materiel" class="uk-input uk-border-rounded" value="">
              </div>
              <!-- SERIAL NUMBERS -->
              <div v-for="input in parseInt(formData.quantite_materiel)" :key="input" class="uk-margin-small uk-width-1-1@m uk-grid-small uk-border-rounded uk-padding-small" uk-grid style="border:solid 1px #ddd !important;">
               <!-- UPGRADE STATE DETAILS -->
                <div v-if="formData.upgrade[input-1] && upgradeDatas[input-1]" class="uk-width-1-1@m uk-grid-small" uk-grid>
                  <div class="uk-width-1-6@m">
                    <span  class="uk-input uk-border-rounded">
                      {{ upgradeDatas[input-1].serial_number }}
                    </span>
                  </div>
                  <div class="uk-width-1-6@m">
                    <span class="uk-input uk-border-rounded">
                      {{ upgradeDatas[input-1].formule_name }}
                    </span>
                  </div>
                  <div class="uk-width-1-6@m">
                    <span class="uk-input uk-border-rounded">
                      
                    </span>
                  </div>

                  <div class="uk-width-1-6@m">
                    <span class="uk-input uk-border-rounded">
                      {{ upgradeDatas[input-1].duree }}
                    </span>
                  </div>

                  <div class="uk-width-1-6@m">
                    <span class="uk-input uk-border-rounded">
                      {{ upgradeDatas[input-1].debut }}
                    </span>
                  </div>

                </div>

                <div v-if="formData.upgrade[input-1] && !upgradeDatas[input-1]" class="uk-width-1-1@m uk-grid-small" uk-grid>
                  <div class="uk-width-1-6@m">
                    <label for="">Materiel</label>
                    <span class="uk-input uk-border-rounded">
                      {{ formData.serial_number[input-1] }}
                    </span>
                  </div>

                  <div class="uk-width-1-6@m">
                    <label for="">Ancienne Formule</label>
                    <select @change="calculMontantTtc()" v-model="formData.old_formule[input-1]" class="uk-select uk-border-rounded">
                      <option value=""></option>
                      <option v-for="f in formules" :key="f.nom" :value="f.nom">{{f.nom}}</option>
                    </select>
                  </div>

                  <div class="uk-width-1-6@m">
                    <label for="">Duree</label>
                    <select @change="calculMontantTtc()" v-model="formData.old_duree[input-1]" class="uk-select uk-border-rounded">
                      <option value=""></option>
                      <option :value="d" v-for="d in duree" :key="d">{{ d }}</option>
                    </select>
                  </div>

                  <div class="uk-width-1-6@m">
                    <label for="">Debut</label>
                    <input v-model="formData.old_debut[input-1]" type="date" class="uk-input uk-border-rounded">
                  </div>
                </div>
               <!-- // -->

                <div class="uk-width-1-1@m uk-grid-small" uk-grid>
                  <div class="uk-width-1-6@m">
                    <label for="">Materiel - {{input}}</label>
                    <input @keyup="upgradeStateActive(input-1)" type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[input-1]" required :placeholder="'Serial Number '+input">
                  </div>
                  <div class="uk-margin-medium-top">
                    <label for="">
                      Upgrade
                      <input @change="upgradeStateActive(input-1)" v-model="formData.upgrade[input-1]"  type="checkbox" class="uk-checkbox">
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
                    <!-- <span class="uk-text-bold">{{debutSuggest[input-1]}}</span> -->
                    <input v-model="formData.debut[input-1]" :id="'debut-'+(input-1)" type="date" class="uk-input uk-border-rounded">
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
                  upgrade : [false],
                  upgradeData : [""],
                  old_formule : [""],
                  old_duree : [""],
                  old_debut : [""]
              },
              duree : [1,2,3,4,5,6,7,8,9,10,11,12,24],
              amount_ttc : [],
              currentF : "",
              currentO : "",
              currentOldFormule : "",
              errors : [],
              // 
              debutSuggest : [""],

              prix_formule : 0,
              duree_formule : 0
            }
        },
        methods : {
          upgradeStateActive : async function (index) {
            try {
              
              if(!this.formData.upgrade[index]) {
                
                Vue.set(this.formData.upgradeData,index,undefined)
                return 0
              }
              
              let response = await axios.post('/user/check-serial-upgrade/',{
                _token : this.myToken,
                serial_number : this.formData.serial_number[index]
              })
              
              if(response) {
                if(response.data != 'fail') {
                  Vue.set(this.formData.upgradeData,index,response.data)
                }
                else {
                  Vue.set(this.formData.upgradeData,index,undefined)
                }
                this.calculMontantTtc()
              }

            } catch(error) {
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
          // searchSerialDebut : async function (serial,index) {
          //   try {
          //       let response = await axios.post('/user/rapport/check-serial-debut-date',{
          //         _token : this.myToken,
          //         serial_materiel : serial
          //       })
          //       if(response.data) {
          //         this.debutSuggest[index] = response.data
          //       }
          //   } catch(error) {
          //       alert(error)
          //   }
          // },
          ajusteQuantiteInput : function () {
            try {
                var diff = this.formData.quantite_materiel - this.formData.formule.length
                var loop = diff * (-1)

                var diffSerial = this.formData.quantite_materiel - this.formData.serial_number.length
                var serialLoop = diffSerial * (-1)

                
                if(diff < 0) {
                  for(var i = 0; i < loop ; i++) {
                    this.formData.formule.pop()

                  }
                }

                if(diffSerial < 0) {
                  for(var k = 0 ; k < serialLoop ; k++) {
                    this.formData.serial_number.pop()
                  }
                }
                this.calculMontantTtc()
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

              if(this.formData.upgrade[i]) {

                // ABONNEMENT AVEC UPGRADE
                

                if(this.upgradeDatas[i]) {
                  //  les donnees existent dans le systeme
                   this.prix_formule = this.upgradeDatas[i].formule_prix
                   this.duree_formule = this.upgradeDatas[i].mois_restant
                } 
                else if(this.formData.old_formule[i]) {
                  //  les donnees n'existent pas dans le systeme
                  this.currentOldFormule = this.formData.old_formule[i]
                  this.prix_formule = this.currentOldFormulePrix[0].prix
                  this.duree_formule = this.formData.old_duree[i]

                  // Vue.set(this.formData.duree[i],i,this.formData.old_duree[i])
                  // Vue.set(this.formData.debut[i],i,this.formData.old_debut[i])

                }

                if(this.formData.options[i]) {
                  this.currentO = this.formData.options[i]
                  var tmp  = (parseFloat(this.currentFormule[0].prix) - parseFloat(this.prix_formule) + parseFloat(this.currentOption[0].prix)) * parseInt(this.duree_formule)
                } else {
                    if(this.formData.formule[i]) {
                      var tmp = (parseFloat(this.currentFormule[0].prix) - parseFloat(this.prix_formule)) * parseInt(this.duree_formule)
                    } else {
                      //  throw "Veuillez choisir une Formule et une duree!"
                    }
                }

              } 
              else {

                  // JUSTE UN SIMPLE ABONNEMENT

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
              }
              
                
                ttc_montant += tmp
                if(tmp < 0) {
                  this.formData.formule.pop()
                  throw "Veuillez choisir une formule superieure a l'ancienne"
                }
                i++

              })
              this.formData.montant_ttc = ttc_montant
            } catch(error) {
                alert(error)
            }
          },
          sendReabonnementRapport : async function () {
            try {
              // this.isLoading = true
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
          upgradeDatas() {
            return this.formData.upgradeData
          },
          currentOldFormulePrix() {
            return this.formules.filter((f) => {
              return f.nom === this.currentOldFormule
            })
          },
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
