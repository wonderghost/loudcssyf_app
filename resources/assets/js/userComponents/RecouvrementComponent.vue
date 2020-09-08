<template>
  <div class="uk-container uk-container-large uk-margin-large-bottom">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
      <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Nouveau Recouvrement</a></li>
      <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Tous les recouvrements</a></li>
      <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Toutes les transactions</a></li>
    </ul>

    <!-- Erreor block -->
      <template v-if="errors.length" v-for="error in errors">
      <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
        <a href="#" class="uk-alert-close" uk-close></a>
        <p>{{error}}</p>
      </div>
    </template>

    <ul class="uk-switcher uk-margin">
      <li>
        <template v-if="typeUser == 'coursier'">
          <form @submit.prevent="sendRecouvrement()" class="uk-width-1-3@m">
          <h3>Enregistrer un recouvrement</h3>
          <div class="uk-margin-small">
            <label for=""><span uk-icon="users"></span> Vendeurs</label>
            <select class="uk-select uk-border-rounded uk-margin-small" @change="checkVendeur()" v-model="recouvrementData.vendeurs">
              <option value="">--Selectionnez un vendeur --</option>
              <option :value="v.username" v-for="v in vStandart">{{ v.localisation }}</option>
            </select>
          </div>
          <div class="uk-margin-small">
            <label for=""><span uk-icon="credit-card"></span> Montant du</label>
            <span class="uk-input uk-border-rounded">{{recouvrementData.montant_du | numFormat}}</span>
          </div>
          <div class="uk-margin-small">
            <label for=""><span uk-icon="credit-card"></span> Montant</label>
            <input type="number" class="uk-input uk-border-rounded" v-model="recouvrementData.montant">
          </div>
          <div class="uk-margin-small">
            <label for=""> Numero Recu</label>
            <input type="text" class="uk-input uk-border-rounded" v-model="recouvrementData.numero_recu">
          </div>
          <button type="submit" class="uk-button uk-width-1-1@s uk-width-1-6@m uk-text-capitalize uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
        </form>
        </template>
        <template v-else>
          <div class="uk-alert-warning uk-border-rounded uk-box-shadow-small" uk-alert>
            <p class="uk-text-center"> <span uk-icon="icon : warning"></span>   Vous n'etes pas autorise a effectuer cette action !</p>
          </div>
        </template>
      </li>
      <li>
        <!-- paginate -->
        <div class="uk-width-1-4@m">
          <span class="">{{recouvrement.firstItem}} - {{recouvrement.firstItem + recouvrement.perPage}} sur {{recouvrement.total}}</span>
          <a v-if="recouvrement.currentPage > 1" @click="paginateFunction(recouvrement.firstPage,recouvrement)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
          <template v-if="recouvrement.lastUrl">
            <button @click="paginateFunction(recouvrement.lastUrl,recouvrement)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Precedent">
              <span uk-icon="chevron-left"></span>
            </button>
          </template>
          <template v-if="recouvrement.nextUrl">
            <button @click="paginateFunction(recouvrement.nextUrl,recouvrement)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize u-t uk-text-small" uk-tooltip="Suivant">
              <span uk-icon="chevron-right"></span>
            </button>
          </template>
        </div>
        <!-- // -->
        <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-striped uk-table-responsive">
          <thead>
            <tr>
              <th>Date</th>
              <th>Vendeurs</th>
              <th>Montant</th>
              <th>Numero Recu</th>
              <th>-</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(r,index) in recouvrement.list" :key="index">
              <td>{{r.date}}</td>
              <td>{{r.vendeurs}}</td>
              <td>{{r.montant}}</td>
              <td>{{r.numero_recu}}</td>
              <td>
                <button class="uk-button uk-button-small uk-border-rounded uk-button-primary" uk-tooltip="Cliquez pour voir les details"><span uk-icon="icon : more"></span></button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="uk-flex uk-flex-center">
          <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
        </div>
      </li>
      <li>
        <!-- TRANSACTIONS -->
        <div class="uk-grid" uk-grid>
          <div class="uk-width-1-4@m">
            <label for=""> <span uk-icon="icon : calendar"></span> Du</label>
            <input type="date" class="uk-input uk-border-rounded" value="">
          </div>
          <div class="uk-width-1-4@m">
            <label for=""><span uk-icon="icon : calendar"></span> Au</label>
            <input type="date" class="uk-input uk-border-rounded" value="">
          </div>
          <div class="uk-width-1-4@m">
            <label for=""> <span uk-icon="icon : users"></span> Vendeurs</label>
            <select class="uk-select uk-border-rounded" v-model="filterVendeurs">
              <option value="">Tous les Vendeurs</option>
              <option :value="v.localisation" v-for="v in vStandart" :key="v.localisation">{{v.localisation}}</option>
            </select>
          </div>
           <!-- paginate -->
        <div class="uk-width-1-4@m">
          <span class="">{{transaction.firstItem}} - {{transaction.firstItem + transaction.perPage}} sur {{transaction.total}}</span>
          <a v-if="transaction.currentPage > 1" @click="paginateFunction(transaction.firstPage,transaction)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
          <template v-if="transaction.lastUrl">
            <button @click="paginateFunction(transaction.lastUrl,transaction)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Precedent">
              <span uk-icon="chevron-left"></span>
            </button>
          </template>
          <template v-if="transaction.nextUrl">
            <button @click="paginateFunction(transaction.nextUrl,transaction)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize u-t uk-text-small" uk-tooltip="Suivant">
              <span uk-icon="chevron-right"></span>
            </button>
          </template>
        </div>
        <!-- // -->
        </div>
        <table class="uk-table uk-table-striped uk-table-small uk-table-hover uk-table-divider uk-table-responsive">
          <thead>
            <tr>
              <th>Date</th>
              <th>Expediteur</th>
              <th>Destinataire</th>
              <th>Montant</th>
              <th>Type</th>
              <th>Status de recouvrement</th>
              <th>Recu</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in TransactionFiltree">
              <td>{{t.date}}</td>
              <td>{{t.expediteur}}</td>
              <td>{{t.destinataire}}</td>
              <td>{{t.montant}}</td>
              <td>{{t.type}}</td>
              <td>{{t.status}}</td>
              <td>{{t.recu}}</td>
            </tr>
          </tbody>
        </table>
        <div class="uk-flex uk-flex-center">
          <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
        </div>
      </li>
    </ul>
  </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

  export default {
    components : {
      Loading
    },
    props : {
      theUser : String
    },
    mounted() {
      UIkit.offcanvas($("#side-nav")).hide();
      this.recouvrementData._token = this.myToken
      this.getData()
    },
    data() {
      return {
        recouvrement : {
  // paginate
          nextUrl : "",
          lastUrl : "",
          perPage : "",
          currentPage : 1,
          firstPage : "",
          firstItem : 1,
          total : 0,
          list : []
  // #####                          
        },
        transaction : {
  // paginate
          nextUrl : "",
          lastUrl : "",
          perPage : "",
          currentPage : 1,
          firstPage : "",
          firstItem : 1,
          total : 0,
          list : []
  // #####                          
        },
        isLoading : false,
        fullPage : true,
        recouvrementData : {
          _token : "",
          montant_du : 0,
          montant : 0,
          numero_recu : "",
          vendeurs : ""
        },
        vendeurs : [],
        
        filterVendeurs : "",
        start : 0,
        end : 15,
        currentPage : 1,
        errors : []
      }
    },
    methods : {
      paginateFunction : async function (url,pagVar) {
        try {
          
          let response = await axios.get(url)
          if(response && response.data) {
            
            pagVar.list = response.data.all

            pagVar.nextUrl = response.data.next_url
            pagVar.lastUrl = response.data.last_url
            pagVar.currentPage = response.data.current_page
            pagVar.firstPage = response.data.first_page
            pagVar.firstItem = response.data.first_item,
            pagVar.total = response.data.total
          }
        }
        catch(error) {
          alert("Erreur!")
          console.log(error)
        }
      },
      getData : async function () {
        this.isLoading = true
        
        let response = await axios.get('/user/all-vendeurs')
        this.vendeurs = response.data

        response = await axios.get('/user/recouvrement/all-recouvrement')

        if(response) {
          this.recouvrement.list = response.data.all

          this.recouvrement.nextUrl = response.data.next_url
          this.recouvrement.lastUrl = response.data.last_url
          this.recouvrement.currentPage = response.data.current_page
          this.recouvrement.firstPage = response.data.first_page
          this.recouvrement.firstItem = response.data.first_item,
          this.recouvrement.total = response.data.total
          this.recouvrement.perPage = response.data.per_page
        }        

        response = await axios.get('/user/recouvrement/all-transactions')
        this.transaction.list = response.data.all

        this.transaction.nextUrl = response.data.next_url
        this.transaction.lastUrl = response.data.last_url
        this.transaction.currentPage = response.data.current_page
        this.transaction.firstPage = response.data.first_page
        this.transaction.firstItem = response.data.first_item,
        this.transaction.total = response.data.total
        this.transaction.perPage = response.data.per_page
        
        this.isLoading = false
      },
      checkVendeur : async function () {
        try {
          if(this.recouvrementData.vendeurs == '') {
            throw "Selectionnez un vendeur!"
          }
          let response = await axios.get('/user/recouvrement/get-montant-du/'+this.recouvrementData.vendeurs)
          this.recouvrementData.montant_du = response.data
        }
         catch (e) {
          UIkit.notification({
            status : "danger",
            message : e
          })
          this.recouvrementData.montant_du = 0
        }
      },
      sendRecouvrement : async function () {
        this.isLoading = true
        try {
          let response = await axios.post('/user/recouvrement/add',this.recouvrementData)
          Object.assign(this.$data,this.$options.data())
          if(response.data == 'done') {
            this.isLoading = false
            alert("Success !")
            this.getData()
          }
        } catch (error) {
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
      }
    },
    computed : {
      typeUser() {
        return this.$store.state.typeUser
      },
      myToken() {
        return this.$store.state.myToken
      },
      vStandart() {
        return this.vendeurs.filter( (v) =>{
          return v.type === 'v_standart'
        })
      },
      TransactionFiltree() {
        return this.transaction.list.filter( (t) => {
          return t.expediteur.match(this.filterVendeurs)
        })
      }
    }
  }
</script>
