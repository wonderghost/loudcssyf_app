<template>
  <div class="">

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Livraison</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit</a></li>
    </ul>
    <ul class="uk-switcher uk-margin">
			<li>
        <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-right">
          <li> <a href="#">Toutes les commandes</a> </li>
          <li> <a href="#">En attente de confirmation</a> </li>
          <li> <a href="#">Deja confirmee</a> </li>
        </ul>
        <ul class="uk-margin uk-switcher">
          <li>
            <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
              <thead>
                <tr>
                  <th v-for="head in materialCommand">{{head}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="command in commandMaterialUnconfirmed">
                  <td v-for="(column , name) in command" v-if="name != 'link' && name != 'id' && name!='status'">{{column}}</td>
                  <td class="uk-text-danger" v-if="command.status == 'en attente'">{{command.status}}</td>
                  <td class="uk-text-success" v-else >{{command.status}}</td>
                  <td> <a :href="command.link" v-if="typeUser == 'logistique'" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-text-capitalize">confirmer</a> </td>
                </tr>
              </tbody>
            </table>
          </li>
          <li>
            <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
              <thead>
                <tr>
                  <th v-for="head in materialCommand">{{head}}</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </li>
        </ul>
      </li>
      <li></li>
      <li></li>
    </ul>
  </div>
</template>
<script>
    export default {
        mounted() {
          this.getMaterialCommande()
        },
        data () {
          return {
            materialCommand : ['date','vendeurs','designation','quantite','parabole a livrer','status'],
            livraison : ['date','vendeurs','designation','commande','quantite','status'],
            confirmed : 'uk-text-success',
            unconfirmed : 'uk-text-danger'
          }
        },
        methods : {
          getMaterialCommande : async function () {
            try {
              let response = await axios.get('/admin/commandes/all')
              if(response.data.length) {
                this.$store.state.commandMaterial.unconfirmed = response.data
              }
            } catch (e) {
              console.log(e)
            }
          }
        },
        computed : {
          commandMaterialUnconfirmed () {
            return this.$store.state.commandMaterial.unconfirmed
          },
          commandMaterialConfirmed () {
            return this.$store.state.commandMaterial.confirmed
          },
          typeUser () {
            return this.$store.state.typeUser
          }
        }
    }
</script>
