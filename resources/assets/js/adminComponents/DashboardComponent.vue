<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>
        
        <template v-if="theUser == 'admin' || theUser == 'logistique' || theUser == 'gcga'">
            <div class="uk-grid-small" uk-grid>

                <template  v-if="theUser == 'admin'">
                    <div class="uk-width-1-4@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                        <div class="uk-card-header" style="border: none !important;">
                            <h5 class="uk-card-title">Utilisateurs</h5>
                        </div>
                        <div class="uk-card-body uk-padding-remove">
                            <ve-pie class="uk-padding-remove" :data="userData"></ve-pie>
                        </div>
                    </div>

                </template>

                <template v-if="theUser == 'gcga' || theUser == 'admin'">
                    <div class="uk-width-1-4@m uk-card uk-card-default uk-margin-remove uk-padding-remove uk-card-small" style="box-shadow : none;">
                        <div class="uk-card-header" style="border:none !important;">
                            <h5 class="uk-card-title">Commande Credit</h5>
                        </div>
                        <div class="uk-card-body uk-padding-remove">
                            <ve-pie :data="commandData"></ve-pie>
                        </div>
                    </div>
                </template>

                <template v-if="theUser == 'logistique' || theUser == 'admin'">
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
                </template>

            <template v-if="theUser == 'admin' || theUser == 'gcga'">
                <div class="uk-width-1-2@m uk-card uk-card-default uk-margin-remove uk-padding-remove uk-card-small" style="box-shadow : none;">
                    <div class="uk-card-header" style="border :none !important">
                        <h5 class="uk-card-title">Transactions</h5>
                    </div>
                    <div class="uk-card-body">
                        
                    </div>
                </div>
            </template>
        </div>
        </template>
        <!-- DA / VSTANDART DASHBOARD-->    
        <template v-if="theUser == 'v_da' || theUser == 'v_standart'">
            <form @submit.prevent="makeFilter()" class="uk-grid-small" uk-grid>
                <div class="uk-width-1-6@m">
                    <label for=""><span uk-icon="icon : calendar"></span> Du</label>
                    <input v-model="filterData.du" type="date" class="uk-input uk-border-rounded">
                </div>
                <div class="uk-width-1-6@m">
                    <label for=""><span uk-icon="icon : calendar"></span> Au</label>
                    <input v-model="filterData.au" type="date" class="uk-input uk-border-rounded">
                </div>
                <div class="uk-width-1-6@m uk-width-1-1@s">
                <button type="submit" class="uk-width-1-2@m uk-width-1-1@s uk-button uk-border-rounded uk-button-small uk-button-primary" style="margin-top : 28px;">
                    Filtrez
                </button>
                <button v-if="filterState"
                        @click="removeFilter()" 
                        type="button" 
                        class="uk-button uk-border-rounded uk-button-small uk-button-primary" style="margin-top : 28px;"
                        uk-tootip="Supprimez le filtre">
                    <span uk-icon="icon : close"></span> 
                </button>
            </div>
            </form>
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-6@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                   <div class="uk-card-header" style="border : none !important">
                        <h5 class="uk-card-title">Inventaire</h5>
                    </div> 
                    <div class="uk-card-body">
                        <ve-histogram :data="inventoryUser.data" :settings="inventoryUser.chartSettings"></ve-histogram>
                    </div>
                </div>
                <div class="uk-width-1-3@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                   <div class="uk-card-header" style="border : none !important">
                        <h5 class="uk-card-title">Performance Recrutement</h5>
                    </div> 
                    <div class="uk-card-body">
                        <ve-histogram :data="recrutement" :settings="chartSettings"></ve-histogram>
                    </div>
                </div>
                <div class="uk-width-1-3@m uk-card uk-padding-remove uk-margin-remove uk-card-default uk-card-small" style="box-shadow : none;">
                   <div class="uk-card-header" style="border : none !important">
                        <h5 class="uk-card-title">Performance Reabonnement</h5>
                    </div> 
                    <div class="uk-card-body">
                        <ve-histogram :data="reabonnement" :settings="chartSettings"></ve-histogram>
                    </div>
                </div>
            </div>

        </template>
        <!-- // -->
       
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
            },
            recrutement: {
                columns: ['date','ttc','commission','quantite'],
                rows: []
            },
            reabonnement : {
                columns : ['date','ttc','commission'],
                rows : []
            },
            chartSettings : {
                showLine : ['ttc','commission']
            },
            filterData : {
                _token : "",
                du : "",
                au : "",
                vendeurs : ""
            },
            filterState : false
        }
    },
    methods : {
        removeFilter : function () {
            this.buildChart()
            this.filterState = false
        },
        makeFilter : async function () {
            try {

                this.isLoading = true
                this.filterData._token = this.myToken
                this.filterData.vendeurs = this.userName

                let response = await axios.post('/user/chart/performances/filter-by-date',this.filterData)

                this.recrutement.rows = response.data.recrutement
                this.reabonnement.rows = response.data.reabonnement
                this.isLoading = false
                this.filterState = true

            } catch(error) {
                this.isLoading = false
                if(error.response.data.errors) {
                    let errorTab = error.response.data.errors
                    for (var prop in errorTab) {
                        alert(errorTab[prop][0])
                    }
                } else {
                    this.errors.push(error.response.data)
                    alert(error.response.data)
                }
            }
        },
        buildChart : async function () {
            try {
                this.isLoading = true
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
                } else if(this.theUser == 'v_da' || this.theUser == 'v_standart') {
                    let response = await axios.get('/user/inventory/all-vendeur-material')
                    this.inventoryUser.data.rows = response.data
                    // on recuperation les donnees grapphiques pour les porfermances et les objectifs
                    response = await axios.get('/user/chart/performances/recrutement')
                    this.recrutement.rows = response.data
                    // 
                    response = await axios.get('/user/chart/performances/reabonnement')
                    this.reabonnement.rows = response.data
                } else if(this.theUser == 'logistique') {
                    let response = await axios.get('/user/inventory/depot')
                    this.depotData.rows = response.data

                    response = await axios.get('/user/chart/command-material-stat')
                    this.commandMaterial.rows = response.data

                    response = await axios.get('/user/chart/livraison-stat')
                    this.livraison.rows = response.data
                } else if(this.theUser == 'gcga') {
                    let response = await axios.get('/user/chart/command-stat')
                    this.commandData.rows = response.data
                }

                this.isLoading = false
            } catch (error) {
                alert(error)
            }
        }
    },
    computed : {
        myToken() {
            return this.$store.state.myToken
        },
        userName() {
            return this.$store.state.userName
        }
    }
}
</script>
