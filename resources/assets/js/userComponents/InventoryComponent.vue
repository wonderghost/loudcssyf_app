<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="true"
        :is-full-page="fullPage"></loading>
    <div class="uk-child-width-1-6@m uk-grid-small" uk-grid>
      <template>
        <!-- INVENTAIRE DES MATERIELS -->
        <div v-for="mat in materials" class="">
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
        <div v-for="(cred , name) in credits" class="">
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
      <template v-if="typeUser == 'admin'">
        <div class="uk-width-1-1@m">
        <ul uk-accordion>
          <li>
            <a class="uk-accordion-title uk-text-capitalize uk-border-rounded uk-button-default uk-box-shadow-small uk-button uk-button-small uk-width-1-6@m" href="#">Transfert Materiel</a>
            <div class="uk-accordion-content">
              <div class="uk-card uk-card-default uk-border-rounded uk-grid-small uk-box-shadow-small" uk-grid>
                <div class="uk-card-body uk-width-1-1@m">
                  <form class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m uk-grid-small" uk-grid>
                      <div class="uk-width-1-1@m uk-margin-remove">
                        <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
                        <select name="" id="" class="uk-select uk-border-rounded"></select>
                      </div>
                      <div class="uk-width-1-6@m uk-margin-remove">
                        <label for="">Quantite</label>
                        <input v-model="transfertData.quantite" type="number" max="10" class="uk-input uk-border-rounded" min="1">
                      </div>
                      <div class="uk-width-1-1@m uk-margin-remove">
                        <label for=""><span uk-icon="icon : commenting"></span> Motif du transfert</label>
                        <textarea cols="30" rows="4" class="uk-textarea uk-border-rounded"></textarea>
                      </div>
                      <div class="uk-width-1-1@m uk-margin-remove">
                        <label for=""><span uk-icon="icon : lock"></span> Confirmez le mot de passe</label>
                        <input type="password" class="uk-input uk-border-rounded">
                      </div>
                      <div>
                        <button type="button" class="uk-button uk-button-small uk-border-rounded uk-button-primary">
                        Envoyez
                        </button>
                      </div>
                    </div>
                    <div class="uk-width-1-2@m">
                      <div class="uk-grid-small" uk-grid>
                        <div v-for="i in parseInt(transfertData.quantite)" class="uk-width-1-2@m uk-margin-remove">
                          <label for="">Serial-{{i}}</label>
                          <input type="text" class="uk-input uk-border-rounded">
                        </div>
                      </div>
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

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a @click="materialState = 'inactif',start = 0 , end =15 , currentPage = 1"  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiels Inactifs</a></li>
        <li><a @click="materialState = 'actif' , start = 0 , end =15 , currentPage = 1"  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiels Actifs</a></li>
    </ul>
    <!-- FILTER BY USERS -->
    <template v-if="typeUser == 'admin' || typeUser == 'logistique'" id="">
      <div class="uk-margin-remove uk-flex uk-flex-right">
        <select v-model="userFilter" class="uk-input uk-border-rounded uk-width-1-3@m" name="">
          <option value="">Tous les vendeurs</option>
          <option v-for="user in users" :value="user.username">{{user.localisation}}</option>
        </select>
      </div>
    </template>
    <!-- // -->
    <!-- LISTE DES NUMEROS DE SERIES -->
    <div class="">
      <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
        <thead>
          <tr>
            <th v-for="head in tableHead">{{head}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="serial in ListSerialNumber.slice(start,end)">
            <td>{{serial.numero_serie}}</td>
            <td>{{serial.vendeurs}}</td>
            <td>{{serial.article}}</td>
            <td>{{serial.status}}</td>
            <td>{{serial.origine}}</td>
          </tr>
        </tbody>
      </table>
      <ul class="uk-pagination uk-flex uk-flex-center">
        <li> <span> Page : {{currentPage}} </span> </li>
        <li> <button type="button" @click="previousPage()" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default" name="button"> <span uk-pagination-previous></span> Previous</button> </li>
        <li> <button type="button" @click="nextPage()" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default" name="button"> Suivant <span uk-pagination-next></span> </button> </li>
      </ul>
    </div>
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
        mounted() {
          this.getSerialNumberList()
          this.getMaterials()
          if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
            this.userFilter = this.userName
            this.getCreditForVendeurs()
          }
        },
        data () {
          return {
            isLoading : false,
            fullPage : true,
            tableHead : ['Numero Serie','Vendeur','Article','Status','Origine'],
            materialState : "inactif",
            start : 0,
            end : 15,
            currentPage : 1,
            materials : [],
            credits : [],
            users : [],
            userFilter : "",
            transfertData : {
              quantite : 0
            }
          }
        },
        methods : {
          getSerialNumberList : async function () { // listing des numeros de series
            try {
              if(this.typeUser == 'admin') {
                var response = await axios.get("/admin/inventory/get-serial-number-list")
              }else {
                var response = await axios.get("/user/inventory/get-serial-number-list")
              }
              this.isLoading = false
              this.$store.commit('setSerialNumberList',response.data)
            } catch (e) {
              alert(e)
            }
          },
          nextPage : function () {
            if(this.ListSerialNumber.length > this.end) {
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
          getMaterials : async function () { //recuperation de l'inventaire material
            try {
              if(this.typeUser == 'admin') {
                var response = await axios.get("/admin/inventory/all-material")
              }else if(this.typeUser == 'logistique') {
                var response = await axios.get("/user/inventory/all-material")
              } else if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                var response = await axios.get("/user/inventory/all-vendeur-material")
              }
              this.materials = response.data
              this.listUserForFilter()
            } catch (e) {
                alert(e)
            }
          },
          listUserForFilter : async function () { //recuperation de la liste des utilisateurs pour le filtre
            try {
              var response = await axios.get("/admin/all-vendeurs")
              this.users = response.data
            } catch (e) {
                alert(e)
            }
          },
          getCreditForVendeurs : async function () { // inventaire des soldes du vendeur
            try {
               if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                var response = await axios.get("/user/inventory/all-credit-vendeurs")
              }
              this.credits = response.data
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
          serialNumberList () {
            return this.$store.state.serialNumberList.filter( (serial) => {
              return serial.status == this.materialState
            })
          },
          serialNumberForVendeurs() {
            return this.serialNumberList.filter( (userSerial) =>  {
              return userSerial.user_id === this.userFilter
            })
          },
          ListSerialNumber () {
            if(this.userFilter == "") {
              return this.serialNumberList
            } else {
              return this.serialNumberForVendeurs
            }
          },
          userName() {
            return this.$store.state.userName
          }
        }
    }
</script>
