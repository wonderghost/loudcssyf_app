<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <h3><router-link class="uk-button uk-button-small uk-border-rounded uk-button-default uk-text-small" uk-tooltip="Retour" to="/commandes"><span uk-icon="arrow-left"></span></router-link> Commande Credit</h3>
        <hr class="uk-divider-small">
        <!-- // -->
        <!-- validation commande modal -->
        <template id="" v-if="typeUser == 'gcga'">
          <div id="modal-validation-command" uk-modal="esc-close : false ; bg-close : false;">
              <div class="uk-modal-dialog">
                  <div class="uk-modal-header">
                    <div class="uk-alert-info" uk-alert>
                      <p>
                      <span uk-icon = "icon : info"></span> Vous confirmez l'envoi de : <span class="uk-text-bold">{{ commandToValidate.montant }}</span>, a : <span class="uk-text-bold">{{ commandToValidate.vendeurs }}</span>
                      </p>
                    </div>
                  </div>
                  <div class="uk-modal-body">
                    <!-- Erreor block -->
                      <template v-if="errors.length">
                      <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                        <a href="#" class="uk-alert-close" uk-close></a>
                        <p>{{error}}</p>
                      </div>
                    </template>
                    <form @submit.prevent="validateCommandCredit()">
                      <div class="uk-margin-small">
                        <label for="">Entrez le montant</label>
                        <input type="number" v-model="validateFormData.montant" placeholder="Montant de la transaction" class="uk-input uk-border-rounded" autofocus>
                      </div>
                      <div class="uk-margin-small">
                        <label for="">Confirmez votre mot de passe</label>
                        <input type="password" v-model="validateFormData.password_confirmed" placeholder="Entrez votre mot de passe ici" class="uk-input uk-border-rounded" value="">
                      </div>
                      <button type="submit" class="uk-button uk-button-small uk-text-capitalize uk-text-small uk-button-primary uk-border-rounded uk-box-shadow-small" name="button">Validez</button>
                    </form>
                  </div>
                  <div class="uk-modal-footer">
                    <button type="button" class="uk-modal-close uk-button uk-button-small uk-text-capitalize uk-text-small uk-border-rounded uk-box-shadow-small uk-button-danger" name="button">Fermer</button>
                  </div>
              </div>
          </div>
        </template>
        <!-- // -->
    <div class="">
      <div class="uk-grid-small uk-margin-top uk-flex uk-flex-right" uk-grid>
        <div class="uk-width-1-6@m">
          <label for=""><span uk-icon=""></span>  Status</label>
          <select @change="filterRequest()" v-model="filterData.state" class="uk-select uk-border-rounded">
            <option value="unvalidated">en attente</option>
            <option value="validated">confirmer</option>
            <option value="aborted">annuler</option>
          </select>
        </div>
        <div v-if="typeUser != 'v_da' && typeUser != 'v_standart'" class="uk-width-1-4@m">
          <label for=""><span uk-icon="users"></span> Vendeurs</label>
          <select @change="filterRequest()" v-model="filterData.user" class="uk-select uk-border-rounded">
            <option value="all">Tous</option>
            <option v-for="(u,index) in userList" :key="index" :value="u.username">{{u.localisation}}</option>
          </select>
        </div>
        <!-- paginate component -->
        <div class="uk-width-1-3@m uk-margin-top">
          <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
          <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
          <button @click="getCommandCredit()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
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
      <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
        <thead>
          <tr>
            <th v-for="(head,index) in tableHead" :key="index">{{head}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(credit,index) in commandList" :key="index">
            <td>{{credit.date}}</td>
            <td>{{credit.vendeurs}}</td>
            <td>{{credit.type}}</td>
            <td>{{credit.montant}}</td>
            <td v-if="credit.status == 'unvalidated'" class="uk-text-danger">{{credit.status}}</td>
            <td v-else-if="credit.status == 'aborted'" class="uk-text-warning">{{credit.status}}</td>
            <td v-else class="uk-text-success">{{credit.status}}</td>
            <td>{{credit.numero_recu}}</td>
            <td v-if="credit.recu !== 'undefined'">
              <div uk-lightbox>
                <a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default uk-text-capitalize" href="" :data-caption="credit.numero_recu">voir</a>
              </div>
            </td>
            <td v-else>{{credit.recu}}</td>
            <td>
              <template id="" v-if="credit.status == 'unvalidated' && typeUser == 'gcga'">
                <button type="button" @click="commandToValidate = credit" uk-toggle="target : #modal-validation-command" name="button" class="uk-text-capitalize uk-button uk-button-small uk-button-primary uk-text-small uk-border-rounded uk-box-shadow-small"> validez <span uk-icon="icon : check"></span> </button>
                <button type="button" @click="abortCommand(credit)" name="button" class="uk-text-capitalize uk-button uk-button-small uk-button-danger uk-border-rounded uk-box-shadow-small uk-text-small"> annuler <span uk-icon="icon : close"></span> </button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="uk-flex uk-flex-center">
        <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
      </div>
    </div>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        mounted() {
          UIkit.offcanvas($("#side-nav")).hide();
          this.getCommandCredit()
        },
        props : {
          theUser : String
        },
        data () {
          return {
            userList : [],
            filterData : {
              state : "unvalidated",
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

            commandList : [],
            tableHead : ['date','vendeurs','type','montant','status','numero recu','recu'],
            
            isLoading : false,
            fullPage : true,
            commandToValidate : {},
            validateFormData : {
              _token : "",
              commande : "",
              montant : "",
              password_confirmed : "",
              type_commande : ""
            },
            errors : []
          }
        },
        components : {
          Loading
        }
        ,
        methods : {
          paginateFunction : async function (url) {
            try {
              
              let response = await axios.get(url)
              if(response && response.data) {
                
                this.commandList = response.data.all

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
          filterRequest : async function () {
            try {
              if(this.typeUser != 'admin' && this.typeUser != 'gcga' && this.typeUser != 'commercial') {
                this.filterData.user = this.userName
              }


              let response = await axios
                .get('/user/commandes/credit/filter/'+this.filterData.state+'/'+this.filterData.user)

                if(response) {

                  this.commandList = response.data.all

                  this.nextUrl = response.data.next_url
                  this.lastUrl = response.data.last_url
                  this.perPage = response.data.per_page
                  this.firstItem = response.data.first_item
                  this.total = response.data.total
                }
            }
            catch(error) {
              alert("Erreur")
              console.log(error)
            }
          },
          getCommandCredit : async function () {
            try {
              Object.assign(this.$data,this.$options.data())
              this.isLoading = true
              var response = await axios.get('/user/commandes/credit-all')
              var userResponse = await axios.get('/user/all-vendeurs')
              if(response && userResponse) {
                this.commandList = response.data.all

                this.userList = userResponse.data

                this.nextUrl = response.data.next_url
                this.lastUrl = response.data.last_url
                this.perPage = response.data.per_page
                this.firstItem = response.data.first_item
                this.total = response.data.total
                
                this.isLoading=false
              }
            }
            catch (e) {
              alert(e)
            }
          },
          validateCommandCredit : async function () {
            this.validateFormData.commande = this.commandToValidate.id
            this.validateFormData._token = this.myToken
            this.validateFormData.type_commande = this.commandToValidate.type

            this.errors = []

            try {
              this.isLoading = true
              UIkit.modal($("#modal-validation-command")).hide()

              let response = await axios.post("/user/send-afrocash",this.validateFormData)

              Object.assign(this.$data,this.$options.data())
              if(response.data == 'done') {
                this.isLoading = false
                alert("Success !")
                this.getCommandCredit()
              }
            } catch (error) {
              this.isLoading = false
              UIkit.modal($("#modal-validation-command")).show()
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
          abortCommand : function (credit) {
            var tmp = this
            var val = confirm("Etes vous sur de vouloir annuler la commande de : "+credit.vendeurs+ " ?")
            if(val == true) {
              tmp.makeAbortCommand(credit)
            }
            else {  
              alert("Vous avez annuler l'action !")
            }
          },
          makeAbortCommand : async function (credit) {
            this.isLoading = true
            try {
                let response = await axios.post("/user/credit-cga/abort-commandes",{
                  _token : this.myToken,
                  command : credit.id
                })
                if(response.data == 'done') {
                  this.isLoading = false
                  alert("Success !")
                  this.getCommandCredit()
                }
            } catch (e) {
              this.isLoading.false
              UIkit.modal.alert(e)
                .then(function () {
                  location.reload()
                })
            }
          }
        },
        computed : {
          userName() {
            return this.$store.state.userName
          },
          typeUser () {
            return this.$store.state.typeUser
          },
          myToken () {
            return this.$store.state.myToken
          }
        }
    }
</script>
