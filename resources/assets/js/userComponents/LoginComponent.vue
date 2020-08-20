<template>
    <div class="">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <div class="uk-visible@s">
            <img src="img/background.jpg" class="img-bg" alt="">
            <div class="uk-position-top login-page-content uk-border-rounded">
                <div class="uk-grid-small uk-padding-small" uk-grid>
                    <div class="uk-width-1-2@m uk-width-1-3@l">
                        <div class="uk-card uk-card-default uk-border-rounded uk-box-shadow-small">
                            <div class="uk-card-header">
                                <h4 class="uk-card-title"><span uk-icon="icon : unlock"></span> Se Connecter</h4>
                            </div>
                            <div class="uk-card-body">
                                <!-- Error block -->
                                <template v-if="errors.length" v-for="error in errors">
                                <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                                    <a href="#" class="uk-alert-close" uk-close></a>
                                    <p>{{error}}</p>
                                </div>
                                </template>
                                <form @submit.prevent="login()">
                                    <div class="uk-margin-small">
                                        <label for=""><span uk-icon="icon : user"></span> Username</label>
                                        <input v-model="formData.username" type="text" class="uk-input uk-border-rounded" placeholder="Nom d'utilisateur">
                                    </div>
                                    <div class="uk-margin-small">
                                        <label for=""><span uk-icon="icon : lock"></span> Password</label>
                                        <input v-model="formData.password" type="password" class="uk-input uk-border-rounded" placeholder="Mot de passe">                                           
                                    </div>
                                    <button type="submit" class="uk-buton uk-button-small uk-button-primary uk-border-rounded" style="cursor : pointer">Login <span uk-icon="icon : arrow-right"></span></button>
                                </form>
                            </div>
                            <div class="uk-card-footer">
                                <a href="#" class="uk-text-xsmall uk-text-meta">Mot de passe oubli&eacute;</a> |
                                <a href="#" class="uk-text-xsmall uk-text-meta">Besoin d'aide</a>
                            </div>
                        </div>
                        <div class="">
                            <p class="uk-text-meta uk-text-small" style="font-size : 8px">
                                En me connectant , je reconnais avoir lu et accepter les conditions generales
                                affichees sur le site web de Loudcssyf
                            </p>
                        </div>
                    </div>
                    <div class="uk-width-1-2@m uk-width-1-2@l">
                        <div class="uk-card uk-card-default uk-border-rounded">
                            <div class="uk-card-body">
                                <div class="uk-grid-small" uk-grid>
                                    <div class="uk-width-2-3@m">
                                        <h4>Pour utiliser la plateforme sur votre telephone</h4>
                                        <ol>
                                            <li>Ouvrez votre appareil photo sur le telephone</li>
                                            <li>Positionnez votre telephone face a cet ecran pour scanner le code</li>
                                        </ol>
                                    </div>
                                    <div class="uk-width-1-3@m">
                                        <img src="img/qr-code-2.png" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- mobile view -->
        <div class="uk-hidden@s">
            <div class="uk-background-top-right uk-background-cover uk-padding uk-height-large uk-panel uk-flex uk-flex-middle uk-flex-center" style="background-image: url(img/background.jpg);background-size : 100% 100% !important;">
                <div class="uk-width-1-1@s">
                        <div class="uk-card uk-card-default uk-border-rounded uk-box-shadow-small">
                            <div class="uk-card-header">
                                <h4 class="uk-card-title"><span uk-icon="icon : unlock"></span> Se Connecter</h4>
                            </div>
                            <div class="uk-card-body">
                                <!-- Erreor block -->
                                <template v-if="errors.length" v-for="error in errors">
                                <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-1@s" uk-alert>
                                    <a href="#" class="uk-alert-close" uk-close></a>
                                    <p>{{error}}</p>
                                </div>
                                </template>
                                <form @submit.prevent="login()">
                                    <div class="uk-margin-small">
                                        <label for=""><span uk-icon="icon : user"></span> Username</label>
                                        <input v-model="formData.username" type="text" class="uk-input uk-border-rounded" placeholder="Nom d'utilisateur">
                                    </div>
                                    <div class="uk-margin-small">
                                        <label for=""><span uk-icon="icon : lock"></span> Password</label>
                                        <input v-model="formData.password" type="password" class="uk-input uk-border-rounded" placeholder="Mot de passe">                                           
                                    </div>
                                    <button type="submit" class="uk-buton uk-button-small uk-width-1-1@s uk-button-primary uk-border-rounded" style="cursor : pointer">Login <span uk-icon="icon : arrow-right"></span></button>
                                </form>
                            </div>
                            <div class="uk-card-footer">
                                <div class="uk-child-width-1-2@s uk-grid-small" uk-grid>
                                    <div><a class="uk-text-xsmall uk-text-meta" href="">Mot de passe oubli&eacute;?</a></div>
                                    <div><a class="uk-text-xsmall uk-text-meta" href="">Besoin d'aide?</a></div>
                                </div>
                            </div>
                        </div>
                        <div class="">
                            <p class="uk-text-small" style="font-size : 11px ; color : #fff">
                                En me connectant , je reconnais avoir lu et accepter les conditions generales
                                affichees sur le site web de Loudcssyf
                            </p>
                        </div>
                    </div>
            </div>  
        </div>
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

    },
    data() {
        return {
            formData : {
                _token : "",
                useranme : "",
                password : ""
            },
            isLoading : false,
            fullPage : true,
            errors : []
        }
    },
    methods : {
        login : async function () {
            this.isLoading = true
            this.errors = []
            try {
                this.formData._token = this.myToken
                let response = await axios.post('/login',this.formData)
                if(response.data == 'done') {
                    location.reload()
                }
            } catch(error) {
                this.isLoading = false
              if(error.response.data.errors) {
                let errorTab = error.response.data.errors
                for (var prop in errorTab) {
                  this.errors.push(errorTab[prop][0])
                }
              } else {
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