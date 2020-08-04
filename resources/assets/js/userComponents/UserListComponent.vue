<template>
    <div>
        <table  class="uk-table uk-table-divider uk-table-striped uk-table-small uk-table-hover uk-table-responsive">
            <thead>
            <tr>
                <th v-for="head in tableHeader" :key="head"> {{ head }} </th>
                <th colspan="3" class="uk-text-center">-</th>
            </tr>
            </thead>
            <tbody>
                <tr v-for="user in list" :key="user.username">
                    
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
                        class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small uk-text-capitalize"> reset <span uk-icon="icon : refresh"></span>
                        </button> 
                    </template>
                    <template v-if="typeUser == 'pdc'">
                        <!-- <button
                        uk-toggle="target : #reset-modal"
                        @click="userToReset = user.localisation , userId = user.username"
                        :id="user.username"
                        class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small uk-text-capitalize"> reset <span uk-icon="icon : refresh"></span>
                        </button> 
                        <button @click="blockUser(user.username)" v-if="user.status === 'unblocked'" :id="user.username" class="uk-button uk-button-small uk-button-danger uk-text-capitalize uk-border-rounded uk-box-shadow-small">bloquer <span uk-icon="icon : lock"></span> </button>
                        <button @click="unblockUser(user.username)" v-else :id="user.username" class="uk-button uk-button-small uk-button-default uk-alert-success uk-text-capitalize uk-border-rounded uk-box-shadow-small">debloquer <span uk-icon="icon : unlock"></span> </button> -->
                    </template>
                    <template v-else>
                        <a :href="userEditLink+'/'+user.username" :id="user.username" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-text-capitalize uk-box-shadow-small">editer <span uk-icon="icon : pencil"></span> </a>
                        <button
                        uk-toggle="target : #reset-modal"
                        @click="userToReset = user.localisation , userId = user.username"
                        :id="user.username"
                        class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small uk-text-capitalize"> reset <span uk-icon="icon : refresh"></span>
                        </button> 
                        <button @click="blockUser(user.username)" v-if="user.status === 'unblocked'" :id="user.username" class="uk-button uk-button-small uk-button-danger uk-text-capitalize uk-border-rounded uk-box-shadow-small">bloquer <span uk-icon="icon : lock"></span> </button>
                        <button @click="unblockUser(user.username)" v-else :id="user.username" class="uk-button uk-button-small uk-button-default uk-alert-success uk-text-capitalize uk-border-rounded uk-box-shadow-small">debloquer <span uk-icon="icon : unlock"></span> </button>
                    </template>
                    </td>
                </tr>
            </tbody>
        </table>        
    </div>
</template>

<script>
export default {
    props : {
        list : Array
    },
    data() {
        return {
            tableHeader : ['username','type','email','phone','agence','status'],
            userEditLink : "/admin/edit-users",
            userBlockLink : "/admin/block-user",
            userUnblockLink : '/admin/unblock-user',
            userResetLink : "/admin/reset-user",
            userToReset : "",
            userId : "",
            userPassword : "",
        }
    },
    computed : {
        typeUser() {
            return this.$store.state.typeUser
        }
    }
}
</script>
