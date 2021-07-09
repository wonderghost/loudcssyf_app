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
            <li><span>Afrocash</span></li>
            <li><span>Retrait</span></li>
        </ul>

        <h3>Retrait Afrocash</h3>
        <hr class="uk-divider-small">

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-3@m">
                <form @submit.prevent="onSubmitRetraitRequest()">
                    <div uk-grid class="uk-grid-small">
                        <div v-for="(err,index) in errors" :key="index" class="uk-alert-danger uk-width-1-1@m" uk-alert>
                            <a class="uk-alert-close" uk-close></a>
                            <p>
                                {{ err }}
                            </p>
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="user"></span> Identifiant</label>
                            <input v-model="dataForm.identifiant" type="text" class="uk-input uk-border-rounded" placeholder="Identifiant du client (ex : 622 44 44 44)">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="credit-card"></span> Montant</label>
                            <input v-model="dataForm.montant" type="number" class="uk-input uk-border-rounded" placeholder="Montant du retrait (ex : 10000)">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="lock"></span> Mot de passe</label>
                            <input v-model="dataForm.password" type="password" class="uk-input uk-border-rounded" placeholder="Mot de passe utilisateur pour confirmation">
                        </div>
                    </div>
                    <div class="uk-margin-small">
                        <button type="submit" class="uk-button uk-button-primary uk-button-small uk-border-rounded">Envoyez</button>
                    </div>
                </form>
            </div>
        </div>

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
                    identifiant : "",
                    montant : "",
                    password : ""
                },
                errors : []
            }
        },
        methods : {
            onSubmitRetraitRequest : async function () {
                try {
                    this.isLoading = true
                    this.errors = []
                    this.dataForm._token = this.myToken
                    let response = await axios.post('/user/afrocash/retrait',this.dataForm)

                    if(response.status == 200) {
                        alert("Retrait initie avec success!")
                        this.dataForm.identifiant = ""
                        this.dataForm.montant = ""
                        this.dataForm.password = ""
                        this.isLoading = false
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
