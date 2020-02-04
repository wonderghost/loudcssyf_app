<template>
  <div class="">
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
          <tr v-for="credit in commandCredit">
            <td>{{credit.date}}</td>
            <td>{{credit.vendeurs}}</td>
            <td>{{credit.type}}</td>
            <td>{{credit.montant}}</td>
            <td>{{credit.status}}</td>
            <td>{{credit.numero_recu}}</td>
            <td v-if="credit.recu !== 'undefined'">
              <div uk-lightbox>
                <a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default uk-text-capitalize" href="" data-caption="Caption">voir</a>
              </div>
            </td>
            <td v-else>{{credit.recu}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
    export default {
        mounted() {
          this.getCommandCredit()
        },
        data () {
          return {
            tableHead : ['date','vendeurs','type','montant','status','numero recu','recu']
          }
        },
        methods : {
          getCommandCredit : async function () {
            try {
              let response = await axios.get('/admin/commandes/credit-all')
              this.$store.commit('setCommandCredit',response.data)
            }
            catch (e) {
              alert(e)
            }
          },
          filterCommandCredit (status) {
            this.$store.commit('setStatusCommandCredit',status)
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
