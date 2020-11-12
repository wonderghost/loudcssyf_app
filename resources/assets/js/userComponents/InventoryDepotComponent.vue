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

      <download-to-excel :data-to-export="serials" :data-fields="fieldsExport" file-name="inventaire-depot"></download-to-excel>
        
      <div class="uk-child-width-1-4@m uk-grid-small" uk-grid>
        <template>
          <!-- INVENTAIRE DES MATERIELS -->
          <div v-for="m in lesMaterials"  class="" :key="m.localisation">
            <div class="uk-card uk-border-rounded uk-box-shadow-hover-small uk-background-muted uk-dark uk-card-body uk-padding-small">
              <h3 class="uk-card-title">{{m.localisation}}</h3>
              <p>
                <ul class="uk-list uk-list-divider">
                  <li v-for="p in m.produits" :key="p.infos.reference">
                    <span class="uk-card-title">{{ p.infos.libelle }} : {{p.quantite}}</span> ,
                  </li>
                </ul>
              </p>
            </div>
          </div>
        </template>
      </div>

      
      <div class="uk-grid-small" uk-grid>
        <!-- <div class="uk-width-1-4@m"></div>
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
        </div> -->
        <!-- paginate component -->
        <div class="uk-width-1-4@m uk-margin-top">
          <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
          <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
          <button @click="getSerialNumberForDepot()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
          <template v-if="lastUrl">
            <button @click="paginateFunction(lastUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Precedent">
              <span uk-icon="chevron-left"></span>
            </button>
          </template>
          <template v-if="nextUrl">
            <button @click="paginateFunction(nextUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize u-t uk-text-small" uk-tooltip="Suivant">
              <span uk-icon="chevron-right"></span>
            </button>
          </template>
        </div>
        <!-- // -->
      </div>
      
      <div class="">
        <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
          <thead>
            <tr>
              <th v-for="(head,index) in tableHead" :key="index">{{head}}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in serials" :key="s.numero_materiel">
              <td>{{s.numero_materiel}} <sup v-if="s.etat !== '-'" class="uk-badge">{{s.etat}}</sup>   </td>
              <td>{{s.depot}}</td>
              <td>{{s.article}}</td>
              <td>{{s.origine}}</td>
            </tr>
          </tbody>
        </table>
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
          // paginate
            nextUrl : "",
            lastUrl : "",
            perPage : "",
            currentPage : 1,
            firstPage : "",
            firstItem : 1,
            total : 0,
    // #####
          fieldsExport : {
            'Numero Materiel' : 'numero_materiel',
            'Depot' : 'depot',
            'Article ' : 'article',
            'Origine' : 'origine'
          },
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
        paginateFunction : async function (url) {
          try {
            
            let response = await axios.get(url)
            if(response && response.data) {
              
              this.serials = response.data.all

              this.nextUrl = response.data.next_url
              this.lastUrl = response.data.last_url
              this.currentPage = response.data.current_page
              this.firstPage = response.data.first_page
              this.firstItem = response.data.first_item,
              this.total = response.data.total
            }
          }
          catch(error) {
            alert("Erreur!")
            console.log(error)
          }
        },
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
            this.serials = response.data.all

            this.nextUrl = response.data.next_url
            this.lastUrl = response.data.last_url
            this.perPage = response.data.per_page
            this.firstItem = response.data.first_item
            this.total = response.data.total

            this.isLoading = false
          } catch (e) {
              alert(e)
          }
        }
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
        typeUser () {
          return this.$store.state.typeUser
        },
        userLocalisation() {
          return this.$store.state.userLocalisation
        }
      }
    }
</script>
