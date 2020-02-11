<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>
    <template id="">
    <div class="uk-grid-small" uk-grid>

      <div class="uk-width-1-6@m">
        <label for="">Comission Totale</label>
        <input type="text" name="" :value="commission | numFormat" class="uk-input uk-border-rounded uk-text-center uk-text-lead" disabled>
      </div>
    </div>
  </template>

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a @click="typeRapp = 'recrutement' , payComission = false"  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Recrutement</a></li>
        <li><a @click="typeRapp = 'reabonnement', payComission = false"  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Reabonnement</a></li>
        <li><a @click="typeRapp = 'migration' , payComission = false" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Migration</a></li>
        <li><a @click="payComission = true" v-if="typeUser == 'v_da' || typeUser == 'v_standart'" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Paiement Comission</a></li>
    </ul>
    <template v-if="!payComission" id="">
      <div class="">
        <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
          <thead>
            <tr>
              <th>Date</th>
              <th>Vendeur</th>
              <th>Type</th>
              <th>Credit</th>
              <th>Quantite</th>
              <th>Montant Ttc</th>
              <th>Commission</th>
              <th>Promo</th>
              <th>Paiement Commission</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rap in rapportVentes.slice(start,end)">
              <td>{{rap.date}}</td>
              <td>{{rap.vendeurs}}</td>
              <td>{{rap.type}}</td>
              <td>{{rap.credit}}</td>
              <td>{{rap.quantite}}</td>
              <td>{{rap.montant_ttc}}</td>
              <td>{{rap.commission}}</td>
              <td>{{rap.promo}}</td>
              <td>{{rap.paiement_commission}}</td>
            </tr>
          </tbody>
        </table>
        <ul class="uk-pagination uk-flex uk-flex-center">
          <li> <span> Page : {{currentPage}} </span> </li>
          <li> <button type="button" @click="previousPage()" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default" name="button"> <span uk-pagination-previous></span> Previous</button> </li>
          <li> <button type="button" @click="nextPage()" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default" name="button"> Suivant <span uk-pagination-next></span> </button> </li>
        </ul>
      </div>
    </template>
    <template v-if="typeUser == 'v_da' || typeUser == 'v_standart' " id="">
      <div v-if="payComission" class="uk-grid-small uk-grid-divider" uk-grid>
        <div  class="uk-width-1-3@m">
          <h3 class="">Demandez un paiement de comission</h3>
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
              <p>Demande de comission envoyee :-)</p>
            </div>
          </template>
          <p class="uk-text-right">
            <form>
              <div class="uk-alert-info uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                <p>Confirmez en tapant votre mot de passe pour envoyer la demande de paiement !</p>
              </div>
              <div class="uk-margin-small">
                <label for="">Le montant total de votre comission est de :</label>
                <input type="text" name="" :value="commission | numFormat" disabled class="uk-input uk-border-rounded uk-text-center uk-text-lead">
              </div>
              <div class="uk-margin-small">
                <label for="">Confirmez le mot de passe</label>
                <input v-model="passwordConfirm" type="password" name="" value="" class="uk-input uk-border-rounded" autofocus placeholder="Entrez votre mot de passe ici ...">
              </div>
              <button @click="sendPayComission()" type="button" name="button" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-text-capitalize">Envoyez <span uk-icon="icon : check"></span> </button>
            </form>
          </p>
        </div>
        <div class="uk-width-2-3@m">
          <h3>Toutes les demandes</h3>
          <table class="uk-table uk-table-small uk-table-hover uk-table-striped uk-table-divider uk-table-responsive">
            <thead>
              <tr>
                <th>Du</th>
                <th>Au</th>
                <th>Total</th>
                <th>Status</th>
                <th>Vendeurs</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="pay in payComissionList">
                <td>{{pay.du}}</td>
                <td>{{pay.au}}</td>
                <td>{{pay.total}}</td>
                <td class="uk-text-danger" v-if="pay.status == 'unvalidated'">{{pay.status}}</td>
                <td class="uk-text-success" v-else>{{pay.status}}</td>
                <td>{{pay.vendeurs}}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
      created () {
        this.isLoading = true
      },
        mounted() {
          this.getRapportVente()
          this.getPayComissionList()
        },
        components : {
          Loading
        },
        data () {
          return {
            isLoading : false,
            fullPage : true,
            typeRapp : 'recrutement',
            start : 0,
            end : 10,
            currentPage : 1,
            commission : 0,
            passwordConfirm : "",
            errors : [],
            success : false,
            payComission : false
          }
        },
        methods : {
          getRapportVente : async function () {
            try {
              if(this.typeUser == 'admin') {
                var response = await axios.get('/admin/rapport/all')
                var responseCom = await axios.get('/admin/rapport/commission-total')
              } else {
                var response = await axios.get('/user/rapport-ventes/all')
                var responseCom = await axios.get('/user/rapport/total-commission')
              }
              this.isLoading = false
              this.commission = responseCom.data
              this.$store.commit('setRapportVente',response.data)
            } catch (error) {
              alert(error)
            }
          },
          getPayComissionList : async function () {
            try {
              let response = await axios.get('/user/rapport-ventes/get-pay-commission')
              this.$store.commit('setPayComissionList',response.data)
            } catch (error) {
              alert(error)
            }
          }
          ,
          nextPage : function () {
            if(this.rapportVentes.length > this.end) {
              let ecart = this.end - this.start
              this.start = this.end
              this.end += ecart
              this.currentPage++
            }
          },
          previousPage : function () {
            if(this.start > 0) {
              let ecart = this.end - this.start
              this.start -= ecart
              this.end -= ecart
              this.currentPage--
            }
          },
          sendPayComission : async function () {
            this.isLoading = true
            try {
              let response = await axios.post('/user/rapport-ventes/pay-commission',{
                _token : this.myToken,
                commission_total : this.commission,
                password_confirm : this.passwordConfirm
              })
              if(response.data == 'done') {
                this.isLoading = false
                this.success = true
                this.getRapportVente()
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
          }
        },
        computed : {
          rapportVentes () {
            return this.$store.state.rapportVentes.filter( (rapport) => {
              return rapport.type === this.typeRapp
            })
          },
          typeUser () {
            return this.$store.state.typeUser
          },
          myToken () {
            this.$store.state.myToken
          },
          payComissionList () {
            return this.$store.state.payComissionList
          }
        }
    }
</script>
