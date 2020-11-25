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
            <li><router-link to="/dashboard"><span uk-icon="home"></span></router-link></li>
            <li><router-link to="/rapport/list">Tous les rapport</router-link></li>
            <li><span>Exporter</span></li>
        </ul>

        <h3 class="uk-margin-top">Exporter</h3>
        <hr class="uk-divider-small">

        <template v-if="list.length">
            <download-to-excel :data-to-export="list" :data-fields="field_export" file-name="details_materiel_rapport_vente"></download-to-excel>
        </template>

        <!-- Erreor block -->
        <template v-if="errors">
            <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
            </div>
        </template>

        <div class="uk-width-1-1@m uk-grid-small" uk-grid>
            <div class="uk-width-1-1@m">
                <form @submit.prevent="exportDataRequest()" uk-grid class="uk-grid-small">
                    <div class="uk-width-1-6@m">
                        <label for=""><span uk-icon="calendar"></span>Du</label>
                        <input v-model="formData.from" type="date" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for=""><span uk-icon="calendar"></span>Au</label>
                        <input v-model="formData.to" type="date" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Type</label>
                        <select v-model="formData.type" class="uk-select uk-border-rounded">
                            <option value="all">Tous</option>
                            <option value="recrutement">Recrutement</option>
                            <option value="reabonnement">Reabonnement</option>
                            <option value="migration">Migration</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for=""><span uk-icon="users"></span> Vendeurs</label>
                        <select v-model="formData.user" class="uk-select uk-border-rounded">
                            <option value="all">Tous</option>
                            <option v-for="(u,index) in users" :key="index" :value="u.username">{{ u.localisation }}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m" style="margin-top : 1%">
                        <button class="uk-button uk-margin-small-top uk-padding-remove uk-button-small uk-button-primary uk-border-rounded" type="submit">envoyez</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="uk-width-1-1@m uk-margin-top">
            <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
                <thead>
                    <tr>
                        <th>materiel</th>
                        <th>article</th>
                        <th>vendeur</th>
                        <th>Rapport</th>
                        <th>date d'activation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(l,index) in list" :key="index">
                        <td>{{l.materiel}}</td>
                        <td>{{l.produit}}</td>
                        <td>{{l.vendeur}}</td>
                        <td>{{l.rapport}}</td>
                        <td>{{l.date_rapport}}</td>
                    </tr>
                </tbody>
            </table>
            <div class="uk-flex uk-flex-center">
                <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
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
            this.getData();
        },
        data() {
            return {
                // data export fields
                field_export : {
                    'materiel':'materiel',
                    'article':'produit',
                    'vendeur':'vendeur',
                    'rapport':'rapport',
                    'date_rapport' : 'date_rapport'
                },
                isLoading : false,
                fullPage : true,
                users : [],
                formData : {
                    _token : "",
                    from : "",
                    to : "",
                    type : "all",
                    user : "all"
                },
                errors : [],
                list : []
            }
        },
        methods : {
            getData : async function () {
                try {
                    let response = await axios.get('/admin/all-vendeurs')
                    if(response) {
                        this.users = response.data
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            exportDataRequest : async function () {
                try {
                    this.isLoading = true

                    this.errors = []
                    this.formData._token = this.myToken
                    let response = await axios.post('/admin/rapport/list/export',this.formData)
                    if(response) {
                        
                        this.list = response.data
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
