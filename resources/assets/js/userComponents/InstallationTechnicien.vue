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
            <li><span>Technicien</span></li>
            <li><span>Installation</span></li>
        </ul>

        <h3>Installation Technicien</h3>
        <hr class="uk-divider-small">

        <ul class="uk-subnav uk-subnav-pill" uk-switcher>
            <li><a href="#" class="uk-border-rounded">Toutes les installations</a></li>
            <li><a href="#" class="uk-border-rounded">Nouvel Installation</a></li>
        </ul>

        <ul class="uk-switcher uk-margin">
            <li>
                <div class="uk-width-1-1@m">
                    <table class="uk-table uk-table-small uk-table-striped uk-table-divider uk-table-hover uk-table-responsive">
                        <thead>
                            <tr>
                                <th>date</th>
                                <th>Materiel</th>
                                <th>Client</th>
                                <th>Adresse</th>
                                <th>Telephone Client</th>
                                <th>Technicien</th>
                                <th>Vendeur</th>
                                <th>Status</th>
                                <th>Status Paiement</th>
                                <th>-</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(ins,index) in installations" :key="index">
                                <td>{{ ins.date }}</td>
                                <td>{{ ins.materiel }}</td>
                                <td>{{ ins.client }}</td>
                                <td>{{ ins.adresse }}</td>
                                <td>{{ ins.phone }}</td>
                                <td>{{ ins.technicien }}</td>
                                <td>{{ ins.vendeur }}</td>
                                <td>
                                    <span v-if="ins.status == 'effectif'" class="uk-label uk-label-success">{{ ins.status }}</span>
                                    <span v-else-if="ins.status == 'en_attente'" class="uk-label uk-label-primary">{{ ins.status }}</span>
                                    <span v-else class="uk-label uk-label-danger">{{ ins.status }}</span>
                                </td>
                                <td>
                                    <span class="uk-label uk-label-primary" v-if="!ins.validated">en attente</span>
                                    <span class="uk-label uk-label-success" v-else>payer</span>
                                </td>
                                <td>
                                    <button @click="validatePaymentIntervention(ins.id)" uk-tooltip="Valider le paiement" class="uk-button-primary uk-border-rounded"><i class="material-icons">check</i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </li>
            <li>
                <div v-if="errors" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m">
                        <div v-for="(err,index) in errors" :key="index" uk-alert class="uk-alert-danger">
                            <a class="uk-alert-close" uk-close></a>
                            <p>{{ err }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid-small" uk-grid>
                    <div class="uk-width-1-3@m">
                        <form @submit.prevent="sendInstallationRequest()" uk-grid class="uk-grid-small">
                            <div class="uk-width-1-1@m">
                                <label for="">Numero Materiel ({{ dataForm.materiel.length }})</label>
                                <input v-model="dataForm.materiel" placeholder="Numero du materiel" type="text" class="uk-border-rounded uk-input" maxlength="14">
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Nom</label>
                                <input v-model="dataForm.nom" placeholder="Nom du client" type="text" class="uk-border-rounded uk-input">
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Prenom</label>
                                <input v-model="dataForm.prenom" placeholder="Prenom du client" type="text" class="uk-border-rounded uk-input">
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Adresse</label>
                                <input v-model="dataForm.adress" placeholder="Adresse/Quariter du client" type="text" class="uk-border-rounded uk-input">
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Telephone</label>
                                <input v-model="dataForm.telephone" placeholder="Numero telephone du client" type="text" class="uk-border-rounded uk-input">
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Technicien</label>
                                <select v-model="dataForm.technicien" class="uk-select uk-border-rounded">
                                    <option value="none">-- Choisissez un Technicien --</option>
                                    <option :value="t.username" v-for="(t,index) in techniciens" :key="index"> {{ t.nom }} {{ t.prenom }} {{t.phone}} {{t.localisation}} ({{t.service_plus_id}})</option>
                                </select>
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Confirmez le mot de passe</label>
                                <input placeholder="Confirmez votre mot de passe" type="password" v-model="dataForm.password" class="uk-input uk-border-rounded">
                            </div>
                            <div class="uk-width-1-1@m">
                                <button class="uk-button uk-button-small uk-width-1-1@m uk-width-1-1@s uk-width-1-3@l uk-button-primary uk-border-rounded">Envoyez</button>
                            </div>
                        </form>
                    </div>
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
        mounted() {
            this.onInit()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                techniciens : [],
                installations : [],
                dataForm : {
                    materiel : "",
                    nom : "",
                    prenom : "",
                    adress : "",
                    telephone : "",
                    technicien : "none",
                    password : ""
                },
                errors : []
            }
        },
        methods : {
            onInit : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/user/technicien-list')
                    let theResponse = await axios.get('/user/technique/interventions')

                    if(response && theResponse) {
                        this.techniciens = response.data
                        this.installations = theResponse.data
                        this.isLoading = false
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            sendInstallationRequest : async function () {
                try {
                    this.isLoading = true
                    this.errors = []
                    this.dataForm._token = this.myToken
                    let response = await axios.post('/send-installation-request',this.dataForm)
                    if(response && response.data == 'done') {
                        alert('Success !')
                        Object.assign(this.$data,this.$options.data())
                        this.onInit()
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
            },
            validatePaymentIntervention : async function (id) {
                try {
                    this.errors = []
                    var conf = confirm("Vous etes sur de vouloir confirmer ?")
                    if(!conf) {
                        return 0
                    }
                    this.isLoading = true
                    let response = await axios.post('/user/technique/validate-intervention',{
                        _token : this.myToken,
                        intervention_id : id
                    })

                    if(response) {
                        alert("Success !")
                        this.onInit()
                        this.isLoading = false
                    }
                }
                catch(error) {
                    this.isLoading = false
                    if(error.response.data.errors) {
                        let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                            // this.errors.push(errorTab[prop][0])
                            alert(errorTab[prop][0])
                        }
                    } else {
                        // this.errors.push(error.response.data)
                        alert(error.response.data)
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
