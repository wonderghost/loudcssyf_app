<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="true"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <h3>Tous les materiels</h3>
        <hr class="uk-divider-small">
        
      <div class="uk-child-width-1-4@m uk-grid-small" uk-grid>
        <template>
          <!-- INVENTAIRE DES MATERIELS -->
          <div v-for="m in lesMaterials"  class="" :key="m.localisation">
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
      <template id="">
        <div class="uk-grid-small" uk-grid>
          <div class="uk-width-1-4@m">

          </div>
          <div class="uk-width-1-4@m">

          </div>
          <div class="uk-width-1-4@m">
            <label for="">Etat</label>
            <select v-model="filterEtat" class="uk-select uk-border-rounded">
              <option value="-">Non Defectueux</option>
              <option value="defectueux">Defectueux</option>
            </select>
          </div>
          <div v-if="typeUser == 'admin' || typeUser == 'logistique' || typeUser == 'commercial'" class="uk-width-1-4@m">
            <label for="">Depots</label>
            <select v-model="filterState" class="uk-select uk-border-rounded">
              <option value="">Tous</option>
              <option :value="d.localisation" v-for="d in materials" :key="d.localisation"> {{d.localisation}} </option>
            </select>
          </div>
        </div>
      </template>
      <div class="">
        <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
          <thead>
            <tr>
              <th v-for="head in tableHead">{{head}}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in filterListEtat.slice(start,end)">
              <td>{{s.numero_materiel}} <sup v-if="s.etat !== '-'" class="uk-badge">{{s.etat}}</sup>   </td>
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
        UIkit.offcanvas($("#side-nav")).hide();
        this.getMaterialsDepot()
        this.getSerialNumberForDepot()
        if(this.typeUser == 'gdepot') {
          this.filterState = this.userLocalisation
        }
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
          filterEtat : "-",
          start : 0,
          end : 15,
          currentPage : 1,
        }
      },
      methods : {
        getMaterialsDepot : async function () {
          try {
            this.isLoading = true
            if(this.typeUser == 'admin') {
              var response = await axios.get('/admin/inventory/depot')
            }
            else {
              var response = await axios.get('/user/inventory/depot')
            }
            this.materials = response.data
            this.$store.commit('setMaterials',response.data)
            this.isLoading = false
          } catch (e) {
              alert(e)
          }
        },
        getSerialNumberForDepot : async function () {
          try {
            this.isLoading = true
            if(this.typeUser == 'admin') {
              var response = await axios.get('/admin/inventory/depot/serialNumber')
            }
            else {
              var response = await axios.get('/user/inventory/depot/serialNumber')
            }
            this.serials = response.data
            this.isLoading = false
          } catch (e) {
              alert(e)
          }
        },
        nextPage : function () {
          if(this.filterListEtat.length > this.end) {
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
        lesMaterials() {
          return this.materials.filter((m) => {
            if(this.typeUser == 'gdepot') {
              return m.localisation.match(this.userLocalisation)
            }
            else {
              return m
            }
          })
        },
        filterListSerials () {
          return this.serials.filter((s) => {
            return s.depot.match(this.filterState)
          })
        },
        filterListEtat() {
          return this.filterListSerials.filter ( (s) => {
            return s.etat.match(this.filterEtat)   
          })
        },
        typeUser () {
          return this.$store.state.typeUser
        },
        userLocalisation() {
          return this.$store.state.userLocalisation
        }
      }
    }
</script>
