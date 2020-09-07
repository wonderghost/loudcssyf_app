<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>
        
        <h3><router-link class="uk-button uk-button-small uk-border-rounded uk-button-default uk-text-small" uk-tooltip="Retour" to="/commandes"><span uk-icon="arrow-left"></span></router-link> Livraison Materiel</h3>
        <hr class="uk-divider-small">

        <div class="">
          <div class="uk-grid-small uk-margin-top uk-flex uk-flex-right" uk-grid>
            <div class="uk-width-1-6@m">
              <label for=""><span uk-icon=""></span>  Status</label>
              <select class="uk-select uk-border-rounded">
                <option value="">Tous</option>
                <option value="unconfirmed">en attente</option>
                <option value="confirmed">confirmer</option>
                <option value="aborted">annuler</option>
              </select>
            </div>
            <div v-if="typeUser != 'v_da' && typeUser != 'v_standart'" class="uk-width-1-4@m">
              <label for="">Recherche</label>
              <input type="text" class="uk-input uk-border-rounded" placeholder="Recherche ...">
            </div>
            <!-- paginate component -->
            <div class="uk-width-1-3@m uk-margin-top">
              <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
              <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
              <button @click="getLivraisonMaterial()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
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
                <th v-for="(head,index) in livraison" :key="index">{{head}}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(livraison,index) in livraisonList" :key="index">
                <td>{{livraison.date}}</td>
                <td>{{livraison.vendeur}}</td>
                <td>{{livraison.produit}}</td>
                <td>{{livraison.command}}</td>
                <td>{{livraison.quantite}}</td>
                <td v-if="livraison.status == 'livred'" class="uk-text-success">{{livraison.status}}</td>
                <td v-else class="uk-text-danger">{{livraison.status}}</td>
                <td>{{livraison.validation}}</td>
                <td>{{livraison.depot}}</td>
                <td>
                   <template id="" v-if="typeUser == 'logistique' && livraison.status=='livred' && livraison.validation == 'non_confirmer'">
                    <button @click="formDataConfirm.livraison = livraison" uk-toggle="target : #modal-livraison-validate" type="button" name="button" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-text-capitalize">confirmer</button>
                   </template>
                   <template v-if="livraison.status == 'livred' && livraison.filename !== 'undefined'" id="">
                     <a download :href="livraison.filename" class="uk-button uk-button-small uk-button-default uk-text-capitalize uk-border-rounded uk-box-shadow-small">details <span uk-icon="icon : more"></span> </a>
                   </template>
                   <template id="" v-if="livraison.status == 'unlivred' && typeUser == 'gdepot'">
                     <button
                        @click="makeLivraison(livraison.id ,livraison.quantite , livraison.with_serial)"
                        uk-toggle="target : #modal-livraison-send"
                        type="button"
                        class="uk-button uk-button-small uk-text-capitalize uk-button-primary uk-border-rounded uk-box-shadow-small">
                        Livrer
                      </button>
                   </template>
                   <template id="" v-if="livraison.status == 'unlivred' && (typeUser == 'v_da' || typeUser == 'v_standart')">
                     <button @click="showConfirmationCode(livraison)" type="button" class="uk-button uk-button-primary uk-button-small uk-border-rounded" name="button">code </button>
                   </template>
                 </td>
              </tr>
            </tbody>
          </table>
          <div class="uk-flex uk-flex-center">
            <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
          </div>
        </div>
    <template id="" v-if="typeUser == 'gdepot'">
      <div id="modal-livraison-send" uk-modal="esc-close:false;bg-close : false">
        <div class="uk-modal-dialog">
          <div class="uk-modal-body">
            <form @submit.prevent="sendLivraison()">
              <!-- Erreor block -->
              <template v-if="errors.length" v-for="error in errors">
              <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                <a href="#" class="uk-alert-close" uk-close></a>
                <p>{{error}}</p>
              </div>
            </template>
            <ul uk-accordion="collapsible: false">
             <li>
                 <a class="uk-accordion-title" href="#">Numero de serie</a>
                 <div class="uk-accordion-content">
                   <template id="" v-if="formData.with_serial == 1">
                     <div class="uk-alert-info " uk-alert>
                       <p>Remplissez les champs vides</p>
                     </div>
                     <div v-for="qte in formData.quantite" :key="qte" class="uk-margin-small">
                       <input @blur="validSerial(qte-1)" type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[qte-1]" :placeholder="'Serial Number '+qte">
                     </div>
                   </template>
                   <template id="" v-else>
                     <div class="uk-alert-info" uk-alert>
                       <p>Numero de Serie inexistant</p>
                       <p>Cliquez sur `suivant` pour continuer :-)</p>
                     </div>
                   </template>
                 </div>
             </li>
             <li>
               <a href="#" class="uk-accordion-title">Suivant</a>
               <div class="uk-accordion-content">
                 <div class="uk-margin-small">
                   <label for="">Entrez le code de confirmation</label>
                   <input type="text" v-model="formData.confirm_code" class="uk-input uk-border-rounded" placeholder="Saisissez le code de confirmation">
                 </div>
                 <div class="uk-margin-small">
                   <label for="">Confirmez le mot de passe</label>
                   <input type="password" v-model="formData.password" class="uk-input uk-border-rounded" value="" placeholder="Saisissez le mot de passe">
                 </div>
                 <button  type="submit" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-primary">Validez</button>
               </div>
             </li>
           </ul>
         </form>
         </div>
         <div class="uk-modal-footer">
           <button type="submit" class="uk-modal-close uk-button uk-button-small uk-button-danger uk-border-rounded" name="button">Fermer</button>
         </div>
        </div>
      </div>
    </template>
    <!-- VALIDATION DE LA LIVRAISON CHEZ LA LOGISTIQUE -->
    <template id="" v-if="typeUser == 'logistique'">
      <div id="modal-livraison-validate" class="uk-flex-top" uk-modal>
          <div class="uk-modal-dialog uk-modal-body">
              <button class="uk-modal-close-default" type="button" uk-close></button>
              <p class="">
                Vous confirmez l'envoi de : <span class="uk-text-bold">{{formDataConfirm.livraison.quantite}} {{formDataConfirm.livraison.produit}}</span> chez  :  <span class="uk-text-bold">{{formDataConfirm.livraison.vendeur}}</span>
              </p>

              <hr class="uk-divider-small">
              <form @submit.prevent="confirmLivraison()">
                <!-- Erreor block -->
                <template v-if="errors.length" v-for="error in errors">
                <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                  <a href="#" class="uk-alert-close" uk-close></a>
                  <p>{{error}}</p>
                </div>
              </template>
                <div class="uk-margin-small">
                  <label for="">Confirmez votre Mot de passe</label>
                  <input type="password" v-model="formDataConfirm.password_confirmation" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe ici" value="">
                </div>
              <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">validez</button>
            </form>
          </div>
      </div>
    </template>
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
          this.getLivraisonMaterial()
        },
        data () {
          return {
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
            livraison : ['date','vendeurs','designation','commande','quantite','status','validation','depot'],
            livraisonList : [],
            depot : "",
            formData : {
              _token : "",
              livraison : "",
              with_serial : 0,
              confirm_code : "",
              password : "",
              quantite  : 0,
              serial_number : []
            },
            errors : [],
            formDataConfirm : {
              _token : "",
              livraison : "",
              password_confirmation : ""
            }
          }
        },
        methods : {
          paginateFunction : async function (url) {
            try {
              
              let response = await axios.get(url)
              if(response && response.data) {
                
                this.livraisonList = response.data.original.all

                this.nextUrl = response.data.original.next_url
                this.lastUrl = response.data.original.last_url
                this.currentPage = response.data.original.current_page
                this.firstPage = response.data.original.first_page
                this.firstItem = response.data.original.first_item,
                this.total = response.data.original.total
              }
            }
            catch(error) {
              alert("Erreur!")
              console.log(error)
            }
          },
          getLivraisonMaterial : async function () {
            try {

              this.isLoading = true

              if(this.typeUser == 'admin') {

                var response = await axios.get('/admin/commandes/livraison')
              } else if(this.typeUser == 'logistique') {
                var response = await axios.get('/logistique/commandes/livraison')
              } else {
                var response = await axios.get('/user/commandes/livraison')
              }
              this.livraisonList = response.data.original.all

              this.nextUrl = response.data.original.next_url
              this.lastUrl = response.data.original.last_url
              this.perPage = response.data.original.per_page
              this.firstItem = response.data.original.first_item
              this.total = response.data.original.total


              this.$store.commit('setLivraisonMaterial',response.data.original.all)
              this.isLoading = false
            } catch (e) {
              alert(e)
            }
          },
          filterLivraison : function (status) {
            this.currentPage = 1
            this.start = 0
            this.end = 10
            this.$store.commit('setStateLivraison',status)
          },
          makeLivraison : function (id,quantite ,withSerial) {
            this.formData._token = this.myToken
            this.formData.livraison = id
            this.formData.quantite = quantite
            this.formData.with_serial = withSerial

          },
          sendLivraison : async function () {
            this.errors = []
            try {
              this.isLoading = true
              UIkit.modal($("#modal-livraison-send")).hide()
              let response = await axios.post('/user/livraison/confirm',this.formData)
              console.log(response.data)
              this.isLoading = false
              if(response.data == 'done') {
                alert("Success !")
                this.getLivraisonMaterial()
              }
            } catch (error) {
              this.isLoading = false
              UIkit.modal($("#modal-livraison-send")).show()
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
          validSerial : async function (index) {
            try {
              let response = await axios.post('/user/livraison/validate-serial',{
                ref : this.formData.serial_number[index]
              })

            } catch (error) {
              if(error.response.data.errors) {
                let errorTab = error.response.data.errors
                for (var prop in errorTab) {
                    UIkit.notification({
                      message: errorTab[prop][0],
                      status: 'danger',
                      pos: 'top-center',
                      timeout: 5000
                  });
                }
              } else {
                  UIkit.notification({
                    message:error.response.data,
                    status: 'danger',
                    pos: 'top-center',
                    timeout: 5000
                });
              }
            }
          },
          showConfirmationCode : function (livraison) {
            UIkit.modal.alert("<div class='uk-alert-info uk-border-rounded' uk-alert>Votre Code de confirmation pour la commande: <span class='uk-text-bold'>"+livraison.command+"</span> , est : <span class='uk-text-bold'>"+livraison.code_livraison+"</span></div>")
          },
          confirmLivraison : async function () {
            this.isLoading = true
            UIkit.modal($("#modal-livraison-validate")).hide()
            try {
              this.formDataConfirm._token = this.myToken
              let response = await axios.post('/user/commandes/validate-livraison-serials',{
                _token : this.formDataConfirm._token,
                livraison : this.formDataConfirm.livraison.id,
                password_confirmation : this.formDataConfirm.password_confirmation
              })
              if(response.data == 'done') {
                this.isLoading = false
                alert("Success !")
                this.getLivraisonMaterial()
              }
            }
            catch (error) {
              this.isLoading = false
              UIkit.modal($("#modal-livraison-validate")).show()
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
          livraisonMaterial () {
            return  this.$store.state.livraisonMaterial.filter( (liv) => {
              return liv.validation === this.statusLivraison
            })
          },
          livraisonFilter () {
            return this.livraisonMaterial.filter((liv) => {
              if(this.typeUser == 'admin' || this.typeUser == 'logistique' || this.typeUser == 'commercial') {
                return liv
              }
              else if(this.typeUser == 'gdepot') {
                return liv.depot.match(this.userLocalisation)
              }
              else if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                return liv.vendeur.match(this.userLocalisation)
              }
            })
          },
          statusLivraison () {
            return this.$store.state.statusLivraison
          },
          typeUser () {
            return this.$store.state.typeUser
          },
          myToken () {
            return this.$store.state.myToken
          },
          userLocalisation() {
            return this.$store.state.userLocalisation
          }
        }
    }
</script>
