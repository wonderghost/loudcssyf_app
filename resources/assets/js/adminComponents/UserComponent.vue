<template>
<div class="uk-container uk-container-large">
  <loading :active.sync="isLoading"
      :can-cancel="false"
      :is-full-page="fullPage"
      loader="dots"></loading>

  <h3 class="uk-margin-top">Tous les Utilisateurs</h3>
  <hr class="uk-divider-small">
  
  <filter-user-component></filter-user-component>
  
  <table  class="uk-table uk-table-divider uk-table-striped uk-table-small uk-table-hover uk-table-responsive">
    <thead>
      <tr>
        <th v-for="head in tableHeader"> {{ head }} </th>
        <th colspan="3" class="uk-text-center">-</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="user in filteredUser" :key="user.username">
        <!-- <td v-for="column in user">{{column}}</td> -->
        <td>{{user.username}}</td>
        <td>{{user.type}}</td>
        <td :uk-tooltip="user.email">{{user.email.substring(0,30)+'...'}}</td>
        <td>{{user.phone}}</td>
        <td :uk-tooltip="user.localisation">{{user.localisation.substring(0,30)+'...'}}</td>
        <td>{{user.status}}</td>
        <td> 
          <template v-if="typeUser == 'commercial'">
              <button
              uk-toggle="target : #reset-modal"
              @click="userToReset = user.localisation , userId = user.username"
              :id="user.username"
              class="uk-button uk-text-small uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small uk-text-capitalize"> reset <span uk-icon="icon : refresh"></span>
            </button> 
          </template>
          <template v-else>
            <a :href="userEditLink+'/'+user.username" :id="user.username" class="uk-button uk-text-small uk-button-small uk-button-primary uk-border-rounded uk-text-capitalize uk-box-shadow-small">editer <span uk-icon="icon : pencil"></span> </a>
            <button
              uk-toggle="target : #reset-modal"
              @click="userToReset = user.localisation , userId = user.username"
              :id="user.username"
              class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small uk-text-small uk-text-capitalize"> reset <span uk-icon="icon : refresh"></span>
            </button> 
            <button @click="blockUser(user.username)" v-if="user.status === 'unblocked'" :id="user.username" class="uk-button uk-button-small uk-button-danger uk-text-capitalize uk-text-small uk-border-rounded uk-box-shadow-small">bloquer <span uk-icon="icon : lock"></span> </button>
            <button @click="unblockUser(user.username)" v-else :id="user.username" class="uk-button uk-button-small uk-button-default uk-alert-success uk-text-capitalize uk-border-rounded uk-box-shadow-small">debloquer <span uk-icon="icon : unlock"></span> </button>
          </template>
        </td>
      </tr>
    </tbody>
  </table>

  <div id="reset-modal" uk-modal="esc-close : false ; bg-close : false">
    <div class="uk-modal-dialog">
        <div class="uk-modal-header">
            <h3 class="uk-modal-title">Reinitialisation de Mot de Passe</h3>
            <p>Vous etes sur le point de reinitialiser le mot de passe pour : <span class="uk-text-bold">{{ userToReset }}</span> </p>
        </div>
        <div class="uk-modal-body">
          <form @submit="resetUser($event)">
            <input type="hidden"  v-model="myToken">
            <input type="hidden"  v-model="userId">
            <div class="uk-margin-small">
              <label for="">Confirmez votre mot de passe</label>
              <input type="password" v-model="userPassword" class="uk-input uk-border-rounded uk-box-shadow-hover-small" autofocus placeholder="Entrez votre mot de passe">
            </div>
            <button class="uk-button uk-button-primary uk-button-small uk-border-rounded uk-box-shadow-small" type="submit">validez</button>
          </form>
        </div>
        <div class="uk-modal-footer uk-text-right">
          <button class="uk-button uk-button-danger uk-button-small uk-border-rounded uk-box-shadow-small uk-modal-close" type="button">annuler</button>
        </div>
    </div>
</div>
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
        this.listUser()
      },
      data :function () {
        return {
          tableHeader : ['username','type','email','phone','agence','status'],
          userEditLink : "/admin/edit-users",
          userBlockLink : "/admin/block-user",
          userUnblockLink : '/admin/unblock-user',
          userResetLink : "/admin/reset-user",
          userToReset : "",
          userId : "",
          userPassword : "",
          errorHandler : "",
          isLoading : false,
          fullPage : true
        }
      },
      components : {
        Loading
      },
      methods : {
        listUser :async function () {
          try {
            var tmp = this
            let response = await axios.get('/admin/users/list')
            tmp.$store.state.users = response.data
            this.isLoading = false;
          }
          catch (e) {
            alert(e)
          }
        },
        resetUser : function (event,username) {
          UIkit.modal($("#reset-modal")).hide()
          event.preventDefault()
          let link = this.userResetLink
          let userPassword = this.userPassword
          let userId = this.userId

          axios.post(link,{
            admin_password : userPassword,
            user : userId
          }).then(function (response) {

              alert("Success !")
              Object.assign(this.$data,this.$options.data())
              this.listUser()
          }).catch(function (error) {
            UIkit.modal($("#reset-modal")).show()
            UIkit.notification({
              message : error.response.data,
              status : 'danger',
              pos : 'top-center'
            })
          })
        }
        ,
        blockUser : function (username) {
          var tmp = this
          let link = this.userBlockLink
          UIkit.modal.confirm("Etes vous sur de vouloir effectuer cette action ?").then(function () {
            tmp.isLoading = true
            axios.post(link,{
              ref : username
            }).then(function (response) {
              tmp.isLoading = false
              UIkit.modal.alert("Utilisateur bloque avec success!").then(function () {
                location.reload()
              })
            }).catch(function (error) {
              console.log(error)
            })
          })
        },
        unblockUser : function (username) {
          var tmp = this
          let link = this.userUnblockLink
          UIkit.modal.confirm("Etes-vous sur de vouloir effectuer cette action ?").then(function () {
            tmp.isLoading = true
            axios.post(link , {
              ref : username
            }).then(function (response) {
              tmp.isLoading = false
              UIkit.modal.alert("Utilisateur debloque avec success!").then(function () {
                location.reload()
              })
            }).catch(function (error) {
              alert(error)
            })
          })
        }
      },
      computed : {
        typeUser() {
          return  this.$store.state.typeUser
        },
        myToken () {
          return this.$store.state.myToken
        }
        ,
        users () {
          return this.$store.state.users
        },
        filteredUser() {
          return this.users.filter((user) => {
            if(this.$store.state.searchState) {
              return user.localisation.toUpperCase().match(this.$store.state.searchText.toUpperCase())
            } else {
              return user.type.match(this.$store.state.typeUserFilter)
            }
          })
        }
      }
    }
</script>
