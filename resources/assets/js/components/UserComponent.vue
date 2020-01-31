<template>
  <table  class="uk-table uk-table-divider uk-table-striped uk-table-small uk-table-hover">
    <thead>
      <tr>
        <th v-for="head in tableHeader"> {{ head }} </th>
        <th colspan="3" class="uk-text-center">-</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="user in filteredUser" :key="user.username">
        <td v-for="column in user">{{column}}</td>
        <td> <a :href="userEditLink+'/'+user.username" :id="user.username" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small">editer</a> </td>
        <td> <a href="#" :id="user.username" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small">reset</a> </td>
        <td>
           <a href="#" v-if="user.status === 'unblocked'" :id="user.username" class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-box-shadow-small">bloquer</a>
           <a href="#" v-else :id="user.username" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small">debloquer</a>
         </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
    export default {
      mounted() {
        this.listUser()
      },
      data :function () {
        return {
          tableHeader : ['username','type','email','phone','agence','status'],
          userEditLink : "/admin/edit-users"
        }
      },
      methods : {
        listUser : function () {
          var tmp = this
          axios.get('/admin/users/list').then(function (response) {
            tmp.$store.state.users = response.data
           }).catch(function (error) {
             console.log(error)
           })
        }
      },
      computed : {
        users () {
          return this.$store.state.users
        },
        filteredUser() {
          return this.users.filter((user) => {
            if(this.$store.state.searchState) {
              return user.localisation.toUpperCase().match(this.$store.state.searchText.toUpperCase())
            } else {
              return user.type.match(this.$store.state.typeUser)
            }
          })
        }
      }
    }
</script>
