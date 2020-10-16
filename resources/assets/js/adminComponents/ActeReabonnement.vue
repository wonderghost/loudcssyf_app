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

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-4@m">
                <label for=""><span uk-icon="users"></span> Vendeurs</label>
                <select name="" id="" class="uk-select uk-border-rounded"></select>
            </div>
            <div class="uk-width-1-4@m">
                <label for=""><span uk-icon="calendar"></span> Du</label>
                <input type="date" class="uk-input uk-border-rounded" placeholder="Debut">
            </div>
            <div class="uk-width-1-4@m">
                <label for=""><span uk-icon="calendar"></span> Au</label>
                <input type="date" class="uk-input uk-border-rounded" placeholder="Fin">
            </div>
        </div>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@m">
                <ve-histogram :data="acteReabo" :settings="chartSetting"></ve-histogram>
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
            this.getData()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                chartSetting : {
                    metrics : ['acte_reabo'],
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
                    this.isLoading = true
                    let response = await axios.get('/admin/performance/acte-reabonnement')
                    if(response) {
                        this.acteReabo.rows = response.data
                        this.isLoading = false
                    }
                }
                catch(error) {
                    alert(error)
                }
            }
        }
    }
</script>