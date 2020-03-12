<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>
        
        <template v-if="theUser == 'admin'">
            <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-3@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header">
                    <h5 class="uk-card-title">Utilisateurs</h5>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <ve-pie class="uk-padding-remove" :data="userData"></ve-pie>
                </div>
            </div>
            <div class="uk-width-1-3@m uk-card uk-card-default uk-margin-remove uk-padding-remove uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header">
                    <h5 class="uk-card-title">Commande</h5>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <ve-pie :data="commandData"></ve-pie>
                </div>
            </div>
            <div class="uk-width-1-2@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header">
                    <h5 class="uk-card-title">Depots</h5>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <ve-histogram :data="depotData"></ve-histogram>
                </div>
            </div>
            <div class="uk-width-1-2@m uk-card uk-card-default uk-margin-remove uk-padding-remove uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header">
                    <h5 class="uk-card-title">Transactions</h5>
                </div>
                <div class="uk-card-body">
                    
                </div>
            </div>
        </div>
        </template>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import VeLine from 'v-charts/lib/line.common'
import VePie from 'v-charts/lib/pie.common'
import VeHistogram from 'v-charts/lib/histogram.common'



export default {
    components : {
        Loading,
        VePie,
        VeLine,
        VeHistogram
    },
    created() {
        this.isLoading = true
    },
    mounted() {
        this.buildChart()
    },
    props : {
        theUser : String
    },
    data() {
        return {
            isLoading : false,
            fullPage : true,
            userData : {
                columns : ['type','count'],
                rows : []
            },
            depotData : {
                columns: ['localisation', 'terminal', 'parabole'],
                rows: []
            },
            commandData : {
                columns : ['state','count'],
                rows : []
            }   
        }
    },
    methods : {
        buildChart : async function () {
            try {
                let response = await axios.get('/admin/chart/user-stat')
                this.userData.rows = response.data

                response = await axios.get('/admin/inventory/depot')
                this.depotData.rows = response.data

                response = await axios.get('/admin/chart/command-stat')
                this.commandData.rows = response.data

                this.isLoading = false
            } catch (error) {
                alert(e)
            }
        }
    },
    computed : {

    }
}
</script>
