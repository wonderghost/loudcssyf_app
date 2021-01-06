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
            <li><span>Installation</span></li>
        </ul>

        <h3 class="uk-margin-top">Installations</h3>
        <hr class="uk-divider-small">

        <div class="uk-width-1-2@m">
            <div v-for="(err,index) in errors" :key="index" class="uk-alert-danger" uk-alert>
                <a href="#" uk-close class="uk-alert-close"></a>
                <p>{{ err }}</p>
            </div>
        </div>

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
                    <tr v-for="(ins,index) in listInstallation" :key="index">
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
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        components: {
            Loading
        },
        mounted() {
            this.onInit()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                listInstallation : [],
                errors : []
            }
        },
        methods : {
            onInit : async function () {
                try {
                    let response = await axios.get('/user/technique/interventions')
                    if(response) {
                        this.listInstallation = response.data
                    }
                }
                catch(error) {
                    alert(error)
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
