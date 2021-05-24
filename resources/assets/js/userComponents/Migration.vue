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
            <li><span>Vente</span></li>
            <li><span>Migration</span></li>
        </ul>

        <h3 class="uk-margin-top">Migration</h3>
        <hr class="uk-divider-small">
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@m">
                <div class="uk-alert-danger" uk-alert v-for="(err,index) in errors" :key="index">
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{ err }}</p>
                </div>
                <form @submit.prevent="sendMigrationRequest()" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-1@m">
                        <h3>Infos Client</h3>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Nom*</label>
                        <input v-model="form.nom" type="text" class="uk-input uk-border-rounded" placeholder="Nom du client">
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Prenom*</label>
                        <input v-model="form.prenom" type="text" class="uk-input uk-border-rounded" placeholder="Prenom du client">
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Telephone Client*</label>
                        <input v-model="form.telephone" type="text" class="uk-input uk-border-rounded" placeholder="Numero de telephone du client">
                    </div>
                    <div class="uk-width-1-1@m">
                        <hr class="uk-divider-small">
                    </div>
                    <div class="uk-width-1-1@m">
                        <h3>Infos Materiel</h3>
                    </div>
                    <div class="uk-width-2-3@m">
                        <label for="">Numero Materiel*({{form.materiel.length}})</label>
                        <input v-model="form.materiel" type="text" class="uk-input uk-border-rounded" placeholder="Numero du materiel">
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Montant (GNF)</label>
                        <span class="uk-input uk-border-rounded">{{ form.montant | numFormat }}</span>
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for="">Confirmez le mot de passe</label>
                        <input v-model="form.password" type="password" class="uk-input uk-border-rounded" placeholder="Mot de passe">
                    </div>
                    <div class="uk-width-1-1@m">
                        <button type="submit" class="uk-button uk-button-primary uk-width-1-1@s uk-width-1-4@m uk-width-1-6@l uk-button-small uk-border-rounded">Envoyez</button>
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
        beforeMount() {
            this.onInit()
        },
        data : () => {
            return {
                isLoading : false,
                fullPage : true,
                errors : [],
                form : {
                    _token : "",
                    nom : "",
                    prenom : "",
                    telephone : "",
                    materiel : "",
                    password : "",
                    montant : 0,
                }
            }
        },
        methods : {
            onInit : async function()
            {
                try
                {
                    Object.assign(this.$data,this.$options.data())
                    let response = await axios.get('/vente/migration')
                    if(response)
                    {
                        this.form.montant = response.data
                    }
                }
                catch(error)
                {
                    alert(error)
                }
            },
            sendMigrationRequest : async function () {
                try {
                    this.isLoading = true
                    this.errors = []
                    this.form._token = this.myToken
                    let response = await axios.post('/vente/migration',this.form)
                    if(response && response.data == 'done')
                    {
                        alert('Success.')
                        this.onInit()
                    }
                }
                catch(error)
                {
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
            myToken()
            {
                return this.$store.state.myToken
            }
        }
    }
</script>
