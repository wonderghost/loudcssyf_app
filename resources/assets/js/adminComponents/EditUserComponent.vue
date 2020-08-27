<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
      :can-cancel="false"
      :is-full-page="fullPage"
      loader="dots"></loading>


        <h3> <router-link to="/user/list"><button class="uk-button uk-button-small uk-button-default uk-border-rounded" uk-tooltip="Retour"><span uk-icon="arrow-left"></span></button></router-link> Modifier infos Utilisateurs</h3>
        <hr class="uk-divider-small">

        <template>
            <formUser type="edit" :data="userInfos"></formUser>
        </template>
        
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import formUser from './formUserComponent'

    export default {
        mounted () {
            this.getInfos()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                userInfos : {},  
            }
        },
        components : {
            Loading,
            formUser
        },
        methods : {
            getInfos : async function () {
                try {
                    let response = await axios.get('/admin/users/edit/'+this.$route.params.id)
                    this.userInfos = response.data
                } catch(error) {
                    alert("Error!")
                }
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
