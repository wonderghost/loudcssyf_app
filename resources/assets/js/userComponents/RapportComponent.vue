<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>


    <h3>Tous les Rapports</h3>
    <hr class="uk-divider-small">

    <download-to-excel :data-to-export="rapportList" :data-fields="field_export" file-name="rapport-vente"></download-to-excel>

    <!-- EXPORT RAPPORT DATA -->
    <nav class="" uk-navbar>
      <div class="uk-navbar-left">
        <ul class="uk-navbar-nav">
          <li class=""><router-link to="/rapport/list/export">Exporter</router-link></li>
        </ul>
      </div>
    </nav>
    <!-- /// -->

    <!-- paiement comission for user -->
    <template v-if="typeUser == 'v_da'">
      <ul uk-accordion>
        <li>
            <!-- <a class="uk-accordion-title" href="#">Paiement Comission</a> -->
            <button class="uk-accordion-title uk-button uk-button-small uk-border-rounded uk-text-capitalize">Se faire payer</button>
            <div class="uk-accordion-content uk-section-muted uk-section uk-padding-small">
                
                  <div class="uk-grid-small uk-grid-divider" uk-grid>
                    <div  class="uk-width-1-3@m">
                      <h3 class="">Demandez un paiement de comission</h3>
                      <!-- Erreor block -->
                      <template v-if="errors.length">
                        <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
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
                          <tr v-for="(pay,index) in payComissionList" :key="index">
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
                
            </div>
        </li>
      </ul>
    </template>

    

    <!-- modal details rapports -->
    <detail-rapport :rapport="rappDetails" :rapport-infos="rappInfos"></detail-rapport>

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
              <template v-if="errors">
              <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
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

    
  <template v-if="!payComission" id="">

    <template id="">
      <div class="uk-grid-small" uk-grid>
        <div class="uk-width-1-6@m">
          <label for="">Type</label>
          <select @change="filterRequest()" v-model="filterData.type" class="uk-select uk-border-rounded">
            <option value="all">Tous</option>
            <option value="recrutement">Recrutement</option>
            <option value="reabonnement">Reabonnement</option>
            <option value="migration">Migration</option>
          </select>
        </div>
        <div class="uk-width-1-6@m">
          <label for=""> <span uk-icon="icon : info"></span> Etat</label>
          <select @change="filterRequest()" v-model="filterData.state" class="uk-select uk-border-rounded">
            <option value="all">Tous</option>
            <option value="unaborted">valide</option>
            <option value="aborted">invalide</option>
          </select>
        </div>
        <div class="uk-width-1-6@m">
          <label for=""><span uk-icon="icon : tag"></span> Promo</label>
          <select @change="filterRequest()" v-model="filterData.promoState" class="uk-select uk-border-rounded">
            <option value="all">Tous</option>
            <option value="hors_promo">Hors Promo</option>
            <option value="en_promo">En Promo</option>
          </select>
        </div>
        <div class="uk-width-1-6@m">
          <label for=""><span uk-icon="icon : credit-card"></span> Paiement</label>
          <select @change="filterRequest()" v-model="filterData.payState" class="uk-select uk-border-rounded">
            <option value="all">Tous</option>
            <option value="paye">Paye</option>
            <option value="non_paye">Impaye</option>
          </select>
        </div>
        
        <div v-if="typeUser == 'admin' || typeUser == 'controleur' || typeUser == 'commercial'" class="uk-width-1-6@m">
          <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
          <select @change="filterRequest()" v-model="filterData.user" class="uk-select uk-border-rounded">
            <option value="all">Tous les vendeurs</option>
            <option v-for="u in users" :key="u.username" :value="u.username"> {{u.localisation}} </option>
          </select>
        </div>
         <!-- paginate component -->
        <div class="uk-width-1-6@m uk-margin-top">
          <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
          <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
          <button @click="getRapportVente()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
          <template v-if="lastUrl">
            <button @click="paginateFunction(lastUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Precedent">
              <span uk-icon="chevron-left"></span>
            </button>
          </template>
          <template v-if="nextUrl">
            <button @click="paginateFunction(nextUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize u-t uk-text-small" uk-tooltip="Suivant">
              <span uk-icon="chevron-right"></span>
            </button>
          </template>
        </div>
        <!-- // -->
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
          <tr v-for="rap in rapportList" :key="rap.id_rapport">
            <td>{{rap.date}}</td>
            <td :uk-tooltip="rap.vendeurs">{{rap.vendeurs.substring(0,40)}}...</td>
            <td>{{rap.type}}</td>
            <td>{{rap.credit}}</td>
            <td>{{rap.quantite}}</td>
            <td>{{rap.montant_ttc| numFormat}}</td>
            <td>{{rap.commission | numFormat}}</td>
            <td>{{rap.promo}}</td>
            <td>{{rap.paiement_commission}}</td>
            <td>
              <template v-if="typeUser == 'admin' && rap.paiement_commission == 'non_paye' && rap.state == 'unaborted'" id="">
                <button @click="activRapport = rap" uk-toggle="target : #modal-abort-rapport" type="button" class="uk-button uk-button-small uk-border-rounded uk-button-danger uk-text-capitalize uk-text-small">Annuler</button>
              </template>
              <template v-if="rap.state == 'aborted'">
                <span class="uk-alert-danger">invalide</span>
              </template>

              <template v-if="rap.state == 'unaborted'">
                <button @click="getDetailsRapport(rap)" class="uk-button uk-button-small uk-border-rounded uk-button-default uk-text-capitalize uk-text-small">Details</button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="uk-flex uk-flex-center">
        <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
      </div>
    </div>
  </template>
    <template v-if="typeUser == 'v_da'" id="">
      <div v-if="payComission" class="uk-grid-small uk-grid-divider" uk-grid>
        <div  class="uk-width-1-3@m">
          <h3 class="">Demandez un paiement de comission</h3>
          <!-- Erreor block -->
          <template v-if="errors">
            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert v-for="(error,index) in errors" :key="index">
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
          <div class="uk-text-right">
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
          </div>
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
              <tr v-for="(pay,index) in payComissionList" :key="index">
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
        mounted() {
          UIkit.offcanvas($("#side-nav")).hide();
          this.getRapportVente()
          // this.showRapport = this.rappWithUser
          
          //
        },
        components : {
          Loading,
          datepicker
        },
        data () {
          return {
            // data export fields
            field_export : {
              'date'   : 'date',
              'vendeurs' : 'vendeurs',
              'type' : 'type',
              'state' : 'state',
              'quantite' : 'quantite',
              'promo' : 'promo',
              'Paiement Comission' : 'paiement_commission',
              'Montant Ttc' : 'montant_ttc',
              'Comission' : 'commission'
            },
            // /
            filterData : {
              type : "all",
              state : "all",
              promoState : "all",
              payState : "all",
              user : "all"
            },
             // paginate
            nextUrl : "",
            lastUrl : "",
            perPage : "",
            currentPage : 1,
            firstPage : "",
            firstItem : 1,
            total : 0,
    // #####       
            isLoading : false,
            fullPage : true,
            rapportList : [],
            
            commission : 0,
            staticComission : 0,
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
            users : [],
            rappDetails : [],
            rappInfos : {}
          }
        },
        methods : {
          paginateFunction : async function (url) {
            try {
              
              let response = await axios.get(url)
              if(response && response.data) {
                
                this.rapportList = response.data.all

                this.nextUrl = response.data.next_url
                this.lastUrl = response.data.last_url
                this.currentPage = response.data.current_page
                this.firstPage = response.data.first_page
                this.firstItem = response.data.first_item,
                this.total = response.data.total
              }
            }
            catch(error) {
              alert("Erreur!")
              console.log(error)
            }
          },
          getDetailsRapport : async function (rap) {
            try {
              UIkit.modal("#modal-detail-rapport").show()
              let response = await axios.post('/user/rapport-ventes/get-details',{
                _token : this.myToken,
                id_rapport : rap.id
              })
              this.rappDetails = response.data                
              this.rappInfos = rap
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
            // this.isLoading = true
            UIkit.modal($("#modal-abort-rapport")).hide()
            this.abortRapportFormData._token = this.myToken
            this.abortRapportFormData.id_rapport = this.activRapport.id
            try {
              let response = await axios.post('/admin/rapport/abort',this.abortRapportFormData)
              if(response.data == 'done') {
                this.isLoading = false
                alert("Un rapport a ete annule :(")
                Object.assign(this.$data,this.$options.data())
                this.getRapportVente()
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
          filterRequest : async function () {
            try {
              this.isLoading = true
              let response = await axios
                .get('/user/rapport/filter/'+this.filterData.type+'/'+this.filterData.state+'/'+this.filterData.promoState+'/'+this.filterData.payState+'/'+this.filterData.user)
              if(response) {
                this.rapportList = response.data.all
                this.commission = response.data.comission

                this.nextUrl = response.data.next_url
                this.lastUrl = response.data.last_url
                this.perPage = response.data.per_page
                this.firstItem = response.data.first_item
                this.total = response.data.total
                
                this.isLoading = false
              }
            }
            catch(error) {
              alert("Erreur!")
              console.log(error)
            }
          },
          getRapportVente : async function () {
            try {
              Object.assign(this.$data,this.$options.data())
              this.isLoading = true

              var response = await axios.get('/user/rapport/all')
              var responseCom = await axios.get('/user/rapport/commission-total')
              
              if(response && responseCom) {

                this.isLoading = false
                this.commission = responseCom.data
                this.rapportList = response.data.all

                this.nextUrl = response.data.next_url
                this.lastUrl = response.data.last_url
                this.perPage = response.data.per_page
                this.firstItem = response.data.first_item
                this.total = response.data.total
              
              }
              if(this.typeUser == 'admin' || this.typeUser == 'commercial' || this.typeUser == 'controleur') {
                this.allUsers()
              }

              if(this.typeUser == 'v_da') {
                this.getPayComissionListForVendeur()
              }
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
                this.getPayComissionListForVendeur()
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
