<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

    <h3>Commande Materiel</h3>
    <hr class="uk-divider-small">


    <template v-if="typeUser == 'logistique' || typeUser == 'admin' || typeUser == 'v_da' || typeUser == 'v_standart'">
      <router-link to="/livraison/all" style="color : #000 !important"><button class="uk-button uk-button-small uk-border-rounded">Livraison</button></router-link>
    </template>
    <template v-if="typeUser == 'gcga' || typeUser == 'admin' || typeUser == 'v_da' || typeUser == 'v_standart' || typeUser == 'commercial'">
      <router-link to="/commande-credit/all" style="color : #000 !important"><button class="uk-button uk-button-small uk-border-rounded">Credit</button></router-link>
    </template>
    <template v-if="typeUser == 'admin' || typeUser == 'v_standart'">
      <router-link to="/pdc/command/list" style="color : #000 !important"><button class="uk-button uk-button-small uk-border-rounded">Reseaux Afrocash</button></router-link>
    </template>
    
   
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

    <div class="">
      <div class="uk-grid-small uk-margin-top uk-flex uk-flex-right" uk-grid>
        <div class="uk-width-1-6@m">
          <label for=""><span uk-icon=""></span>  Status</label>
          <select @change="filterRequest()" v-model="filterData.state" class="uk-select uk-border-rounded">
            <option value="unconfirmed">en attente</option>
            <option value="confirmed">confirmer</option>
            <option value="aborted">annuler</option>
          </select>
        </div>
        <div class="uk-width-1-6@m">
          <label for="">Promo</label>
          <select @change="filterRequest()" v-model="filterData.promoState" class="uk-select uk-border-rounded">
            <option value="all">Tous</option>
            <option value="en_promo">En promo</option>
            <option value="hors_promo">Hors promo</option>
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
          <button @click="getMaterialCommande()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
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

      <table class="uk-table uk-table-small uk-table-divider uk-margin-remove-top uk-table-striped uk-table-hover uk-table-responsive">
        <thead>
          <tr>
            <th v-for="(head,index) in materialCommand" :key="index">{{head}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(command,index) in commandList" :key="index">
            <td v-for="(column , name) in command" v-if="name != 'link' && name != 'id' && name!='status' && name != 'id_command'">{{column}}</td>

            <td class="uk-text-danger" v-if="command.status == 'unconfirmed'">{{command.status}}</td>
            <td class="uk-text-success" v-if="command.status == 'confirmed'" >{{command.status}}</td>
            <td class="uk-text-warning" v-if="command.status == 'aborted'" >{{command.status}}</td>
            <!-- <td> <a :href="command.link" v-if="typeUser == 'logistique' && command.status == 'unconfirmed'" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-text-capitalize">confirmer</a> </td> -->
            <td>
              <router-link :to="'/ravitailler/vendeur/'+command.id_command" v-if="typeUser == 'logistique' && command.status == 'unconfirmed'"><span class="uk-button uk-border-rounded uk-button-small uk-text-small uk-text-capitalize uk-button-primary">Confirmer</span></router-link>
            </td>
            <td v-if="command.status == 'unconfirmed'">
              <a @click="abortCommandMaterial(command.id)" v-if="typeUser == 'logistique'" class="uk-button-small uk-text-small uk-button uk-button-danger uk-border-rounded uk-text-capitalize">Annuler</a>
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
          this.getMaterialCommande()
        },
        components : {
          Loading,
        },
        props : {
          theUser : String
        },
        data () {
          return {
            userList : [],
            filterData : {
              state : "unconfirmed",
              promoState : 'all',
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
            materialCommand : ['date','vendeurs','designation','quantite','parabole a livrer','promo','status'],
            isLoading : false,
            fullPage : true,
            afrocashLogistique : {},
          }
        },
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
          getSoldeLogistique : async function () {
            try {
              let response = await axios.get('/user/logistique/afrocash-solde')
              this.afrocashLogistique = response.data
            } catch (e) {
                alert(e)
            }
          },
          filterRequest : async function () {
            try {
              this.isLoading = true

              if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                this.filterData.user = this.userName
              }

              let response = await axios
                .get('/user/command/filter/'+this.filterData.user+'/'+this.filterData.state+'/'+this.filterData.promoState+'')

                if(response) {

                  this.commandList = response.data.all
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
            }
          },
          getMaterialCommande : async function () {
            try {
              Object.assign(this.$data,this.$options.data())
              this.isLoading = true
              var response = await axios.get('/user/commandes/all')
              var userResponse = await axios.get('/user/all-vendeurs')
              if(response && response.data.all.length && userResponse) {

                this.commandList = response.data.all

                this.nextUrl = response.data.next_url
                this.lastUrl = response.data.last_url
                this.perPage = response.data.per_page
                this.firstItem = response.data.first_item
                this.total = response.data.total

                this.userList = userResponse.data

                this.getSoldeLogistique()

              }
              this.isLoading = false
              
            } catch (e) {
              alert(e)
            }
          },
          abortCommandMaterial : async function(e) {
            try {
              var confirmState = confirm("Vous etes sur de vouloir effectue cette action ?")

              if(!confirmState) {
                return 0
              }

              this.isLoading = true
              let response = await axios.post('/logistique/commandes/abort', {
                id : e
              })
              if(response.data == 'done') {
                this.isLoading = false
                alert("Success!")
                this.getMaterialCommande()
                this.getSoldeLogistique()
              }
            } catch(error) {
                alert(error.response.data)
            }
          }
        },
        computed : {
          userName() {
            return this.$store.state.userName
          },
          userLocalisation() {
            return this.$store.state.userLocalisation
          },
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