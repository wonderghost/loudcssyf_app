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
            <li><span>Reseaux Afrocash</span></li>
            <li><span>Confirmation de commande</span></li>
        </ul>

        <h3 class="uk-margin-top">Confirmation de commande</h3>
        <hr class="uk-divider-small">

         <nav class="" uk-navbar>
            <div class="uk-navbar-left">
                <ul class="uk-navbar-nav">
                    <li class=""><router-link to="/pdc/command/list">Toutes les commandes</router-link></li>
                </ul>
            </div>
        </nav>

        <!-- FORMULAIRE DE CONFIRMATION DE LA COMMANDE -->
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-2-3@m">
                <form @submit.prevent="sendConfirmationRequest()" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-1@m uk-grid-small" uk-grid v-for="(item,index) in items" :key="index">
                        <div class="uk-width-1-5@m">
                            <label for="">Materiel</label>
                            <span class="uk-input uk-border-rounded">{{ item.item }}</span>
                        </div>
                        <div class="uk-width-1-5@m">
                            <label for="">Quantite</label>
                            <span class="uk-input uk-border-rounded">{{ item.quantite }}</span>
                        </div>
                        <div class="uk-width-1-5@m">
                            <label for="">A Livrer</label>
                            <span class="uk-input uk-border-rounded">{{ item.quantite_a_livrer }}</span>
                        </div>
                    </div>
                    
                    <div v-if="items[0]" class="uk-width-1-1@m uk-grid-small" uk-grid>
                        <div class="uk-width-1-5@m" v-for="q in items[0].quantite" :key="q">
                            <label for="">Materiel-{{q}}</label>
                            <input v-model="formData.serials[q-1]" type="text" class="uk-input uk-border-rounded" :placeholder="'Materiel-'+q">
                        </div>
                    </div>
                    <div class="uk-width-1-2@m uk-grid-small" uk-grid>
                        <div class="uk-width-1-1@m">
                            <label for="">Code de confirmation</label>
                            <input v-model="formData.confirm_code" type="text" class="uk-input uk-border-rounded" placeholder="Confirmation de la commande">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Confirmez le mot de passe</label>
                            <input v-model="formData.password_confirmation" type="password" class="uk-border-rounded uk-input" placeholder="Entrez votre mot de passe pour confirmer la commande">
                        </div>
                    </div>
                    <div class="uk-width-1-1@m">
                        <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- // -->

    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components : {
            Loading
        },
        mounted() {
            this.getData()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                items : [],
                formData : {
                    serials : [],
                    confirm_code : "",
                    password_confirmation : ""
                }
            }
        },
        methods : {
            sendConfirmationRequest : async function () {
                try {
                    this.formData._token = this.myToken
                    let response = await axios.post('/pdc/command/confirm/'+this.$route.params.id,this.formData)
                    if(response) {
                        console.log(response.data)
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            getData : async function () {
                try {
                    let response = await axios.get('/pdc/command/confirm/'+this.$route.params.id)
                    if(response) {
                        this.items = response.data
                    }
                }
                catch(error) {
                    alert(error)
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
