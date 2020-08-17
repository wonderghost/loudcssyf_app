<template>
   <div class="uk-container uk-container-large">

       <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <h3>Profile</h3>
        <hr class="uk-divider-small">

       <ul uk-tab>
		    <li><a href="#">Mes informations</a></li>
		</ul>

        <ul class="uk-switcher uk-margin">
            <li>
                <!-- MES INFORMATIONS -->
                <div class="uk-grid-small uk-grid-divider" uk-grid>
                    <div class="uk-width-1-3@m">
                        <h4>Modifier le mot de passe</h4>
                        <hr class="uk-divider-small">
                        <form @submit.prevent="changePasswordForm()" uk-grid class="uk-grid-small">
                            <!-- Erreor block -->
                            <template v-if="errors.length" v-for="error in errors">
                                <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-1@m" uk-alert>
                                    <a href="#" class="uk-alert-close" uk-close></a>
                                    <p>{{error}}</p>
                                </div>
                            </template>
                            <div class="uk-width-1-1@">
                                <label for="">Ancien Mot de passe</label>
                                <input v-model="formPasswordData.old_password" type="password" class="uk-input uk-border-rounded">
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for=""><span uk-icon="icon : lock"></span>  Nouveau Mot de passe</label>
                                <input v-model="formPasswordData.new_password" type="password" class="uk-input uk-border-rounded">
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for=""><span uk-icon="icon : lock"></span> Confirmez le nouveau mot de passe</label>
                                <input v-model="formPasswordData.new_password_confirmation" type="password" class="uk-input uk-border-rounded">
                            </div>
                            <div>
                                <button class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                            </div>
                        </form>
                    </div>
                    <div class="uk-width-2-3@m">
                        <h4>Details</h4>
                        <hr class="uk-divider-small">
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-1-3@m">
                                <label for=""><span uk-icon="icon : "></span> Numero Distributeur</label>
                                <span class="uk-input uk-border-rounded">{{profile.agence.num_dist}}</span>
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for=""><span uk-icon="icon : mail"></span> Email</label>
                                <span class="uk-input uk-border-rounded">{{profile.user.email}}</span>
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for=""><span uk-icon="icon : user"></span> Identificant</label>
                                <span class="uk-input uk-border-rounded">
                                    {{profile.user.username}}
                                </span>
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for=""><span uk-icon="icon : phone"></span> Telephone</label>
                                <span class="uk-input uk-border-rounded">{{profile.user.phone}}</span>
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for="">Niveau d'access</label>
                                <span class="uk-input uk-border-rounded">{{profile.user.type}}</span>
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for=""><span uk-icon="icon : info"></span> Etat du compte</label>
                                <span v-if="profile.user.status == 'unblocked'" class="uk-input uk-border-rounded uk-alert-success">Fonctionnelle</span>
                                <span v-else class="uk-input uk-border-rounded uk-alert-danger">Bloquer</span>
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for=""><span uk-icon="icon : location"></span> Agence</label>
                                <span class="uk-input uk-border-rounded">{{profile.user.localisation}}</span>
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for=""><span uk-icon="icon : world"></span> Ville</label>
                                <span class="uk-input uk-border-rounded">{{profile.agence.ville}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- // -->
            </li>
        </ul>
   </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components : {
            Loading
        },
        mounted() {
            this.getInfosProfile()
        },
        data () {
            return {
                profile : {},
                formPasswordData : {
                    _token : "",
                    old_password : "",
                    new_password : "",
                    new_password_confirmation : ""
                },
                errors : [],
                isLoading : false,
                fullPage : true
            }
        },
        methods : {
            getInfosProfile : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/user/get-profile-infos')
                    if(response) {
                        this.profile = response.data
                        this.isLoading = false
                    }
                } catch(error) {
                    alert(error)
                }
            },
            changePasswordForm : async function() {
                try {
                    this.isLoading = true
                    this.formPasswordData._token = this.myToken
                    let response = await axios.post('/user/change-password',this.formPasswordData)
                    if(response.data == 'done') {
                        alert("Success !")
                        location.reload()
                    }
                } catch(error) {
                    this.isLoading = false
                    if(error.response.data.errors) {
                        let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                            this.errors.push(errorTab[prop][0])
                        }
                    } 
                    else {
                        this.errors.push(error.response.data)
                    }
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
