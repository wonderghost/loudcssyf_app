<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>


    <!-- modal details rapports -->
    <detail-rapport :rapport="rappDetails"></detail-rapport>
    <!-- <template>
      <div id="modal-detail-rapport" uk-modal="esc-close : false ; bg-close : true;">
        <div class="uk-modal-dialog">
          <div class="uk-modal-header">
            <div class="uk-alert-info" uk-alert>
              Materiels activ&eacute;  dans le rapport du : <span id="date-rap" class="uk-text-bold"></span>
            </div>
          </div>
          <div class="uk-modal-body">
            <div class="uk-grid-small" uk-grid>
              <div v-for="s in detailSerials" class="uk-width-1-2@m">
                <span class="uk-input uk-border-rounded">{{s.serial_number}}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template> -->
    <!-- // -->

    <template id="">
    <div class="uk-grid-small" uk-grid>
      <div class="uk-width-1-6@m">
        <label for="">Comission Totale</label>
        <input type="text" name="" :value="commission | numFormat" class="uk-input uk-border-rounded uk-text-center uk-text-lead" disabled>
      </div>
    </div>
  </template>

  <template v-if="typeUser == 'admin'" id="">
    <div id="modal-abort-rapport" uk-modal="esc-close : false ; bg-close : false;">
      <div class="uk-modal-dialog">
          <div class="uk-modal-header">
              <div class="uk-alert-warning" uk-alert>
                <p>
                  <span uk-icon="icon : warning"></span> Vous etes sur de vouloir annuler le rapport du : <span class="uk-text-bold">{{activRapport.date}}</span>
                  pour : <span class="uk-text-bold">{{activRapport.vendeurs}}</span> en : <span class="uk-text-bold">{{activRapport.type}}</span> ?
                </p>
              </div>
          </div>
          <div class="uk-modal-body">
            <!-- Error block -->
              <template v-if="errors.length" v-for="error in errors">
              <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                <a href="#" class="uk-alert-close" uk-close></a>
                <p>{{error}}</p>
              </div>
            </template>

            <form @submit.prevent="abortRapport()">
              <div class="uk-margin-small">
                <label for="">Confirmez le mot de passe Administrateur</label>
                <input type="password" v-model="abortRapportFormData.password_confirmation" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe" autofocus>
              </div>
              <button type="submit" class="uk-button uk-button-small uk-button-primary uk-text-capitalize uk-border-rounded">Validez <span uk-icon="icon : check"></span> </button>
            </form>
          </div>
          <div class="uk-modal-footer">
            <button type="button" class="uk-modal-close uk-button uk-button-small uk-button-danger uk-text-capitalize uk-border-rounded">Fermer</button>
          </div>
      </div>
  </div>
  </template>

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a @click="typeRapp = 'recrutement' , payComission = false"  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Recrutement</a></li>
        <li><a @click="typeRapp = 'reabonnement', payComission = false"  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Reabonnement</a></li>
        <li><a @click="typeRapp = 'migration' , payComission = false" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Migration</a></li>
        <li><a @click="payComission = true" v-if="typeUser == 'v_da'" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Paiement Comission</a></li>
    </ul>

    <template v-if="!payComission" id="">

      <template id="">
        <div class="uk-grid-small" uk-grid>
          <div class="uk-width-1-4@m">
            <label for="">Commission Cumulee</label>
            <span class="uk-input uk-text-center uk-text-bold uk-border-rounded">{{comissionCummule | numFormat}}</span>
          </div>
        </div>
        <div class="uk-grid-small" uk-grid>

          <div class="uk-width-1-6@m">
            <label for=""> <span uk-icon="icon : info"></span> Etat</label>
            <select class="uk-select uk-border-rounded" v-model="stateRapp">
              <option value="">Tous</option>
              <option value="unaborted">valide</option>
              <option value="aborted">invalide</option>
            </select>
          </div>
          <div class="uk-width-1-6@m">
            <label for=""><span uk-icon="icon : tag"></span> Promo</label>
            <select class="uk-select uk-border-rounded">
              <option value="">Tous</option>
              <option value="">Hors Promo</option>
              <option value="">En Promo</option>
            </select>
          </div>
          <div class="uk-width-1-6@m">
            <label for=""><span uk-icon="icon : credit-card"></span> Paiement</label>
            <select class="uk-select uk-border-rounded" v-model="payFilter">
              <option value="">Tous</option>
              <option value="paye">Paye</option>
              <option value="non_paye">Impaye</option>
            </select>
          </div>
          <div class="uk-width-1-3@m">
            <div class="uk-grid-small" uk-grid>
              <div class="uk-width-1-2@m">
                <label for=""><span uk-icon="icon : calendar"></span> Du</label>
                <input type="date" class="uk-input uk-border-rounded" v-model="filterDate.debut">
              </div>
              <div class="uk-width-1-2@m">
                <label for=""><span uk-icon="icon : calendar"></span> Au</label>
                <input type="date" class="uk-input uk-border-rounded" v-model="filterDate.fin">
              </div>
            </div>
          </div>

          <div v-if="typeUser == 'admin' || typeUser == 'controleur'" class="uk-width-1-6@m">
            <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
            <select class="uk-select uk-border-rounded" v-model="filterUser">
              <option value="">Tous les vendeurs</option>
              <option v-for="u in users" :key="u.username" :value="u.localisation"> {{u.localisation}} </option>
            </select>
          </div>
        </div>
      </template>

      <div class="">
        <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
          <thead>
            <tr>
              <th>Date</th>
              <th>Vendeur</th>
              <th>Type</th>
              <th>Credit</th>
              <th>Quantite</th>
              <th>Ttc</th>
              <th>Commission</th>
              <th>Promo</th>
              <th>Paiement Commission</th>
              <th>-</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rap in rappWithDate.slice(start,end)" :key="rap.id_rapport">
              <td>{{rap.date}}</td>
              <td>{{rap.vendeurs}}</td>
              <td>{{rap.type}}</td>
              <td>{{rap.credit}}</td>
              <td>{{rap.quantite}}</td>
              <td>{{rap.montant_ttc| numFormat}}</td>
              <td>{{rap.commission | numFormat}}</td>
              <td>{{rap.promo}}</td>
              <td>{{rap.paiement_commission}}</td>
              <td>
                <template v-if="typeUser == 'admin' && rap.paiement_commission == 'non_paye' && rap.state == 'unaborted'" id="">
                  <button @click="activRapport = rap" uk-toggle="target : #modal-abort-rapport" type="button" class="uk-button uk-button-small uk-border-rounded uk-button-danger uk-text-capitalize">Annuler</button>
                </template>
                <template v-if="rap.state == 'aborted'">
                  <span class="uk-alert-danger">invalide</span>
                </template>

                <template v-if="rap.state == 'unaborted' && (rap.type == 'recrutement' || rap.type == 'migration')">
                  <button @click="getDetailsRapport(rap)" class="uk-button uk-button-small uk-border-rounded uk-button-default uk-text-capitalize">Details</button>
                </template>
              </td>
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
    <template v-if="typeUser == 'v_da'" id="">
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
import datepicker from 'vue-date-picker'

    export default {
      created () {
        this.isLoading = true
      },
        mounted() {
          this.getRapportVente()
          this.showRapport = this.rappWithUser
          if(this.typeUser == 'admin') {
            this.allUsers()
          }

          if(this.typeUser == 'v_da') {
            this.getPayComissionListForVendeur()
          }
          //
        },
        components : {
          Loading,
          datepicker
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
            payComission : false,
            activRapport : {},
            abortRapportFormData : {
              _token : "",
              password_confirmation : "",
              id_rapport : ""
            },
            stateRapp : "",
            filterUser : "",
            users : [],
            filterDate : {
              debut : "",
              fin : ""
            },
            payFilter : "",
            rappDetails : {}
          }
        },
        methods : {
          getDetailsRapport : async function (rap) {
            try {
              this.rappDetails = rap
              UIkit.modal("#modal-detail-rapport").show()
                // this.rapDetailsData._token = this.myToken
                // this.rapDetailsData.rapId = rap.id
                // $("#date-rap")[0].innerText = rap.date

                // let response = await axios.post('/user/rapport-ventes/get-details',this.rapDetailsData)
                // this.detailSerials = response.data
            } catch(error) {
                alert(error)
            }
          },
          allUsers : async function () {
            try {
              let response = await axios.get('/admin/all-vendeurs')
              this.users = response.data
            } catch (e) {
                alert(e)
            }
          },
          abortRapport : async function () {
            this.isLoading = true
            UIkit.modal($("#modal-abort-rapport")).hide()
            this.abortRapportFormData._token = this.myToken
            this.abortRapportFormData.id_rapport = this.activRapport.id
            try {
              let response = await axios.post('/admin/rapport/abort',this.abortRapportFormData)
              if(response.data == 'done') {
                this.isLoading = false
                UIkit.modal.alert("<div class='uk-alert-success' uk-alert>un Rapport Annule :-(</div>")
                  .then(function () {
                    location.reload()
                  })
              }
            } catch (error) {
              UIkit.modal($("#modal-abort-rapport")).show()
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
          getRapportVente : async function () {
            try {
              if(this.typeUser == 'admin') {
                var response = await axios.get('/admin/rapport/all')
                var responseCom = await axios.get('/admin/rapport/commission-total')
              }else if(this.typeUser == 'controleur'){
                var response = await axios.get('/user/rapport/all')
                var responseCom = await axios.get('/user/rapport/commission-total')
              }else {
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
          getPayComissionListForVendeur : async function () {
            try {
              let response = await axios.get('/user/rapport-ventes/get-pay-commission')
              this.$store.commit('setPayComissionList',response.data)
            } catch (error) {
              alert(error)
            }
          }
          ,
          nextPage : function () {
            if(this.rappWithDate.length > this.end) {
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
          comissionCummule() {
            var som = 0
            this.rappWithDate.forEach( r => {
              som = som + r.commission
            })
            return som
          },
          rappWithDate() {
            return this.rappWithPay.filter( (rapport) => {
              if(this.filterDate.debut !== "" && this.filterDate.fin !== "") {
                let debut = new Date(this.filterDate.debut)
                let fin = new Date(this.filterDate.fin)
                let rappDate = new Date(rapport.date)
                return (rappDate >= debut && rappDate <= fin)
              }
              else {
                return true
              }
            })
          },
          rappWithPay() {
            return this.rappWithUser.filter( (rapport) => {
              if(this.payFilter == "") {
                return true
              }
              else {
                return rapport.paiement_commission == this.payFilter
              }
            })
          }
          ,
          rappWithUser () {
            return this.stateRapportVentes.filter( (rapport) => {
              return rapport.vendeurs.match(this.filterUser) 
            })
          }
          ,
          rapportVentes () {
            return this.$store.state.rapportVentes.filter( (rapport) => {
              return rapport.type === this.typeRapp
            })
          },
          stateRapportVentes () {
            return this.rapportVentes.filter( (rapport) => {
              if(this.stateRapp == "") {
                return true
              }
              else {
                return rapport.state === this.stateRapp
              } 
            })
          },
          typeUser () {
            return this.$store.state.typeUser
          },
          myToken () {
            return this.$store.state.myToken
          },
          payComissionList () {
            return this.$store.state.payComissionList
          }
        }
    }
</script>
