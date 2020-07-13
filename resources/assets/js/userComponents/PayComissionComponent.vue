<template>
  <div class="">
    
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

    <div id="modal-commission" class="uk-modal-container" uk-modal="esc-close : false ; bg-close : false;">
  	    <div class=" uk-modal-dialog">
  	        <div class="uk-modal-header">
  	            <h3 class="uk-modal-title"> <i class="material-icons">monetization_on</i> Paiement Commission</h3>
                <div class="uk-align-right uk-width-1-3@m">
                  <label for="">Vendeurs</label>
                  <select v-model="filterByUser" class="uk-select uk-border-rounded">
                    <option value="">Tous</option>
                    <option :value="u.localisation" v-for="u in users" :key="u.username">{{u.localisation}}</option>
                  </select>
                </div>
  	        </div>
  	        <div class="uk-modal-body uk-overflow-auto uk-height-large">
  						<table class="uk-table uk-table-small uk-table-hover uk-table-striped uk-table-divider uk-table-responsive">
  							<thead>
  								<tr>
  									<th>Du</th>
  									<th>Au</th>
  									<th>Total</th>
  									<th>Status</th>
                    <th>Paye le</th>
                    <th>Demande le</th>
  									<th>Vendeurs</th>
                    <th>-</th>
  								</tr>
  							</thead>
  							<tbody>
                  <tr v-for="pay in listFilterByUser.slice(start,end)" :key="pay.id">
                    <td>{{pay.du}}</td>
                    <td>{{pay.au}}</td>
                    <td>{{pay.total}}</td>
                    <td v-if="pay.status == 'unvalidated'" class="uk-text-danger">{{pay.status}}</td>
                    <td v-else class="uk-text-success">{{pay.status}}</td>
                    <td>{{pay.pay_at}}</td>
                    <td>{{pay.demand_at}}</td>
                    <td>{{pay.vendeurs}}</td>
                    <td>
                    <template id="" v-if="pay.status == 'unvalidated' && typeUser == 'gcga'">
                        <button @click="userActiveValidate = pay" type="button" uk-toggle="target : #validate-payment-comission" class="uk-button uk-button-small uk-button-primary uk-text-capitalize uk-box-shadow-small uk-border-rounded">Validez</button>
                        <button @click="userActiveValidate = pay" type="button" uk-toggle="target : #abort-payment-comission" class="uk-button uk-button-small uk-button-danger uk-text-capitalize uk-box-shadow-small uk-border-rounded">Annulez</button>
                    </template>
                  </td>
                  </tr>
                </tbody>
  						</table>
  	        </div>
  	        <div class="uk-modal-footer">
              <div class="uk-align-left">
                <ul class="uk-pagination uk-flex uk-flex-center" uk-margin>
                  <li> <span> Page active : {{currentPage}} </span> </li>
                  <li> <button @click="previousPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> <span uk-pagination-previous></span> Precedent </button> </li>
                  <li> <button @click="nextPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> Suivant <span uk-pagination-next></span>  </button> </li>
                </ul>
              </div>
              <div class="uk-align-right">
  	            <button class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-danger uk-modal-close" type="button">Fermer</button>
              </div>
  	        </div>
  	    </div>
  	</div>

    <div id="validate-payment-comission" uk-modal="esc-close : false ; bg-close : false">
  	    <div class="uk-modal-dialog">

  	        <div class="uk-modal-header" >
              <div class="uk-alert-info" uk-alert>
                <p> <span uk-icon="icon : info"></span> Vous confirmez la validation pour le paiement des commissions a hauteur de : <span class="uk-text-bold">{{ userActiveValidate.total }} GNF </span> , pour :  <span class="uk-text-bold">{{ userActiveValidate.vendeurs }}</span></p>
              </div>
  	        </div>
  	        <div class="uk-modal-body">
              <!-- Erreor block -->
                <template v-if="errors.length" v-for="error in errors">
                  <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{error}}</p>
                  </div>
                </template>
              <form @submit.prevent="validatePayComission()">
                <div class="uk-margin-small">
                  <label for="">Confirmez votre mot de passe</label>
                  <input type="password" v-model="passwordConfirm" class="uk-input uk-border-rounded uk-box-shadow-hover-small" placeholder="Entrez votre mot de passe ..." autofocus>
                </div>
                <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small">validez <span uk-icon="icon : check"></span> </button>
              </form>
  					</div>
  	        <div class="uk-modal-footer uk-text-right">
  						<button uk-toggle="target : #modal-commission" class="uk-button uk-button-default uk-border-rounded uk-box-shadow-small uk-button-small" type="button"> <span uk-icon="icon : arrow-left"></span> Retour</button>
  						<button class="uk-button uk-button-danger uk-modal-close uk-border-rounded uk-box-shadow-small uk-button-small" type="button">Fermer</button>
  					</div>
  	    </div>
  	</div>
    <!-- ANNULER UNE DEMANDE DE PAIEMENT DE COMMISSION -->
    <div id="abort-payment-comission" uk-modal="esc-close : false ; bg-close : false">
  	    <div class="uk-modal-dialog">

  	        <div class="uk-modal-header" >
              <div class="uk-alert-warning" uk-alert>
                <p> <span uk-icon="icon : warning"></span> Vous confirmez l'annulation pour le paiement des commissions a hauteur de : <span class="uk-text-bold">{{ userActiveValidate.total }} GNF </span> , pour :  <span class="uk-text-bold">{{ userActiveValidate.vendeurs }}</span></p>
              </div>
  	        </div>
  	        <div class="uk-modal-body">
              <!-- Erreor block -->
                <template v-if="errors.length" v-for="error in errors">
                  <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{error}}</p>
                  </div>
                </template>
              <form @submit.prevent="abortPayComission()">
                <div class="uk-margin-small">
                  <label for="">Confirmez votre mot de passe</label>
                  <input type="password" v-model="passwordConfirm" class="uk-input uk-border-rounded uk-box-shadow-hover-small" placeholder="Entrez votre mot de passe ..." autofocus>
                </div>
                <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small">validez <span uk-icon="icon : check"></span> </button>
              </form>
  					</div>
  	        <div class="uk-modal-footer uk-text-right">
  						<button uk-toggle="target : #modal-commission" class="uk-button uk-button-default uk-border-rounded uk-box-shadow-small uk-button-small" type="button"> <span uk-icon="icon : arrow-left"></span> Retour</button>
  						<button class="uk-button uk-button-danger uk-modal-close uk-border-rounded uk-box-shadow-small uk-button-small" type="button">Fermer</button>
  					</div>
  	    </div>
  	</div>
    <!-- // -->
  </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
  export default {
    created () {

    },
      mounted() {
        this.getPayComissionList()
      },
      components : {
        Loading
      },

      data () {
        return {
          isLoading : false,
          fullPage : true,
          userActiveValidate : {},
          passwordConfirm : "",
          errors : [],
          users : [],
          filterByUser : "",
          start : 0,
          end : 8,
          currentPage : 1
        }
      },
      methods : {
        nextPage : function () {
            if(this.listFilterByUser.length > this.end) {
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
        getPayComissionList : async function () {
          try {
            if(this.typeUser == 'admin' || this.typeUser == 'commercial') {
              var response = await axios.get('/admin/pay-comissions/all')
            } else if(this.typeUser == 'gcga') {
              var response = await axios.get('/user/pay-comissions/all')
            } else {
              return 0
            }
            var userListResponse = await axios.get('/user/all-vendeurs')
            if(response.data && userListResponse.data) {
              this.$store.commit('setPayComissionList',response.data)
              this.users = userListResponse.data
            }
          } catch (e) {
              console.log(e)
          }
        },
        validatePayComission : async function () {
          this.isLoading = true
          UIkit.modal($("#validate-payment-comission")).hide()
          try {
            let response = await axios.post("/user/rapport-ventes/validate-pay-commission",{
              _token : this.myToken,
              password_confirm : this.passwordConfirm,
              pay_comission_id : this.userActiveValidate.id
            })

            if(response.data == 'done') {
              this.isLoading = false
              UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Vous avez paye une commission :-)</div>").then(function () {
                location.reload()
              })
            }
          } catch (error) {
            this.isLoading = false
            UIkit.modal($("#validate-payment-comission")).show()
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
        abortPayComission : async function () {
          try {
              this.isLoading = true
              UIkit.modal($("#abort-payment-comission")).hide()
              let response = await axios.post('/user/rapport-ventes/abort-pay-commission',{
                _token : this.myToken,
                password_confirm : this.passwordConfirm,
                pay_comission_id : this.userActiveValidate.id
              })
              if(response.data) {
                this.isLoading = false
                UIkit.modal.alert("<div class='uk-alert-sucess' uk-alert>Operation effectue avec success !</div>")
                  .then(function () {
                    location.reload()
                  })
              }
          } catch(error) {
              this.isLoading = false
              UIkit.modal($("#abort-payment-comission")).show()
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
        listFilterByUser() {
          return this.payComissionList.filter((p) => {
            return p.vendeurs.match(this.filterByUser)
          })
        },
        payComissionList () {
          return this.$store.state.payComissionList
        },
        myToken () {
          return this.$store.state.myToken
        },
        typeUser () {
          return this.$store.state.typeUser
        }
      }
  }
</script>
