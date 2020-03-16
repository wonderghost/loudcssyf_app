<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>

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
        <template v-if="theUser == 'coursier'">
          <form @submit.prevent="sendRecouvrement()" class="uk-width-1-2@m">
          <h3>Enregistrer un recouvrement</h3>
          <div class="uk-margin-small">
            <label for="">Vendeurs</label>
            <select class="uk-select uk-border-rounded uk-margin-small" @change="checkVendeur()" v-model="recouvrementData.vendeurs">
              <option value="">--Selectionnez un vendeur --</option>
              <option :value="v.username" v-for="v in vStandart">{{ v.localisation }}</option>
            </select>
          </div>
          <div class="uk-margin-small">
            <label for="">Montant du</label>
            <span class="uk-input uk-border-rounded">{{recouvrementData.montant_du | numFormat}}</span>
          </div>
          <div class="uk-margin-small">
            <label for="">Montant</label>
            <input type="number" class="uk-input uk-border-rounded" v-model="recouvrementData.montant">
          </div>
          <div class="uk-margin-small">
            <label for="">Numero Recu</label>
            <input type="text" class="uk-input uk-border-rounded" v-model="recouvrementData.numero_recu">
          </div>
          <button type="submit" class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
        </form>
        </template>
        <template v-else>
          <div class="uk-alert-warning uk-border-rounded uk-box-shadow-small" uk-alert>
            <p class="uk-text-center"> <span uk-icon="icon : warning"></span>   Vous n'etes pas autorise a effectuer cette action !</p>
          </div>
        </template>
      </li>
      <li>
        <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-striped uk-table-responsive">
          <thead>
            <tr>
              <th>Date</th>
              <th>Vendeurs</th>
              <th>Montant</th>
              <th>Numero Recu</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in recouvrements">
              <td>{{r.date}}</td>
              <td>{{r.vendeurs}}</td>
              <td>{{r.montant}}</td>
              <td>{{r.numero_recu}}</td>
            </tr>
          </tbody>
        </table>
      </li>
      <li>
        <!-- TRANSACTIONS -->
        <div class="uk-grid" uk-grid>
          <div class="uk-width-1-3@m">
            <label for=""> <span uk-icon="icon : calendar"></span> Du</label>
            <input type="date" class="uk-input uk-border-rounded" value="">
          </div>
          <div class="uk-width-1-3@m">
            <label for=""><span uk-icon="icon : calendar"></span> Au</label>
            <input type="date" class="uk-input uk-border-rounded" value="">
          </div>
          <div class="uk-width-1-3@m">
            <label for=""> <span uk-icon="icon : users"></span> Vendeurs</label>
            <select class="uk-select uk-border-rounded" v-model="filterVendeurs">
              <option value="">Tous les Vendeurs</option>
              <option :value="v.localisation" v-for="v in vStandart.slice(start,end)">{{v.localisation}}</option>
            </select>
          </div>
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
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in TransactionFiltree.slice(start,end)">
              <td>{{t.date}}</td>
              <td>{{t.expediteur}}</td>
              <td>{{t.destinataire}}</td>
              <td>{{t.montant}}</td>
              <td>{{t.type}}</td>
              <td>{{t.status}}</td>
            </tr>
          </tbody>
        </table>
        <ul class="uk-pagination uk-flex uk-flex-center" uk-margin>
          <li> <span> Page active : {{currentPage}} </span> </li>
          <li> <button @click="previousPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> <span uk-pagination-previous></span> Precedent </button> </li>
          <li> <button @click="nextPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> Suivant <span uk-pagination-next></span>  </button> </li>
        </ul>
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
    created() {
      this.isLoading = true
    },
    mounted() {
      this.recouvrementData._token = this.myToken
      this.getData()
    },
    data() {
      return {
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
        recouvrements : [],
        transactions : [],
        filterVendeurs : "",
        start : 0,
        end : 15,
        currentPage : 1,
        errors : []
      }
    },
    methods : {
      getData : async function () {
        if(this.theUser == 'coursier') {
          let response = await axios.get('/user/all-vendeurs')
          this.vendeurs = response.data

          response = await axios.get('/user/recouvrement/all-recouvrement')
          this.recouvrements = response.data

          response = await axios.get('/user/recouvrement/all-transactions')
          this.transactions = response.data
        } 
        else {
          let response = await axios.get('/admin/all-vendeurs')
          this.vendeurs = response.data

          response = await axios.get('/admin/recouvrement/all-recouvrement')
          this.recouvrements = response.data

          response = await axios.get('/admin/recouvrement/all-transactions')
          this.transactions = response.data
        }
        this.isLoading = false
      },
      nextPage : function () {
        if(this.TransactionFiltree.length > this.end) {
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
          if(response.data == 'done') {
            UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Recouvrement effectuee!</div>")
              .then(function () {
                location.reload()
              })
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
      myToken() {
        return this.$store.state.myToken
      },
      vStandart() {
        return this.vendeurs.filter( (v) =>{
          return v.type === 'v_standart'
        })
      },
      TransactionFiltree() {
        return this.transactions.filter( (t) => {
          return t.expediteur.match(this.filterVendeurs)
        })
      }
    }
  }
</script>
