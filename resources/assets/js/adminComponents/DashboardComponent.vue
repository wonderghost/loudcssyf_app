<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>
        
        <template v-if="theUser == 'admin'">
            <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-4@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header" style="border: none !important;">
                    <h5 class="uk-card-title">Utilisateurs</h5>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <ve-pie class="uk-padding-remove" :data="userData"></ve-pie>
                </div>
            </div>
            <div class="uk-width-1-4@m uk-card uk-card-default uk-margin-remove uk-padding-remove uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header" style="border:none !important;">
                    <h5 class="uk-card-title">Commande Credit</h5>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <ve-pie :data="commandData"></ve-pie>
                </div>
            </div>
            <div class="uk-width-1-4@m uk-card uk-card-default uk-margin-remove uk-padding-remove uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header" style="border : none;">
                    <h5 class="uk-card-title">Commande Materiel</h5>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <ve-pie :data="commandMaterial"></ve-pie>
                </div>
            </div>
            <div class="uk-width-1-4@m uk-card uk-card-default uk-margin-remove uk-padding-remove uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header" style="border:none !important;">
                    <h5 class="uk-card-title">Livraison</h5>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <ve-pie :data="livraison"></ve-pie>
                </div>
            </div>
            <div class="uk-width-1-2@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header" style="border :none !important;">
                    <h5 class="uk-card-title">Depots</h5>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <ve-histogram :data="depotData"></ve-histogram>
                </div>
            </div>
            <div class="uk-width-1-2@m uk-card uk-card-default uk-margin-remove uk-padding-remove uk-card-small" style="box-shadow : none;">
                <div class="uk-card-header" style="border :none !important">
                    <h5 class="uk-card-title">Transactions</h5>
                </div>
                <div class="uk-card-body">
                    
                </div>
            </div>
        </div>
        </template>
        <!-- DA / VSTANDART DASHBOARD-->    
        <template v-if="theUser == 'vendeurs'">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-4@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                   <div class="uk-card-header" style="border : none !important">
                        <h5 class="uk-card-title">Inventaire</h5>
                        <ve-histogram :data="inventoryUser.data" :settings="inventoryUser.chartSettings"></ve-histogram>
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
            },
            commandMaterial : {
                columns : ['status','count'],
                rows : []
            },
            livraison : {
                columns : ['status','count'],
                rows : []
            },
            inventoryUser : {
                chartSettings : {
                    metrics : ['quantite'],
                    dimension : ['article']
                },
                data : {
                    columns : ['article','quantite'],
                    rows : []
                }
            }   
        }
    },
    methods : {
        buildChart : async function () {
            try {
                if(this.theUser == 'admin') {
                    let response = await axios.get('/admin/chart/user-stat')
                    this.userData.rows = response.data

                    response = await axios.get('/admin/inventory/depot')
                    this.depotData.rows = response.data

                    response = await axios.get('/admin/chart/command-stat')
                    this.commandData.rows = response.data

                    response = await axios.get('/admin/chart/command-material-stat')
                    this.commandMaterial.rows = response.data

                    response = await axios.get('/admin/chart/livraison-stat')
                    this.livraison.rows = response.data
                } else if(this.theUser == 'vendeurs') {
                    let response = await axios.get('/user/inventory/all-vendeur-material')
                    this.inventoryUser.data.rows = response.data
                }
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
