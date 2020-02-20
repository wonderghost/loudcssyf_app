<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>

        <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-right">
          <li> <a href="#" @click="filterLivraison('non_confirmer')">en attente de validation</a> </li>
          <li> <a href="#" @click="filterLivraison('confirmer')">deja validee</a> </li>
        </ul>
        <div class="">
          <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
            <thead>
              <tr>
                <th v-for="head in livraison">{{head}}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="livraison in livraisonFilter.slice(start,end)">
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
          <ul class="uk-pagination uk-flex uk-flex-center">
            <li> <span> Page : {{currentPage}} </span> </li>
            <li> <button @click="previousPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> <span uk-pagination-previous></span> Precedent</button> </li>
            <li> <button @click="nextPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> Suivant <span uk-pagination-next></span> </button> </li>
          </ul>
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
                     <div v-for="qte in formData.quantite" class="uk-margin-small">
                       <input @blur="validSerial(qte-1)" required type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[qte-1]" :placeholder="'Serial Number '+qte">
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
      created () {
        this.isLoading = true
      },
      components : {
        Loading
      },
      props : {
        depotCourant : String,
        theUser : String
      },
        mounted() {
          this.getLivraisonMaterial()
        },
        data () {
          return {
            isLoading : false,
            fullPage : true,
            livraison : ['date','vendeurs','designation','commande','quantite','status','validation','depot'],
            start : 0,
            end : 10,
            currentPage : 1,
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
          getLivraisonMaterial : async function () {
            try {
              if(this.typeUser == 'admin') {

                var response = await axios.get('/admin/commandes/livraison')
              } else if(this.typeUser == 'logistique') {
                var response = await axios.get('/logistique/commandes/livraison')
              } else {
                var response = await axios.get('/user/commandes/livraison')
              }
              this.$store.commit('setLivraisonMaterial',response.data.original)
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
          nextPage : function () {
            if(this.livraisonFilter.length > this.end) {
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
          makeLivraison : function (id,quantite ,withSerial) {
            this.formData._token = this.myToken
            this.formData.livraison = id
            this.formData.quantite = quantite
            this.formData.with_serial = withSerial

          },
          sendLivraison : async function () {
            try {
              this.isLoading = true
              UIkit.modal($("#modal-livraison-send")).hide()
              let response = await axios.post('/user/livraison/confirm',this.formData)
              console.log(response.data)
              this.isLoading = false
              if(response.data == 'done') {
                UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Une livraison effectuee :-)</div>")
                  .then(function () {
                    location.reload()
                  })
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
                UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Commande validee avec success :-)</div>")
                  .then(function () {
                    location.reload()
                  })
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
            return  this.livraisonM.filter( (liv) => {
              return liv.validation === this.statusLivraison
            })
          },
          livraisonM() {
            if(!this.depotCourant) {
              return this.$store.state.livraisonMaterial
            }
            else {
              return this.$store.state.livraisonMaterial.filter((liv) => {
                return liv.depot == this.depotCourant
              })
            }
          },
          livraisonFilter () {
            if(!this.theUser) {
              return this.livraisonMaterial
            }
            else {
              return this.livraisonMaterial.filter( (liv) => {
                return liv.vendeur.match(this.theUser)
              })
            }
          }
          ,
          statusLivraison () {
            return this.$store.state.statusLivraison
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
