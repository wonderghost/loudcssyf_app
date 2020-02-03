<template>
  <div class="">

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Livraison</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit</a></li>
    </ul>
    <ul class="uk-switcher uk-margin">
			<li>
        <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-right">
          <li> <a href="#" @click="filterCommande('en attente')">En attente de confirmation</a> </li>
          <li> <a href="#" @click="filterCommande('confirmer')">Deja confirmee</a> </li>
          <li> <a href="#" @click="filterCommande('')">Toutes les commandes</a> </li>
        </ul>

          <div class="">

            <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
              <thead>
                <tr>
                  <th v-for="head in materialCommand">{{head}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="command in commandMaterial">
                  <td v-for="(column , name) in command" v-if="name != 'link' && name != 'id' && name!='status'">{{column}}</td>
                  <td class="uk-text-danger" v-if="command.status == 'en attente'">{{command.status}}</td>
                  <td class="uk-text-success" v-else >{{command.status}}</td>
                  <td> <a :href="command.link" v-if="typeUser == 'logistique'" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-text-capitalize">confirmer</a> </td>
                </tr>
              </tbody>
            </table>
          </div>
      </li>
      <li>
        <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-right">
          <li> <a href="#" @click="filterLivraison('unlivred')">en attente de validation</a> </li>
          <li> <a href="#" @click="filterLivraison('livred')">deja validee</a> </li>
        </ul>
        <div class="">
          <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
            <thead>
              <tr>
                <th v-for="head in livraison">{{head}}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="livraison in livraisonMaterial">
                <td>{{livraison.date}}</td>
                <td>{{livraison.vendeur}}</td>
                <td>{{livraison.produit}}</td>
                <td>{{livraison.command}}</td>
                <td>{{livraison.quantite}}</td>
                <td v-if="livraison.status == 'livred'" class="uk-text-success">{{livraison.status}}</td>
                <td v-else class="uk-text-danger">{{livraison.status}}</td>
                <td>
                   <a href="#" v-if="typeUser == 'logistique'" class="uk-button uk-button-small uk-button-primary uk-text-capitalize uk-border-rounded uk-box-shadow-small">validez <span uk-icon="icon : check"></span> </a>
                   <a download :href="livraison.filename" v-if="livraison.status == 'livred'" class="uk-button uk-button-small uk-button-default uk-text-capitalize uk-border-rounded uk-box-shadow-small">details <span uk-icon="icon : more"></span> </a>
                 </td>
              </tr>
            </tbody>
          </table>
        </div>
      </li>
      <li></li>
    </ul>
  </div>
</template>
<script>
    export default {
        mounted() {
          this.getMaterialCommande()
          this.getLivraisonMaterial()
        },
        data () {
          return {
            materialCommand : ['date','vendeurs','designation','quantite','parabole a livrer','status'],
            livraison : ['date','vendeurs','designation','commande','quantite','status']
          }
        },
        methods : {
          getMaterialCommande : async function () {
            try {
              let response = await axios.get('/admin/commandes/all')
              if(response.data.length) {
                this.$store.commit('setCommandMaterial',response.data)
              }
            } catch (e) {
              alert(e)
            }
          },
          getLivraisonMaterial : async function () {
            try {
              let response = await axios.get('/admin/commandes/livraison')
              this.$store.commit('setLivraisonMaterial',response.data.original)
            } catch (e) {
              alert(e)
            }
          }
          ,
          filterCommande : function (type) {
            this.$store.commit('setTypeCommand',type)
          },
          filterLivraison : function (status) {
            this.$store.commit('setStateLivraison',status)
          }
        },
        computed : {
          commandMaterial () {
            return this.cMaterial.filter( (command) => {
              return command.status.match(this.typeCommand)
            })
          },
          typeUser () {
            return this.$store.state.typeUser
          },
          cMaterial () {
            return this.$store.state.commandMaterial
          },
          typeCommand() {
            return this.$store.state.typeCommand
          },
          livraisonMaterial () {
            // return this.$store.state.livraisonMaterial
            return  this.livraisonM.filter( (liv) => {
              return liv.status === this.statusLivraison
            })
          },
          livraisonM() {
            return this.$store.state.livraisonMaterial
          },
          statusLivraison () {
            return this.$store.state.statusLivraison
          }
        }
    }
</script>
