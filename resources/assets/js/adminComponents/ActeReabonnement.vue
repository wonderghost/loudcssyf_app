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
            <li><router-link to="/performances"><span>Performances</span></router-link></li>
            <li><span>Acte de Reabonnement</span></li>
        </ul>

        <h3 class="uk-margin-top">Acte de reabonnement</h3>
        <hr class="uk-divider-small">

        <div class="uk-container">
            <div class="uk-row">
                <form @submit.prevent="filterActeReabo()" class="uk-grid-small" uk-grid>
                    <!-- <div class="uk-width-1-4@m">
                        <label for=""><span uk-icon="users"></span> Vendeurs</label>
                        <select v-model="form.user" class="uk-select uk-border-rounded">
                            <option value="">Tous</option>
                            <option value="u.username" :key="index" v-for="(u,index) in users">{{ u.localisation }}</option>
                        </select>
                    </div> -->
                    <div class="uk-width-1-4@m">
                        <label for=""><span uk-icon="calendar"></span> Mois</label>
                        <select v-model="form.month" class="uk-select uk-border-rounded">
                            <option value="">-- Selectionnez le mois --</option>
                            <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-4@m">
                        <label for=""><span uk-icon="calendar"></span> Annee</label>
                        <select v-model="form.year" class="uk-select uk-border-rounded" placeholder="Anee">
                            <option value="">-- Selectionnez l'annee --</option>
                            <option value="2020">2020</option>
                            <option value="2021">2021</option>
                        </select>
                    </div>
                    <div class="uk-width-1-4@m">
                        <button type="submit" class="uk-button uk-margin-top uk-border-rounded uk-button-primary">Exporter <span uk-icon="download"></span></button>
                    </div>
                </form>
                

                <template v-if="materiels">
                    <download-to-excel :data-to-export="materiels" :data-fields="field_export" :file-name="form.month+form.year+'acte_reabonnement'"></download-to-excel>
                </template>
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-1@m">
                        <ve-histogram :data="acteReabo" :settings="chartSetting"></ve-histogram>
                    </div>
                </div>
            </div>
        </div>
        


    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import VeHistogram from 'v-charts/lib/histogram.common'

    export default {
        components : {
            Loading,
            VeHistogram
        },
        mounted() {
            this.onInit()
        },
        data() {
            return {
                field_export : {
                    'Materiel'   : 'serial',
                    'Formule' : 'formule',
                    'Duree' : 'duree',
                    'Debut' : 'debut',
                },
                isLoading : false,
                fullPage : true,
                form : {
                    _token : "",
                    user : "",
                    month : "",
                    year : ""
                },
                chartSetting : {
                    metrics : ['acte_reabo'],
                },
                acteReabo : {
                    columns : ['date','acte_reabo'],
                    rows : []
                },
                reabo_acte_list : [],
                users : [],
                materiels : [],
                errors : []
            }
        },
        methods : {
            filterActeReabo : async function () {
                try
                {
                    this.isLoading = true
                    this.form._token = this.myToken
                    let response = await axios.post('/admin/performance/acte-reabonnement',this.form)
                    if(response)
                    {
                        this.materiels = response.data.data
                        this.isLoading = false
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
            },
            onInit : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/admin/performance/acte-reabonnement')
                    let theResponse = await axios.get('/admin/all-vendeurs')
                    if(response && theResponse) {
                        this.acteReabo.rows = response.data.stats
                        this.users = theResponse.data
                        this.isLoading = false
                    }
                }
                catch(error) {
                    alert(error)
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