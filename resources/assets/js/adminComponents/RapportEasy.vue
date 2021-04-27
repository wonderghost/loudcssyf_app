<template>
    <div>
      <loading :active.sync="isLoading"
      :can-cancel="false"
      :is-full-page="fullPage"
      loader="bars"
      :opacity="1"
      color="#1e87f0"
      background-color="#fff"></loading>
      
        <!-- Error block -->
        <template v-if="errors.length">
          <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
          </div>
        </template>
      <!-- // -->

        <form @submit.prevent="sendRecrutementRapport()" class="uk-width-1-1@m">
              <div class="uk-margin-small uk-width-1-6@m">
                <label for="">Quantite Materiel</label>
                <input @keyup="ajusteQuantiteInput()" @change="ajusteQuantiteInput()" type="number" min="1" required v-model="formData.quantite_materiel" class="uk-input uk-border-rounded" value="">
              </div>
              <!-- SERIAL NUMBERS -->
              <div v-for="input in parseInt(formData.quantite_materiel)" :key="input" class="uk-margin-small uk-width-1-1@m uk-grid-small uk-border-rounded uk-padding-small" uk-grid style="border:solid 1px #ddd !important;">
                <div class="uk-width-1-4@m">
                  <label for="">Materiel - {{input}}</label>
                  <input type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[input-1]" required :placeholder="'Serial Number '+input">
                </div>
                <div class="uk-width-1-6@m">
                  <label for="">Formule</label>
                  <span class="uk-input uk-border-rounded">{{ formules[0].title }}</span>
                </div>
                <div class="uk-width-1-6@m">
                  <label for="">Duree</label>
                  <span class="uk-input uk-border-rounded">1</span>
                </div>
                <div class="uk-width-1-6@m">
                  <label for="">Debut</label>
                  <input v-model="formData.debut[input-1]" type="date" class="uk-input uk-border-rounded">
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
                quantite_materiel : 0,
                vendeurs : "",
                date : "",
                montant_ttc : 0,
                promo_id : "",
                serial_number : [""],
                formule : 'EASY TV',
                debut : [""],
                duree : 1,
                options : [""],
                type_credit : "cga"
              },
              duree : [1,2,3,6,12,24],
              amount_ttc : [],
              currentF : "",
              currentO : "",
              errors : []
            }
        },
        watch : {
          'formData.quantite_materiel'  : 'ajusteQuantiteInput'
        },
        methods : {
          sendRecrutementRapport : async function () {
            try {
                this.isLoading = true
                this.formData._token = this.myToken
                this.formData.promo_id = this.promoId
                this.formData.vendeurs = this.rappVendeur
                this.formData.date = this.rappDate

                if(this.typeUser == 'admin') {
                    var response = await axios.post('/admin/send-rapport/recrutement-easy',this.formData)
                } else {
                    var response = await axios.post('/user/send-rapport/recrutement-easy',this.formData)
                }
                
                if(response.data == 'done') {
                  Object.assign(this.$data,this.$options.data())
                  this.isLoading = false
                  alert("Success !")
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
          },
          ajusteQuantiteInput : function () {
            try {
                var diff = this.formData.quantite_materiel
                var loop = diff * (-1)
                if(diff < 0) {
                  for(var i = 0; i < loop ; i++) {
                    this.formData.formule.pop()
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
                ttc_montant = this.formData.quantite_materiel * 190000
                this.formData.montant_ttc = ttc_montant
            } catch(error) {
                alert(error)
            }
          }
        },
        computed : {
          myToken() {
            return this.$store.state.myToken
          },
          typeUser() {
            return this.$store.state.typeUser
          },
          formules() {
            return this.$store.state.formulesList.filter((f) => {
              return f.nom == 'EASY TV'
            })
          },
        }
    }
</script>
