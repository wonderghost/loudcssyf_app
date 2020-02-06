<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="true"
        :is-full-page="fullPage"></loading>

    <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-right">
      <li> <a @click="filterCommandCredit('unvalidated')" href="#">En attente de validation</a> </li>
      <li> <a @click="filterCommandCredit('validated')" href="#">Deja validee</a> </li>
      <li> <a @click="filterCommandCredit('aborted')" href="#">commandes annullee</a> </li>
    </ul>
    <div class="">
      <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
        <thead>
          <tr>
            <th v-for="head in tableHead">{{head}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="credit in commandCredit.slice(start,end)">
            <td>{{credit.date}}</td>
            <td>{{credit.vendeurs}}</td>
            <td>{{credit.type}}</td>
            <td>{{credit.montant}}</td>
            <td>{{credit.status}}</td>
            <td>{{credit.numero_recu}}</td>
            <td v-if="credit.recu !== 'undefined'">
              <div uk-lightbox>
                <a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default uk-text-capitalize" href="" :data-caption="credit.numero_recu">voir</a>
              </div>
            </td>
            <td v-else>{{credit.recu}}</td>
          </tr>
        </tbody>
      </table>
      <ul class="uk-pagination uk-flex uk-flex-center" uk-margin>
        <li> <span> Page active : {{currentPage}} </span> </li>
        <li> <button @click="previousPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> <span uk-pagination-previous></span> Precedent </button> </li>
        <li> <button @click="nextPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> Suivant <span uk-pagination-next></span>  </button> </li>
      </ul>
    </div>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        mounted() {
          this.isLoading = true
          this.getCommandCredit()
        },
        props : {
          theUser : String
        },
        data () {
          return {
            tableHead : ['date','vendeurs','type','montant','status','numero recu','recu'],
            currentPage: 1,
            start : 0,
            end  : 10,
            isLoading : false,
            fullPage : true
          }
        },
        components : {
          Loading
        }
        ,
        methods : {
          getCommandCredit : async function () {
            try {
              if(this.theUser == 'admin') {
                var response = await axios.get('/admin/commandes/credit-all')
              } else {
                var response = await axios.get('/user/commandes/credit-all')
              }
              this.$store.commit('setCommandCredit',response.data)
              this.isLoading=false
            }
            catch (e) {
              alert(e)
            }
          },
          filterCommandCredit (status) {
            this.currentPage = 1
            this.start = 0
            this.end = 10
            this.$store.commit('setStatusCommandCredit',status)
          },
          nextPage : function () {
            if(this.commandCredit.length > this.end) {
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
          commandCredit () {
            return this.commandC.filter( (credit)  => {
                return credit.status === this.statusCommandCredit
            })
          },
          commandC () {
            return this.$store.state.commandCredit
          },
          statusCommandCredit () {
            return this.$store.state.statusCommandCredit
          }
        }
    }
</script>
