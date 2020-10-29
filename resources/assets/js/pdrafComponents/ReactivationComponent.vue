<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <ul class="uk-breadcrumb">
            <li><router-link uk-tooltip="Tableau de bord" to="/dashboard"><span uk-icon="home"></span></router-link></li>
            <li><router-link to="/reabonnement-afrocash">Reabonnement Afrocash</router-link></li>
            <li><span>Reactivation Materiel</span></li>
        </ul>

        <h3 class="uk-margin-top">Reactivation Materiel</h3>
        <hr class="uk-divider-small">

        <ul class="uk-subnav uk-subnav-pill" uk-switcher>
            <li><a class="uk-border-rounded" href="#">Reactiver</a></li>
            <li><a class="uk-border-rounded" href="#">Historique de Reactivation</a></li>
        </ul>
        
        <ul class="uk-switcher">
            <li>
                <div v-if="errors" class="uk-width-1-3@m">
                    <div class="uk-alert-danger uk-border-rounded" uk-alert v-for="(error,index) in errors" :key="index">
                        <a uk-close class="uk-alert-close"></a>
                        <p>{{error}}</p>
                    </div>
                </div>
                <form @submit.prevent="sendReactivationRequest()" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-3@m uk-grid-small" uk-grid>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="cog"></span> Numero Materiel * ({{dataForm.serial_number.length}})</label>
                            <input v-model="dataForm.serial_number" type="text" class="uk-border-rounded uk-input" placeholder="Entrez le numero materiel">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="lock"></span>  Confirmez votre mot de passe *</label>
                            <input v-model="dataForm.password_confirmation" type="password" class="uk-border-rounded uk-input" placeholder="Entrez votre mot de passe">
                        </div>
                    </div>
                    <div class="uk-width-1-1@m">
                        <button class="uk-button uk-border-rounded uk-button-small uk-button-primary">Envoyez</button>
                    </div>
                </form>
            </li>
            <li>
                <div class="uk-width-1-2@m">
                    <historique-reactivation-materiel :view-modal="false"></historique-reactivation-materiel>
                </div>
            </li>
        </ul>        

    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        components : {
            Loading
        },
        mounted() {},
        data() {
            return {
                isLoading : false,
                fullPage : true,
                dataForm : {
                    _token : "",
                    serial_number : "",
                    password_confirmation : ""
                },
                errors : []
            }
        },
        methods : {
            sendReactivationRequest : async function () {
                try {
                    this.isLoading = true
                    this.dataForm._token = this.myToken
                    let response = await axios.post('/user/pdraf/reactivation-materiel',this.dataForm)
                    if(response && response.data) {
                        this.isLoading = false
                        alert('Success !')
                        Object.assign(this.$data,this.$options.data.call(this))
                        location.reload()
                    }
                }
                catch(error) {
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
