<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

    <h3>Inventaire Reseaux</h3>
    <hr class="uk-divider-small">

    <download-to-excel :data-to-export="serialList" :data-fields="fieldsExport" file-name="inventaire-stock"></download-to-excel>
        
    <div class="uk-child-width-1-6@m uk-grid-small" uk-grid>
      <template>
        <!-- INVENTAIRE DES MATERIELS -->
        <div v-for="(mat,index) in materials" class="" :key="index">
          <div class="uk-card uk-border-rounded uk-box-shadow-hover-small uk-background-muted uk-dark uk-card-body uk-padding-small">
            <h3 class="uk-card-title">{{ mat.article }}</h3>
            <p>
              <ul class="uk-list uk-list-divider">
                <li>
                  <span>Qte : {{mat.quantite}}</span> ,
                  <span>TTC : {{mat.prix_ttc | numFormat}}</span>
                </li>
                <li>
                  <span>HT : {{mat.ht | numFormat}}</span> ,
                  <span>Marge : {{mat.marge | numFormat}}</span>
                </li>
              </ul>
            </p>
          </div>
        </div>
      </template>
      <template id="" v-if="typeUser == 'v_da' || typeUser == 'v_standart'">
        <!-- INVENTAIRE DES SOLDES DES CREDITS -->
        <div v-for="(cred , name) in credits" class="" :key="name">
          <div class="uk-card uk-border-rounded uk-box-shadow-hover-small uk-background-muted uk-dark uk-card-body uk-padding-small">
            <h3 class="uk-card-title uk-text-capitalize">{{name}}</h3>
            <p>
              <ul class="uk-list uk-list-divider">
                <li>
                  <span>{{cred | numFormat}}</span>
                </li>
                <li> <span>GNF</span> </li>
              </ul>
            </p>
          </div>
        </div>
      </template>
      <template v-if="typeUser == 'admin' || typeUser == 'commercial'">
        <div class="uk-width-1-1@m">
        <ul uk-accordion>
          <li>
            <a class="uk-accordion-title uk-text-capitalize uk-border-rounded uk-button-default uk-box-shadow-small uk-button uk-button-small uk-width-1-6@m" href="#">Transfert Materiel</a>
            <div class="uk-accordion-content">
              <div class="uk-card uk-card-default uk-border-rounded uk-grid-small uk-box-shadow-small" uk-grid>
                <div class="uk-card-body uk-width-1-1@m">
                  <!-- ERROR BLOCK -->
                    <template v-if="errors.length">
                      <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" v-for="(error,index) in errors" uk-alert :key="index">
                        <a href="#" class="uk-alert-close" uk-close></a>
                        <p>{{error}}</p>
                      </div>
                    </template>
                    <!-- // -->
                  <form @submit.prevent="transfertMaterialToOtherUser()" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m uk-grid-small" uk-grid>
                      <div class="uk-width-1-2@m uk-margin-remove">
                        <label for=""><span uk-icon="icon : users"></span> Expediteur</label>
                        <select name="" id="" class="uk-select uk-border-rounded" v-model="transfertData.expediteur">
                          <!-- LIST USERS -->
                          <option value="">-- Selectionnez un vendeur --</option>
                          <option :value="u.username" :key="u.username" v-for="u in users">{{u.localisation}}</option>
                        </select>
                      </div>
                      <div class="uk-width-1-2@m uk-margin-remove">
                        <label for=""><span uk-icon="icon : users"></span> Destinataire</label>
                        <select class="uk-select uk-border-rounded" v-model="transfertData.destinataire">
                          <option value="">-- Selectionnez un vendeur --</option>
                          <option v-for="u in users" :key="u.username" :value="u.username">{{u.localisation}}</option>
                        </select>
                      </div>
                      <div class="uk-width-1-6@m uk-margin-remove">
                        <label for="">Quantite</label>
                        <input v-model="transfertData.quantite" type="number" max="10" class="uk-input uk-border-rounded" min="1">
                      </div>
                      <div class="uk-width-1-1@m uk-margin-remove">
                        <label for=""><span uk-icon="icon : commenting"></span> Motif du transfert</label>
                        <textarea v-model="transfertData.motif_transfert" cols="30" rows="4" class="uk-textarea uk-border-rounded"></textarea>
                      </div>
                      <div class="uk-width-1-1@m uk-margin-remove">
                        <label for=""><span uk-icon="icon : lock"></span> Confirmez le mot de passe</label>
                        <input type="password" v-model="transfertData.password" class="uk-input uk-border-rounded">
                      </div>
                    </div>
                    <div class="uk-width-1-2@m">
                      <div class="uk-grid-small" uk-grid>
                        <div v-for="i in parseInt(transfertData.quantite)" :key="i" class="uk-width-1-2@m uk-margin-remove">
                          <label for="">Serial-{{i}}</label>
                          <input type="text" v-model="transfertData.serialsNumber[i-1]" class="uk-input uk-border-rounded" 
                            :placeholder="'Materiel-'+(i)">
                        </div>
                      </div>
                    </div>
                    <div>
                      <button type="submit" class="uk-button uk-button-small uk-border-rounded uk-button-primary">
                      Envoyez
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </li>
          <li>
            <a class="uk-accordion-title uk-text-capitalize uk-border-rounded uk-button-default uk-box-shadow-small uk-button uk-button-small uk-width-1-6@m" href="#">Materiel defectueux</a>
            <div class="uk-accordion-content">
              <div class="uk-card uk-card-default uk-border-rounded">
                <div class="uk-card-body">
                  <template v-if="errors.length" v-for="error in errors">
                      <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
                        <a href="#" class="uk-alert-close" uk-close></a>
                        <p>{{error}}</p>
                      </div>
                    </template>
                  <form @submit.prevent="makeMaterialDefuctueux()" class="uk-grid-small uk-width-1-2@m" uk-grid>
                    <div class="uk-width-1-2@m">
                      <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
                      <select v-model="replaceMaterial.vendeur" class="uk-select uk-border-rounded">
                        <option value="">-- Selectionnez un vendeur --</option>
                        <option :value="u.username" v-for="u in users">{{u.localisation}}</option>
                      </select>
                    </div>
                    <div class="uk-width-1-2@m">
                      <label for=""><span uk-icon="icon : info"></span> Materiel Defectueux</label>
                      <input v-model="replaceMaterial.defectuous" type="text" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-width-1-2@m">
                      <label for=""><span uk-icon="icon : info"></span> Materiel remplacant</label>
                      <input type="text" v-model="replaceMaterial.replacement" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-width-1-2@m">
                      <label for=""><span uk-icon="icon : lock"></span> Confirmez le mot de passe</label>
                      <input type="password" v-model="replaceMaterial.password" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-width-1-1@m">
                      <label for=""><span uk-icon="icon : pencil"></span> Motif</label>
                      <textarea v-model="replaceMaterial.motif" class="uk-textarea uk-border-rounded" cols="30" rows="10"></textarea>
                    </div>
                    <div>
                      <button class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
      </template>
    </div>

    <!-- // -->
    <!-- LISTE DES NUMEROS DE SERIES -->
    <div class="uk-margin-top">
      <div class="uk-grid-small" uk-grid>
        <div class="uk-width-1-4@m">
          <label for="">Status Materiel</label>
          <select @change="filterRequest()" v-model="filterData.status" class="uk-select uk-border-rounded">
            <option value="all">Tous</option>
            <option value="actif">actif</option>
            <option value="inactif">inactif</option>
          </select>
        </div>
        <!-- <div class="uk-width-1-4@m">
          <label for="">Depot Origine</label>
          <select @change="filterRequest()" v-model="filterData.origine" class="uk-select uk-border-rounded">
            <option value="all">Tous les depots</option>
          </select>
        </div> -->
        <div v-if="typeUser == 'admin' || typeUser == 'commercial' || typeUser == 'logistique'" class="uk-width-1-4@m">
          <label for=""><span uk-icon="users"></span> Vendeurs</label>
          <select @change="filterRequest()" v-model="filterData.vendeurs" class="uk-select uk-border-rounded">
            <option value="all">Tous les vendeurs</option>
            <option :value="u.username" v-for="(u,index) in users" :key="index">{{u.localisation}}</option>
          </select>
        </div>
         <!-- paginate component -->
        <div class="uk-width-1-4@m uk-margin-top">
          <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
          <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
          <button @click="getSerialNumberList()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
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
      <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
        <thead>
          <tr>
            <th v-for="(head,index) in tableHead" :key="index">{{head}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="serial in serialList" :key="serial.numero_serie">
            <td>{{serial.numero_serie}}</td>
            <td>{{serial.vendeurs}}</td>
            <td>{{serial.article}}</td>
            <template v-if="serial.status == 'actif'">
              <td><span class="uk-alert-success" uk-tooltip="actif" uk-icon="check"></span></td>
            </template>
            <template v-else>
              <td><span class="uk-alert-primary" uk-tooltip="inactif" uk-icon="minus"></span></td>
            </template>
            <td>{{serial.origine}}</td>
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
      components : {
        Loading
      },
        mounted() {
          UIkit.offcanvas($("#side-nav")).hide();
          this.getSerialNumberList()
          this.getMaterials()
          if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
            this.userFilter = this.userName
            this.getCreditForVendeurs()
          }
        },
        data () {
          return {
            fieldsExport :{
              'Numero Serie' : 'numero_serie',
              'Vendeur' : 'vendeurs',
              'Article' : 'article',
              'Status' : 'status',
              'Origine' : 'origine'
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

            serialList : [],

            isLoading : false,
            fullPage : true,
            tableHead : ['Numero Serie','Vendeur','Article','Status','Origine'],
            materialState : "inactif",
            
            materials : [],
            credits : [],
            users : [],
            userFilter : "",

            transfertData : {
              _token : "",
              quantite : 1,
              expediteur : "",
              destinataire : "",
              motif_transfert : "",
              password : "",
              serialsNumber : []
            },
            replaceMaterial : {
              _token : "",
              vendeur : "",
              defectuous : "",
              replacement : "",
              password : "",
              motif : ""
            },
            errors : [],
            filterData : {
              _token : "",
              status : "all",
              origine : "",
              vendeurs : "all"
            }
          }
        },
        methods : {
          paginateFunction : async function (url) {
            try {
              this.isLoading = true
              let response = await axios.get(url)
              if(response && response.data) {
                
                this.serialList = response.data.all

                this.nextUrl = response.data.next_url
                this.lastUrl = response.data.last_url
                this.currentPage = response.data.current_page
                this.firstPage = response.data.first_page
                this.firstItem = response.data.first_item,
                this.total = response.data.total

                this.isLoading = false
              }
            }
            catch(error) {
              alert("Erreur!")
              console.log(error)
            }
          },
          // ACTIVATION DU FILTRE
          filterRequest : async function() {
            try {
              this.isLoading = true

              this.filterData._token = this.myToken
              let response = await axios.post('/admin/inventory-stock/filter',this.filterData)

              if(response) {

                this.serialList = response.data.all

                this.nextUrl = response.data.next_url
                this.lastUrl = response.data.last_url
                this.perPage = response.data.per_page
                this.firstItem = response.data.first_item
                this.total = response.data.total

                this.isLoading = false
              }
            }
            catch(error) {
              alert(error)
            }
          },
          // REQUEST DE REMPLACEMENT DES MATERIELS DEFECTUEUX
          makeMaterialDefuctueux : async function() {
            try {
                this.isLoading = true
                this.replaceMaterial._token = this.myToken
                let response = await axios.post('/admin/inventory/replace-material-defectuous',this.replaceMaterial)
                if(response.data == 'done') {
                  this.isLoading = false
                  UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Operation effectuee avec success !</div>")
                    .then(function() {
                      location.reload()
                    })
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
          transfertMaterialToOtherUser : async function() { // envoi de la requete de transfert de materiel d'un vendeur a l'autre
            try {
              this.isLoading = true
              this.transfertData._token = this.myToken
              let response = await axios.post('/admin/inventory/transfert-material',this.transfertData)
              if(response.data == 'done') {
                UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Transfert Effectue!</div>")
                  .then(function() {
                    location.reload()
                  })
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
          getSerialNumberList : async function () { // listing des numeros de series
            try {
              this.isLoading = true
              // reinitialisation des donnees pour le filtre
              this.filterData.vendeurs = "all"
              this.filterData.status = "all"

              if(this.typeUser == 'admin' || this.typeUser == 'commercial') {
                var response = await axios.get("/admin/inventory/get-serial-number-list")
              }else {
                var response = await axios.get("/user/inventory/get-serial-number-list")
              }
              if(response && response.data) {
                this.$store.commit('setSerialNumberList',response.data.all)
                
                this.serialList = response.data.all

                this.nextUrl = response.data.next_url
                this.lastUrl = response.data.last_url
                this.perPage = response.data.per_page
                this.firstItem = response.data.first_item
                this.total = response.data.total
                this.isLoading = false
              }
            } catch (e) {
              alert(e)
            }
          },
          
          getMaterials : async function () { //recuperation de l'inventaire material
            try {
              this.isLoading = true
              if(this.typeUser == 'admin' || this.typeUser == 'commercial') {
                var response = await axios.get("/admin/inventory/all-material")
              }else if(this.typeUser == 'logistique') {
                var response = await axios.get("/user/inventory/all-material")
              } else if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                var response = await axios.get("/user/inventory/all-vendeur-material")
              }
              this.materials = response.data
              this.listUserForFilter()
              this.isLoading = false
            } catch (e) {
                alert(e)
            }
          },
          listUserForFilter : async function () { //recuperation de la liste des utilisateurs pour le filtre
            try {
              this.isLoading = true
              var response = await axios.get("/admin/all-vendeurs")
              this.users = response.data
              this.isLoading = false
            } catch (e) {
                alert(e)
            }
          },
          getCreditForVendeurs : async function () { // inventaire des soldes du vendeur
            try {
              this.isLoading = true
               if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                var response = await axios.get("/user/inventory/all-credit-vendeurs")
                this.credits = response.data
                this.isLoading = false
              }
            } catch (e) {
                alert(e)
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
          userName() {
            return this.$store.state.userName
          }
        }
    }
</script>
