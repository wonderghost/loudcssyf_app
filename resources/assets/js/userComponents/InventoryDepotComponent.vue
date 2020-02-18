<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="true"
        :is-full-page="fullPage"></loading>
      <div class="uk-child-width-1-4@m uk-grid-small" uk-grid>
        <template>
          <!-- INVENTAIRE DES MATERIELS -->
          <div v-for="m in materials"  class="">
            <div class="uk-card uk-border-rounded uk-box-shadow-hover-small uk-background-muted uk-dark uk-card-body uk-padding-small">
              <h3 class="uk-card-title">{{m.localisation}}</h3>
              <p>
                <ul class="uk-list uk-list-divider">
                  <li>
                    <span class="uk-card-title">Terminal : {{m.terminal}}</span> ,
                  </li>
                  <li>
                    <span class="uk-card-title">Parabole : {{m.parabole}}</span>
                  </li>
                </ul>
              </p>
            </div>
          </div>
        </template>
      </div>
      <div class="uk-grid-small" uk-grid>
        <div class="uk-width-3-4@m">

        </div>
        <div class="uk-width-1-4@m">
          <select v-model="filterState" class="uk-select uk-border-rounded">
            <option value="">Tous</option>
            <option :value="d.localisation" v-for="d in materials"> {{d.localisation}} </option>
          </select>
        </div>
      </div>
      <div class="">
        <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
          <thead>
            <tr>
              <th v-for="head in tableHead">{{head}}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in filterListSerials.slice(start,end)">
              <td>{{s.numero_materiel}}</td>
              <td>{{s.depot}}</td>
              <td>{{s.article}}</td>
              <td>{{s.origine}}</td>
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
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
      created() {
        this.isLoading = true
      },
      components : {
        Loading
      },
      mounted() {
        this.getMaterialsDepot()
        this.getSerialNumberForDepot()
        this.isLoading = false
      },
      data () {
        return {
          isLoading : false,
          fullPage : true,
          errors : [],
          materials : [],
          tableHead : ['Numero Materiel','depot','article','origine'],
          serials : [],
          filterState : "",
          start : 0,
          end : 15,
          currentPage : 1,
        }
      },
      methods : {
        getMaterialsDepot : async function () {
          try {
            if(this.typeUser == 'admin') {
              var response = await axios.get('/admin/inventory/depot')
            }
            else {
              var response = await axios.get('/user/inventory/depot')
            }
            this.materials = response.data
          } catch (e) {
              alert(e)
          }
        },
        getSerialNumberForDepot : async function () {
          try {
            if(this.typeUser == 'admin') {
              var response = await axios.get('/admin/inventory/depot/serialNumber')
            }
            else {
              var response = await axios.get('/user/inventory/depot/serialNumber')
            }
            this.serials = response.data
          } catch (e) {
              alert(e)
          }
        },
        nextPage : function () {
          if(this.filterListSerials.length > this.end) {
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
      },
      computed : {
        filterListSerials () {
          return this.serials.filter((s) => {
            return s.depot.match(this.filterState)
          })
        },
        typeUser () {
          return this.$store.state.typeUser
        }
      }
    }
</script>
