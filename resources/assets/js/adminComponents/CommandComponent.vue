<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a @click="start=0 , end=10 , currentPage = 1" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Livraison</a></li>
        <li><a v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'v_da' || typeUser == 'v_standart'" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit</a></li>


    </ul>
    <ul class="uk-switcher uk-margin">
			<li>
        <!-- AFROCASH -->
        <template id="" v-if="typeUser == 'logistique'">
          <!-- modal retour afrocash -->
          <div id="modal-retour-afrocash" uk-modal>
              <div class="uk-modal-dialog">
                  <div class="uk-modal-header">
                      <h3 class="uk-modal-title"> <span uk-icon="icon : reply"></span> Retour Afrocash</h3>
                  </div>
                  <div class="uk-modal-body">
                    
                  </div>
                  <div class="uk-modal-footer">
                    <button type="button" class="uk-modal-close uk-button uk-button-small uk-button-danger uk-border-rounded">Fermer</button>
                  </div>
              </div>
          </div>
          <!-- // -->
          <div class="uk-grid" uk-grid>
            <div class="uk-width-1-4@m">
              <div class="uk-margin-small">
                <label for=""> <span uk-icon="icon : credit-card"></span> Solde (GNF)</label>
                <span class="uk-input uk-border-rounded uk-text-center uk-text-bold">{{ afrocashLogistique.solde | numFormat}}</span>
              </div>
                <button uk-toggle="target : #modal-retour-afrocash" type="button" class="uk-button uk-button-primary uk-align-right uk-border-rounded uk-button-small">retour afrocash <span uk-icon="icon : reply"></span> </button>
            </div>
            <div class="uk-width-1-4@m"></div>
          </div>
        </template>
        <!-- LOGISTIQUE -->
        <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-right">
          <li> <a href="#" @click="filterCommande('unconfirmed')">En attente de confirmation</a> </li>
          <li> <a href="#" @click="filterCommande('confirmed')">Deja confirmee</a> </li>
          <li> <a href="#" @click="filterCommande('aborted')">Annule</a> </li>
        </ul>

          <div class="">

            <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
              <thead>
                <tr>
                  <th v-for="head in materialCommand">{{head}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="command in commandList.slice(start,end)">
                  <td v-for="(column , name) in command" v-if="name != 'link' && name != 'id' && name!='status'">{{column}}</td>

                  <td class="uk-text-danger" v-if="command.status == 'unconfirmed'">{{command.status}}</td>
                  <td class="uk-text-success" v-if="command.status == 'confirmed'" >{{command.status}}</td>
                  <td class="uk-text-warning" v-if="command.status == 'aborted'" >{{command.status}}</td>
                  <td> <a :href="command.link" v-if="typeUser == 'logistique' && command.status == 'unconfirmed'" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-text-capitalize">confirmer</a> </td>
                  <td v-if="command.status == 'unconfirmed'">
                    <a @click="abortCommandMaterial(command.id)" v-if="typeUser == 'logistique'" class="uk-button-small uk-button uk-button-danger uk-border-rounded uk-text-capitalize">Annuler</a>
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
      </li>
      <li>
        <template id="">
          <livraison :the-user="theUser"></livraison>
        </template>
      </li>
      <template id="" v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'v_da' || typeUser == 'v_standart'">
        <li>
          <credit-component :the-user="theUser"></credit-component>
        </li>
    </template>
    </ul>
  </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
      created () {
        this.isLoading = true
      },
        mounted() {
          this.getMaterialCommande()
          this.getSoldeLogistique()
        },
        components : {
          Loading
        },
        props : {
          theUser : String
        },
        data () {
          return {
            materialCommand : ['date','vendeurs','designation','quantite','parabole a livrer','promo','status'],
            start : 0,
            end : 10,
            currentPage : 1,
            isLoading : false,
            fullPage : true,
            afrocashLogistique : {},
          }
        },
        methods : {
          getSoldeLogistique : async function () {
            try {
              let response = await axios.get('/user/logistique/afrocash-solde')
              this.afrocashLogistique = response.data
            } catch (e) {
                alert(e)
            }
          },
          getMaterialCommande : async function () {
            try {
              if(this.typeUser == 'admin') {
                var response = await axios.get('/admin/commandes/all')
              } else if(this.typeUser == 'logistique') {
                var response = await axios.get("/logistique/commandes/all")
              } else if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                var response = await axios.get('/user/commandes/all')
              }
              if(response.data.length) {
                this.$store.commit('setCommandMaterial',response.data)
                this.isLoading = false
              }
            } catch (e) {
              alert(e)
            }
          }
          ,
          filterCommande : function (type) {
            this.currentPage = 1
            this.start = 0
            this.end = 10
            this.$store.commit('setTypeCommand',type)
          },
          nextPage : function () {
            if(this.commandList.length > this.end) {
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
          abortCommandMaterial : async function(e) {
            try {
              this.isLoading = true
              let response = await axios.post('/logistique/commandes/abort', {
                id : e
              })
              if(response.data == 'done') {
                this.isLoading = false
                UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded'  uk-alert>Vous avez annule une commande Materiel</div>")
                  .then(function() {
                    location.reload()
                  })
              }
            } catch(error) {
                alert(error.response.data)
            }
          }
        },
        computed : {
          commandList () {
            if(!this.theUser) {
              return this.commandMaterial
            }
            else {
              return this.commandMaterial.filter((command) => {
                return command.vendeurs.match(this.theUser)
              })
            }
          }
          ,
          commandMaterial () {
            return this.cMaterial.filter( (command) => {
              return command.status == this.typeCommand
            })
          },
          typeUser () {
            return this.$store.state.typeUser
          },
          cMaterial () {
            return this.$store.state.commandMaterial
          },
          typeCommand() {
            return this.$store.state.typeCommand
          }
        }
    }
</script>
