<template>
    <div>
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
                    let response = await axios.get('/user/chart/objectif/recrutement')
                    console.log(response.data)
                    this.recrutementData.rows[0].classe = "Classe : "+response.data.classe_recrutement
                    this.recrutementData.rows[0].plafond = response.data.plafond_recrutement
                    this.recrutementData.rows[0].realise = response.data.realise.recrutement
                    this.recrutementData.rows[0].pourcent = response.data.realise.recrutement/response.data.plafond_recrutement
                    // 
                    this.reabonnementData.rows[0].classe = "Classe : "+response.data.classe_reabonnement
                    this.reabonnementData.rows[0].plafond = response.data.plafond_reabonnement
                    this.reabonnementData.rows[0].realise = response.data.realise.reabonnement
                    this.reabonnementData.rows[0].pourcent = response.data.realise.reabonnement/response.data.plafond_reabonnement

                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {

        }
    }
</script>
