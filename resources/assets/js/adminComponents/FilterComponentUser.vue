<template>

  <div class="uk-child-width-1-4@m" uk-grid>
    <div class="">
      <label for=""> <span uk-icon="icon : search"></span> Recherche</label>
      <input v-model="wordSearch" @keyup="search(wordSearch)" type="text" name="" value="" class="uk-input uk-border-rounded uk-box-shadow-hover-smal">
    </div>
  <div class="">
    <label for=""> <span uk-icon="icon : users"></span> Vendeurs</label>
    <select @change="filterUserByType(type)" v-model="type" class="uk-select uk-border-rounded uk-box-shadow-hover-small" name="">
      <option value="">tous</option>
      <option v-for="(type , key) in userType"  :value="key" :key="key">{{ type }}</option>
    </select>
  </div>
  <div class="">
    <p class="uk-text-bold">Resultat de recherche pour : <span v-if="wordSearch !== ''"> {{wordSearch}} </span><span v-else> {{type}}</span>   </p>
  </div>
</div>

</template>

<script>
    export default {
      mounted () {
        this.search("")
        this.filterUserByType("")
      },
        data () {
          return {
            userType : {
              'v_da' : 'distributeur agree',
              'v_standart' : 'vendeur standart',
              'logistique' : 'Logistique',
              'gcga' : 'gestionnaire cga',
              'grex' : 'gestionnaire rex',
              'coursier' : 'coursier',
              'controleur' : 'controleur',
              'pdc' : 'Point de controle',
              'pdraf' : 'Point de Recharge Afrocash',
              'technicien'  : 'Technicien',
              'client'  : 'Client'
            },
            wordSearch : "",
            type : ""
          }
        }
        ,
        methods : {
          search (text) {
            this.$store.commit('searchText',text)
          },
          filterUserByType (type) {
            this.$store.commit('filterUsers',type)
          }
        },
        computed : {
          users() {
            return this.$store.state.users
          }
        }
    }
</script>
