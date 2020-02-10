<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>

    <template id="">
    <div class="uk-grid-small" uk-grid>

      <div class="uk-width-1-6@m">
        <input type="text" name="" :value="commission | numFormat" class="uk-input uk-border-rounded uk-text-center uk-text-lead" disabled>
        <button v-if="typeUser == 'v_da' || typeUser == 'v_standart'" type="button" name="button" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-primary uk-margin-small">Paiement commission</button>
      </div>
    </div>
  </template>


    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a @click="typeRapp = 'recrutement'"  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Recrutement</a></li>
        <li><a @click="typeRapp = 'reabonnement'"  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Reabonnement</a></li>
        <li><a @click="typeRapp = 'migration'" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Migration</a></li>
    </ul>

    <div class="">
      <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
        <thead>
          <tr>
						<th>Date</th>
						<th>Vendeur</th>
						<th>Type</th>
						<th>Credit</th>
						<th>Quantite</th>
						<th>Montant Ttc</th>
						<th>Commission</th>
						<th>Promo</th>
						<th>Paiement Commission</th>
					</tr>
        </thead>
        <tbody>
          <tr v-for="rap in rapportVentes.slice(start,end)">
            <td>{{rap.date}}</td>
            <td>{{rap.vendeurs}}</td>
            <td>{{rap.type}}</td>
            <td>{{rap.credit}}</td>
            <td>{{rap.quantite}}</td>
            <td>{{rap.montant_ttc}}</td>
            <td>{{rap.commission}}</td>
            <td>{{rap.promo}}</td>
            <td>{{rap.paiement_commission}}</td>
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
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
      created () {
        this.isLoading = true
      },
        mounted() {
          this.getRapportVente()
        },
        components : {
          Loading
        },
        data () {
          return {
            isLoading : false,
            fullPage : true,
            typeRapp : 'recrutement',
            start : 0,
            end : 10,
            currentPage : 1,
            commission : 0
          }
        },
        methods : {
          getRapportVente : async function () {
            try {
              if(this.typeUser == 'admin') {
                var response = await axios.get('/admin/rapport/all')
                var responseCom = await axios.get('/admin/rapport/commission-total')
              } else {
                var response = await axios.get('/user/rapport-ventes/all')
                var responseCom = await axios.get('/user/rapport/total-commission')
              }
              this.isLoading = false
              this.commission = responseCom.data
              this.$store.commit('setRapportVente',response.data)
            } catch (error) {
              alert(error)
            }
          },
          nextPage : function () {
            if(this.rapportVentes.length > this.end) {
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
          rapportVentes () {
            return this.$store.state.rapportVentes.filter( (rapport) => {
              return rapport.type === this.typeRapp
            })
          },
          typeUser () {
            return this.$store.state.typeUser
          }
        }
    }
</script>
