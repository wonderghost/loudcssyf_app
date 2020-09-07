<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <div class="uk-container uk-container-large">
            <h3 class="uk-margin-top">Transfert Afrocash Courant</h3>
            <hr class="uk-divider-small">
            <div v-for="e in errors" :key="e" class="uk-alert-danger uk-width-1-3@m" uk-alert>
                <a href="#" class="uk-alert-close" uk-close></a>
                <p>
                    {{e}}
                </p>
            </div>
            <div class="uk-grid-small uk-grid-divider" uk-grid>
                <div class="uk-width-1-3@m">
                    <form @submit.prevent="sendTransaction()">
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="icon : user"></span> Destinataire</label>
                            <select v-model="dataForm.destinataire" class="uk-select uk-border-rounded">
                                <option value="">-- Choisissez le pdraf --</option>
                                <option :value="u.user.username" v-for="(u,index) in withoutActifUser" :key="index">{{u.user.localisation}}</option>
                            </select>
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="icon : credit-card"></span> Montant</label>
                            <input v-model="dataForm.montant" type="number" class="uk-input uk-border-rounded" placeholder="Montant de la transaction">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="icon : lock"></span> Confirmez le mot de passe</label>
                            <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe">
                        </div>
                        <div class="uk-margin-small-top">
                            <button class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
                        </div>
                    </form>
                </div>
                 <!-- SOLDE AFROCASH -->
                <div class="uk-width-1-3@m">
                    <div class="uk-card uk-card-default uk-border-rounded" style="box-shadow : none !important ; border : solid 1px #ddd !important;">
                        <div class="uk-card-header">
                            <h3 class="uk-card-title">SOLDE AFROCASH (GNF)</h3>
                        </div>
                        <div class="uk-card-body uk-text-center">
                            <span class=" uk-card-title">{{userAccount.solde | numFormat}}</span>
                        </div>
                    </div>                
                </div>
            <!-- // -->
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
        mounted() {
            UIkit.offcanvas($("#side-nav")).hide();
            this.getOtherPdraf()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                destinataire : [],
                dataForm : {
                    _token : "",
                    destinataire : "",
                    montant : 0,
                    password_confirmation : ""
                },
                errors : [],
                userAccount : {}
            }
        },
        methods : {
            sendTransaction : async function () {
                try {
                    this.isLoading = true
                    this.dataForm._token = this.myToken
                    let response = await axios.post('/user/pdraf/send-transaction',this.dataForm)
                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Success !")
                        Object.assign(this.$data,this.$options.data())
                        this.getOtherPdraf()
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
            },
            getOtherPdraf : async function () {
                try {
                    this.isLoading = true
                    var response = await axios.get('/user/pdraf/get-other-users')
                    if(response) {
                        this.destinataire = response.data
                    }
                     response = await axios.get('/user/pdraf/get-solde')
                     if(response) {
                         this.userAccount = response.data
                     }

                        this.isLoading = false
                } catch(error) {
                    this.isLoading = false
                    alert(error)
                }
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            },
            userName() {
                return this.$store.state.userName
            },
            withoutActifUser() {
                return this.destinataire.filter((u) => {
                    return u.user.username != this.userName
                })
            }
        }
    }
</script>
