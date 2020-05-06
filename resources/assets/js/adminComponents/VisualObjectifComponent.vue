<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>
        <form @submit.prevent="" id="form-filter" class="uk-grid-small" uk-grid>
            <div class="uk-width-1-1@m uk-grid-small" uk-grid>
                
            </div>
            <div class="uk-width-1-5@m uk-wdith-1-1@s">
                <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
                <select class="uk-select uk-border-rounded" >
                    <option value=""> -- Tous les vendeurs -- </option>
                </select>
            </div>
            <div class="uk-width-1-6@m uk-width-1-1@s">
                <label for=""><span uk-icon="icon : calendar"></span> Du</label>
                <input type="date" class="uk-input uk-border-rounded">
            </div>
            <div class="uk-width-1-6@m uk-width-1-1@s">
                <label for=""><span uk-icon="icon : calendar"></span> Au</label>
                <input type="date" class="uk-input uk-border-rounded">
            </div>
            <div class="uk-width-1-6@m uk-width-1-1@s">
                <button type="submit" class="uk-width-1-2@m uk-width-1-1@s uk-button uk-border-rounded uk-button-small uk-button-primary" style="margin-top : 28px;">
                    Filtrez
                </button>
                <button type="button" 
                        class="uk-button uk-border-rounded uk-button-small uk-button-primary" style="margin-top : 28px;"
                        uk-tootip="Supprimez le filtre">
                    <span uk-icon="icon : close"></span> 
                </button>
            </div>
        </form>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@m uk-width-1-1@s">
                <div class="uk-card uk-card-default uk-card-small" style="box-shadow : none">
                    <div class="uk-card-header" style="border : none !important">
                        <h4 class="uk-card-title">Recrutement</h4>
                        <ve-histogram :data="recrutementData" :settings="chartSetting"></ve-histogram>
                    </div>
                </div>
            </div>
            <div class="uk-width-1-2@m uk-width-1-1@s">
                <div class="uk-card uk-card-default uk-card-small" style="box-shadow : none">
                    <div class="uk-card-header" style="border : none !important">
                        <h4 class="uk-card-title">Reabonnement</h4>
                        
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
            this.getRecrutementStat()
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
                chartSettingTest : {

                },
                 recrutementData : {
                     columns : ['class','plafond','realise','pourcent'],
                     rows : []
                 }
            }
        },
        methods : {
            getRecrutementStat : async function () {
                try {
                    let response = await axios.get('/admin/objectif/recrutement')
                    // console.log(response.data)
                    this.recrutementData.rows = response.data
                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {

        }
    }
</script>
