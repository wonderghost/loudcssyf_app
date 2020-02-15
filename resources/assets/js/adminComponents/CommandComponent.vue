<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a @click="start=0" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
        <li><a @click="end=10" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Livraison</a></li>
        <li><a v-if="typeUser == 'admin' || typeUser == 'gcga'" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit</a></li>
    </ul>
    <ul class="uk-switcher uk-margin">
			<li>
        <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-right">
          <li> <a href="#" @click="filterCommande('en attente')">En attente de confirmation</a> </li>
          <li> <a href="#" @click="filterCommande('confirmer')">Deja confirmee</a> </li>
          <li> <a href="#" @click="filterCommande('')">Toutes les commandes</a> </li>
        </ul>

          <div class="">

            <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
              <thead>
                <tr>
                  <th v-for="head in materialCommand">{{head}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="command in commandMaterial.slice(start,end)">
                  <td v-for="(column , name) in command" v-if="name != 'link' && name != 'id' && name!='status'">{{column}}</td>
                  <td class="uk-text-danger" v-if="command.status == 'en attente'">{{command.status}}</td>
                  <td class="uk-text-success" v-else >{{command.status}}</td>
                  <td> <a :href="command.link" v-if="typeUser == 'logistique' && command.status == 'en attente'" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-text-capitalize">confirmer</a> </td>
                </tr>
              </tbody>
            </table>
              <ul class="uk-pagination uk-flex uk-flex-center">
                <li> <span> Page : {{currentPage}} </span> </li>
                <li> <button type="button" @click="previousPage()" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default" name="button"> <span uk-pagination-previous></span> Previous</button> </li>
                <li> <button type="button" @click="nextPage()" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-default" name="button"> Suivant <span uk-pagination-next></span> </button> </li>
              </ul>
          </div>
      </li>
      <li>
        <ul class="uk-tab" uk-switcher="animation : uk-animation-slide-right">
          <li> <a href="#" @click="filterLivraison('unlivred')">en attente de validation</a> </li>
          <li> <a href="#" @click="filterLivraison('livred')">deja validee</a> </li>
        </ul>
        <div class="">
          <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
            <thead>
              <tr>
                <th v-for="head in livraison">{{head}}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="livraison in livraisonMaterial.slice(start,end)">
                <td>{{livraison.date}}</td>
                <td>{{livraison.vendeur}}</td>
                <td>{{livraison.produit}}</td>
                <td>{{livraison.command}}</td>
                <td>{{livraison.quantite}}</td>
                <td v-if="livraison.status == 'livred'" class="uk-text-success">{{livraison.status}}</td>
                <td v-else class="uk-text-danger">{{livraison.status}}</td>
                <td>{{livraison.depot}}</td>
                <td>
                   <a href="#" v-if="typeUser == 'logistique' && livraison.status == 'unlivred'" class="uk-button uk-button-small uk-button-primary uk-text-capitalize uk-border-rounded uk-box-shadow-small">validez <span uk-icon="icon : check"></span> </a>
                   <a download :href="livraison.filename" v-if="livraison.status == 'livred'" class="uk-button uk-button-small uk-button-default uk-text-capitalize uk-border-rounded uk-box-shadow-small">details <span uk-icon="icon : more"></span> </a>
                 </td>
              </tr>
            </tbody>
          </table>
          <ul class="uk-pagination uk-flex uk-flex-center">
            <li> <button @click="previousPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> <span uk-pagination-previous></span> Precedent</button> </li>
            <li> <button @click="nextPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> Suivant <span uk-pagination-next></span> </button> </li>
          </ul>
        </div>
      </li>
      <template id="" v-if="typeUser == 'admin' || typeUser == 'gcga'">
        <li>
          <credit-component the-user="admin"></credit-component>
        </li>
    </template>
    </ul>
  </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
      created () {
        this.isLoading = true
      },
        mounted() {
          this.getMaterialCommande()
          this.getLivraisonMaterial()
        },
        components : {
          Loading
        },
        data () {
          return {
            materialCommand : ['date','vendeurs','designation','quantite','parabole a livrer','status'],
            livraison : ['date','vendeurs','designation','commande','quantite','status','depot'],
            start : 0,
            end : 10,
            currentPage : 1,
            isLoading : false,
            fullPage : true
          }
        },
        methods : {
          getMaterialCommande : async function () {
            try {
              if(this.typeUser == 'admin') {
                var response = await axios.get('/admin/commandes/all')
              } else if(this.typeUser == 'logistique') {
                var response = await axios.get("/logistique/commandes/all")
              }
              if(response.data.length) {
                this.$store.commit('setCommandMaterial',response.data)
                this.isLoading = false
              }
            } catch (e) {
              alert(e)
            }
          },
          getLivraisonMaterial : async function () {
            try {
              if(this.typeUser == 'admin') {

                var response = await axios.get('/admin/commandes/livraison')
              } else if(this.typeUser == 'logistique') {
                var response = await axios.get('/logistique/commandes/livraison')
              }
              this.$store.commit('setLivraisonMaterial',response.data.original)
            } catch (e) {
              alert(e)
            }
          }
          ,
          filterCommande : function (type) {
            this.currentPage = 1
            this.start = 0
            this.end = 10
            this.$store.commit('setTypeCommand',type)
          },
          filterLivraison : function (status) {
            this.currentPage = 1
            this.start = 0
            this.end = 10
            this.$store.commit('setStateLivraison',status)
          },
          nextPage : function () {
            if(this.commandMaterial.length > this.end) {
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
