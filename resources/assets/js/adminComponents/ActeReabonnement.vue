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

        <div class="uk-width-1-2@m">
            <ve-histogram :data="acteReabo" :settings="chartSetting"></ve-histogram>
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
            this.getData()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                chartSetting : {
                    metrics : ['acte_reabo','line'],
                    showLine : ['line']
                },
                acteReabo : {
                    columns : ['date','acte_reabo'],
                    rows : []
                }
            }
        },
        methods : {
            getData : async function () {
                try {
                    let response = await axios.get('/admin/performance/acte-reabonnement')
                    if(response) {
                        console.log(response.data)
                        this.acteReabo.rows = response.data
                    }
                }
                catch(error) {
                    alert(error)
                }
            }
        }
    }
</script>
