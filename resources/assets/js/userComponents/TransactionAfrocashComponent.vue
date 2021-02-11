<template>
  <div class="uk-container uk-container-large uk-margin-large-bottom">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

    <h3>Toutes les transactions</h3>
    <hr class="uk-divider-small">

    <div class="uk-grid-small" uk-grid>
      <div class="uk-grid-small uk-width-1-1@m" uk-grid>
        <div class="uk-width-1-4@m">
          <label for=""> <span uk-icon="icon : calendar"></span> Du</label>
          <input type="date" class="uk-input uk-border-rounded" v-model="filterData.du">
        </div>
        <div class="uk-width-1-4@m">
          <label for=""> <span uk-icon="icon : calendar"></span> Au</label>
          <input type="date" class="uk-input uk-border-rounded" v-model="filterData.au">
        </div>
        <div v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'commercial'" class="uk-width-1-4@m">
          <label for=""><span uk-icon="icon : search"></span> Recherche Rapide</label>
          <input v-model="searchText" type="text" class="uk-input uk-border-rounded" placeholder="Tapez une recherche ...">
        </div>
        <div v-else class="uk-width-1-4@m">
          <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
          <span class="uk-input uk-border-rounded">{{userLocalisation}}</span>
        </div>
        <!-- paginate -->
        <div class="uk-width-1-4@m">
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
    </div>
    <table class="uk-table uk-table-small uk-table-divider uk-table-responsive uk-table-striped uk-table-hover">
      <thead>
        <tr>
          <th>Date</th>
          <th>Expediteur</th>
          <th>Destinataire</th>
          <th>Montant</th>
          <th>Solde Anterieur</th>
          <th>Nouveau Solde</th>
          <th>Motif</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(t,index) in transactions" :key="index">
          <td>{{ t.date }}</td>
          <td v-if="t.expediteur.type == 'technicien' || t.expediteur.type == 'client'">{{ t.expediteur.nom }} {{ t.expediteur.prenom}}</td>
          <td v-else>{{ t.expediteur.localisation }}</td>
          <td v-if="t.destinataire.type == 'technicien' || t.destinataire.type == 'client'">{{ t.destinataire.nom }} {{ t.destinataire.prenom}}</td>
          <td v-else>{{ t.destinataire.localisation }}</td>
          <td>{{ t.montant | numFormat }}</td>
          <td v-if="t.solde_anterieur != null">{{ t.solde_anterieur | numFormat }}</td><td v-else>-</td>
          <td v-if="t.nouveau_solde != null">{{ t.nouveau_solde | numFormat }}</td><td v-else>-</td>
          <td>{{ t.motif }}</td>
        </tr>
      </tbody>
    </table>
    <div class="uk-flex uk-flex-center">
      <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
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
      this.allTransactionList()
    },
    props : {
      theUser : String
    },
    data() {
      return {
        isLoading : false,
        fullPage : true,
        transactions : [],
        start : 0,
        end : 10,
        currentPage : 1,
        users : [],
// paginate
        nextUrl : "",
        lastUrl : "",
        perPage : "",
        currentPage : 1,
        firstPage : "",
        firstItem : 1,
        total : 0,
// #####                  
        filterData : {
          _token : "",
          vendeurs : "",
          du : "",
          au : "",
        },
        searchText : ""
      }
    },
    methods : {
      paginateFunction : async function (url) {
        try {
          // this.isLoading = true
          let response = await axios.get(url)
          if(response && response.data) {
            this.isLoading = false
            this.transactions = response.data.all
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
      allTransactionList : async function () {
        try {
          this.isLoading = true
          let response = await axios.get('/user/afrocash/get-transactions')

          this.transactions = response.data.all

          this.nextUrl = response.data.next_url
          this.lastUrl = response.data.last_url
          this.currentPage = response.data.current_page
          this.firstPage = response.data.first_page
          this.firstItem = response.data.first_item,
          this.total = response.data.total
          this.perPage = response.data.per_page

          response = await axios.get('/admin/all-vendeurs')
          this.users = response.data
          this.isLoading = false
        }
        catch (e) {
          alert(e)
        }
      }
    },
    computed : {
      userLocalisation() {
        return this.$store.state.userLocalisation
      },
      typeUser () {
        return this.$store.state.typeUser
      },
      // transactionsList() {
      //   return this.transactions.filter((t) =>  {
      //     if(this.typeUser == 'admin' || this.typeUser == 'gcga' || this.typeUser == 'commercial') {
      //       return (t.expediteur.toUpperCase().match(this.searchText.toUpperCase()) || t.destinataire.toUpperCase().match(this.searchText.toUpperCase()))
      //     }
      //     else {
      //       return (t.expediteur.match(this.userLocalisation) || t.destinataire.match(this.userLocalisation))
      //     }
      //   })
      // }
    }
  }
</script>
