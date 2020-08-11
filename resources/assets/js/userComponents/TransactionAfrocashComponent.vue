<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <h3>Toutes les transactions</h3>
        <hr class="uk-divider-small">

    <div class="uk-grid" uk-grid>
      <form class="uk-width-1-1@m" uk-grid>
        <div class="uk-width-1-4@m">
          <label for=""> <span uk-icon="icon : calendar"></span> Du</label>
          <input type="date" class="uk-input uk-border-rounded" v-model="filterData.du">
        </div>
        <div class="uk-width-1-4@m">
          <label for=""> <span uk-icon="icon : calendar"></span> Au</label>
          <input type="date" class="uk-input uk-border-rounded" v-model="filterData.au">
        </div>
        <div v-if="!theUser" class="uk-width-1-4@m">
          <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
          <select class="uk-select uk-border-rounded" v-model="filterData.vendeurs">
            <option value="">-- Selectionnez le vendeur --</option>
            <option :value="u.localisation" v-for="u in users">{{u.localisation}}</option>
          </select>
        </div>
        <div v-else="" class="uk-width-1-4@m">
          <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
          <span class="uk-input uk-border-rounded">{{theUser}}</span>
        </div>
      </form>
    </div>
    <table class="uk-table uk-table-small uk-table-divider uk-table-responsive uk-table-striped uk-table-hover">
      <thead>
        <tr>
          <th>Date</th>
          <th>Expediteur</th>
          <th>Destinataire</th>
          <th>Montant</th>
          <th>Motif</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in transactionsList.slice(start,end)">
          <td>{{ t.date }}</td>
          <td>{{ t.expediteur }}</td>
          <td>{{ t.destinataire }}</td>
          <td>{{ t.montant | numFormat }}</td>
          <td>{{ t.motif }}</td>
        </tr>
      </tbody>
    </table>
    <ul class="uk-pagination uk-flex uk-flex-center">
      <li> <span> Page : {{currentPage}} </span> </li>
      <li> <button type="button" @click="previousPage()" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default" name="button"> <span uk-pagination-previous></span> Previous</button> </li>
      <li> <button type="button" @click="nextPage()" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default" name="button"> Suivant <span uk-pagination-next></span> </button> </li>
    </ul>
  </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

  export default {
    components : {
      Loading
    },
    created() {
      this.isLoading = true
    },
    mounted() {
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
        filterData : {
          _token : "",
          vendeurs : "",
          du : "",
          au : ""
        }
      }
    },
    methods : {
      allTransactionList : async function () {
        try {
          let response = await axios.get('/user/afrocash/get-transactions')
          this.transactions = response.data
          response = await axios.get('/admin/all-vendeurs')
          this.users = response.data
          this.isLoading = false
        }
        catch (e) {
          alert(e)
        }
      },
      nextPage : function () {
        if(this.transactionsList.length > this.end) {
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
      typeUser () {
        return this.$store.state.typeUser
      },
      transactionsList() {
        return this.transactions.filter((t) =>  {
          if(!this.theUser) {
            return (t.expediteur.match(this.filterData.vendeurs) || t.destinataire.match(this.filterData.vendeurs))
          }
          else {
            return (t.expediteur.match(this.theUser) || t.destinataire.match(this.theUser))
          }
        })
      }
    }
  }
</script>
