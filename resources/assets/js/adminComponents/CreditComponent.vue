<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>
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
                      <template v-if="errors.length" v-for="error in errors">
                      <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
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
                      <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small" name="button">Validez</button>
                    </form>
                  </div>
                  <div class="uk-modal-footer">
                    <button type="button" class="uk-modal-close uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-danger" name="button">Fermer</button>
                  </div>
              </div>
          </div>
        </template>
        <!-- // -->

    <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-bottom">
      <li> <a @click="filterCommandCredit('unvalidated')" href="#">En attente de validation</a> </li>
      <li> <a @click="filterCommandCredit('validated')" href="#">Deja validee</a> </li>
      <li> <a @click="filterCommandCredit('aborted')" href="#">commandes annullee</a> </li>
    </ul>
    <div class="">
      <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
        <thead>
          <tr>
            <th v-for="head in tableHead">{{head}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="credit in filterCommandeCredit.slice(start,end)">
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
                <button type="button" @click="commandToValidate = credit" uk-toggle="target : #modal-validation-command" name="button" class="uk-text-capitalize uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small"> validez <span uk-icon="icon : check"></span> </button>
                <button type="button" @click="abortCommand(credit)" name="button" class="uk-text-capitalize uk-button uk-button-small uk-button-danger uk-border-rounded uk-box-shadow-small"> annuler <span uk-icon="icon : close"></span> </button>
              </template>
            </td>

          </tr>
        </tbody>
      </table>
      <ul class="uk-pagination uk-flex uk-flex-center" uk-margin>
        <li> <span> Page active : {{currentPage}} </span> </li>
        <li> <button @click="previousPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> <span uk-pagination-previous></span> Precedent </button> </li>
        <li> <button @click="nextPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> Suivant <span uk-pagination-next></span>  </button> </li>
      </ul>
    </div>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        mounted() {
          this.isLoading = true
          this.getCommandCredit()
        },
        props : {
          theUser : String
        },
        data () {
          return {
            tableHead : ['date','vendeurs','type','montant','status','numero recu','recu'],
            currentPage: 1,
            start : 0,
            end  : 10,
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
          getCommandCredit : async function () {
            try {
              if(this.theUser == 'admin') {
                var response = await axios.get('/admin/commandes/credit-all')
              } else {
                var response = await axios.get('/user/commandes/credit-all')
              }
              this.$store.commit('setCommandCredit',response.data)
              this.isLoading=false
            }
            catch (e) {
              alert(e)
            }
          },
          validateCommandCredit : async function () {
            this.validateFormData.commande = this.commandToValidate.id
            this.validateFormData._token = this.myToken
            this.validateFormData.type_commande = this.commandToValidate.type
            try {
              this.isLoading = true
              UIkit.modal($("#modal-validation-command")).hide()

              let response = await axios.post("/user/send-afrocash",this.validateFormData)
              if(response.data == 'done') {
                this.isLoading = false
                UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Un commande valide avec success :-) <span uk-icon='icon : check'></span></div>")
                  .then(function () {
                    location.reload()
                  })
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
            UIkit.modal.confirm("<div class='uk-alert-warning' uk-alert><p> <span uk-icon='icon : warning'></span> Etes vous sur de vouloir annuler la commande de : "+credit.vendeurs+ " ? </p></div>")
              .then(function () {
                tmp.makeAbortCommand(credit)
              })
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
                  UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Une commande annule !</div>")
                    .then(function () {
                      location.reload()
                    })
                }
            } catch (e) {
              this.isLoading.false
              UIkit.modal.alert(e)
                .then(function () {
                  location.reload()
                })
            }
          }
          ,
          filterCommandCredit (status) {
            this.currentPage = 1
            this.start = 0
            this.end = 10
            this.$store.commit('setStatusCommandCredit',status)
          },
          nextPage : function () {
            if(this.commandCredit.length > this.end) {
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
          }
        },
        computed : {
          commandCredit () {
            return this.commandC.filter( (credit)  => {
                return credit.status === this.statusCommandCredit
            })
          },
          filterCommandeCredit () {
            if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
              return this.commandCredit.filter((credit) => {
                return credit.vendeurs.match(this.theUser)
              })
            }
            else {
              return this.commandCredit
            }
          },
          commandC () {
            return this.$store.state.commandCredit
          },
          statusCommandCredit () {
            return this.$store.state.statusCommandCredit
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
