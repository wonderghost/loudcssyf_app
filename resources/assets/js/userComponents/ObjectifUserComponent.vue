<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>
        
        <h3>Visu Objectif</h3>
        <hr class="uk-divider-small">

        <!-- Erreor block -->
        <template v-if="errors.length">
            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert v-for="(error,index) in errors" :key="index">
                <a href="#" class="uk-alert-close" uk-close></a>
                <p>{{error}}</p>
            </div>
        </template>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@m">
                <h5>Recrutement</h5>
                <ve-histogram :data="recrutementData" :settings="chartSetting"></ve-histogram>
            </div>
            <div class="uk-width-1-2@m">
                <h5>Reabonnement</h5>
                <ve-histogram :data="reabonnementData" :settings="chartSettingReabo"></ve-histogram>
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
            this.getVisuObject()
        },
        data() {
            return {
                errors : [],
                isLoading : false,
                fullPage : true,
                chartSetting : {
                    axisSite: { right: ['pourcent'] },
                    yAxisType: ['M', 'percent'],
                    yAxisName: ['Qte', '%']
                },
                chartSettingReabo : {
                    axisSite: { right: ['pourcent'] },
                    yAxisType: ['M', 'percent'],
                    yAxisName: ['TTC', '%']
                },
                recrutementData : {
                    columns : ['classe','plafond','realise','pourcent'],
                    rows : [{
                        'classe'     : '',
                        'plafond'   : '',
                        'realise' : '',
                        'pourcent' : ''
                    }]
                 },
                 reabonnementData : {
                     columns : ['classe','plafond','realise','pourcent'],
                     rows : [{
                         'classe' : '',
                         'plafond' : '',
                         'realise' : '',
                         'pourcent' : ''
                     }]
                 }
            }
        },
        methods : {
            getVisuObject : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/user/chart/objectif/recrutement')
                    
                    if(response && response.data) {

                        this.recrutementData.rows[0].classe = "Classe : "+response.data.classe_recrutement
                        this.recrutementData.rows[0].plafond = response.data.plafond_recrutement
                        this.recrutementData.rows[0].realise = response.data.realise.recrutement
                        this.recrutementData.rows[0].pourcent = response.data.realise.recrutement/response.data.plafond_recrutement
                        // 
                        this.reabonnementData.rows[0].classe = "Classe : "+response.data.classe_reabonnement
                        this.reabonnementData.rows[0].plafond = response.data.plafond_reabonnement
                        this.reabonnementData.rows[0].realise = response.data.realise.reabonnement
                        this.reabonnementData.rows[0].pourcent = response.data.realise.reabonnement/response.data.plafond_reabonnement

                        this.isLoading = false
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

        }
    }
</script>
