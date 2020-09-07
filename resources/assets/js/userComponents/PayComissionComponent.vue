<template>
  <div class="uk-container uk-container-large">
    
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

    <h3><router-link class="uk-button uk-button-small uk-border-rounded uk-button-default" uk-tooltip="Retour" to=""><span uk-icon="arrow-left"></span></router-link> Paiement Comission</h3>
    <hr class="uk-divider-small">

    <ul class="uk-subnav uk-subnav-pill" uk-switcher>
      <li><a class="uk-button uk-button-small uk-border-rounded uk-button-primary uk-text-small" href="#">DA & PDC</a></li>
      <li><a class="uk-button uk-button-small uk-border-rounded uk-button-primary uk-text-small" href="#">PDRAF</a></li>
    </ul>

    <ul class="uk-switcher uk-margin">
      <li>
        <div class="uk-grid-small" uk-grid>
          <div class="uk-width-2-3@m">
            <input type="text" v-model="filterByUser" class="uk-input uk-border-rounded" placeholder="Recherche ...">
          </div>
          <!-- paginate component -->
          <div class="uk-width-1-3@m">
            <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
            <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
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
            <tr v-for="pay in listFilterByUser" :key="pay.id">
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
      </li>
      <li>
        <table class="uk-table uk-table-small uk-table-hover uk-table-striped uk-table-divider uk-table-responsive">
          <thead>
            <tr>
              <th>Du</th>
              <th>Au</th>
              <th>Total</th>
              <th>Status</th>
              <th>Paye le</th>
              <th>PDC</th>
              <th>PDRAF</th>
              <th>-</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </li>
    </ul>
  </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
  export default {
    mounted() {
      UIkit.offcanvas($("#side-nav")).hide();
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
// paginate
        nextUrl : "",
        lastUrl : "",
        perPage : "",
        currentPage : 1,
        firstPage : "",
        firstItem : 1,
        total : 0,
// #####
        payComissionList : []
      }
    },
    methods : {
      paginateFunction : async function (url) {
        try {
          
          let response = await axios.get(url)
          if(response && response.data) {
            
            this.payComissionList = response.data.data
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
      getPayComissionList : async function () {
        try {
          this.isLoading = true
          if(this.typeUser == 'admin' || this.typeUser == 'commercial') {
            var response = await axios.get('/admin/pay-comissions/all')
            // commission for pdraf
            var theResponse = await axios.get('/admin/pay-comission/pdraf/all')
            console.log(theResponse)
          } else if(this.typeUser == 'gcga') {
            var response = await axios.get('/user/pay-comissions/all')
          } else {
            throw "Aucune Donnees"
          }
          this.isLoading = false
          var userListResponse = await axios.get('/user/all-vendeurs')
          if(response.data && userListResponse.data) {
            // this.$store.commit('setPayComissionList',response.data.data)
            this.payComissionList = response.data.data
            this.nextUrl = response.data.next_url
            this.lastUrl = response.data.last_url
            this.perPage = response.data.per_page
            this.firstItem = response.data.first_item
            this.total = response.data.total

            this.users = userListResponse.data
          }
        } catch (e) {
            alert(e)
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
            alert("Success !")
            this.getPayComissionList()
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
              alert("Success !")
              this.getPayComissionList()
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
          return p.vendeurs.match(this.filterByUser.toUpperCase())
        })
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
