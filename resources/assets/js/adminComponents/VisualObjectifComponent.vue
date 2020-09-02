<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

    <h3 class="uk-margin-top">Visu Objectifs</h3>
    <hr class="uk-divider-small">    

    <nav class="" uk-navbar>
        <div class="uk-navbar-left">
            <ul class="uk-navbar-nav">
                <li class="uk-active"><router-link to="/objectifs/new">Nouvel Objectif</router-link></li>
                <li class="uk-active"><router-link to="/objectifs/all">Tous les objectifs</router-link></li>
            </ul>

        </div>
    </nav>    

        <!-- Erreor block -->
        <template v-if="errors.length" v-for="error in errors">
            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
            </div>
        </template>

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
                        <ve-histogram :data="reabonnementData" :settings="chartSettingReabo"></ve-histogram>
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
            UIkit.offcanvas($("#side-nav")).hide();
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
                chartSettingReabo : {
                    axisSite : {right : ['pourcent']},
                    yAxisType : ['M','percent'],
                    yAxisName : ['TTC','%']
                },
                 recrutementData : {
                     columns : ['class','plafond','realise','pourcent'],
                     rows : []
                 },
                 reabonnementData : {
                     columns : ['class','plafond','realise','pourcent'],
                     rows : []
                 },
                 errors : []
            }
        },
        methods : {
            getRecrutementStat : async function () {
                try {
                    this.isLoading = true
                    // get Recrutement Stat
                    let response = await axios.get('/admin/objectif/recrutement')
                    // get Reabonnement Stat
                    this.recrutementData.rows = response.data

                    response = await axios.get('/admin/objectif/reabonnement')
                    this.reabonnementData.rows = response.data

                    this.isLoading = false
                    
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
