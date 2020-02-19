<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>

    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a @click="start=0 , end=10 , currentPage = 1" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Livraison</a></li>
        <li><a v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'v_da' || typeUser == 'v_standart'" class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit</a></li>
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
                <tr v-for="command in commandList.slice(start,end)">
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
        <template id="">
          <livraison :the-user="theUser"></livraison>
        </template>
      </li>
      <template id="" v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'v_da' || typeUser == 'v_standart'">
        <li>
          <credit-component :the-user="theUser"></credit-component>
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
        },
        components : {
          Loading
        },
        props : {
          theUser : String
        },
        data () {
          return {
            materialCommand : ['date','vendeurs','designation','quantite','parabole a livrer','status'],
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
              } else if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                var response = await axios.get('/user/commandes/all')
              }
              if(response.data.length) {
                this.$store.commit('setCommandMaterial',response.data)
                this.isLoading = false
              }
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
          nextPage : function () {
            if(this.commandList.length > this.end) {
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
          commandList () {
            if(!this.theUser) {
              return this.commandMaterial
            }
            else {
              return this.commandMaterial.filter( (command) => {
                return command.vendeurs.match(this.theUser)
              })
            }
          }
          ,
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
          }
        }
    }
</script>
