<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loading="dots"></loading>

        <form @submit.prevent="makeFilter()" class="uk-grid-small" uk-grid>
            <div class="uk-width-1-1@m uk-grid-small" uk-grid>
                <template v-if="errors.length" v-for="error in errors">
                    <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-1@m" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{error}}</p>
                    </div>
                </template>
            </div>
            <div class="uk-width-1-5@m uk-wdith-1-1@s">
                <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
                <select class="uk-select uk-border-rounded" v-model="filterFormData.vendeurs">
                    <option value=""> -- Tous les vendeurs -- </option>
                    <option :value="u.username" v-for="u in users">{{u.localisation}}</option>
                </select>
            </div>
            <div class="uk-width-1-6@m uk-width-1-1@s">
                <label for=""><span uk-icon="icon : calendar"></span> Du</label>
                <input type="date" v-model="filterFormData.du" class="uk-input uk-border-rounded">
            </div>
            <div class="uk-width-1-6@m uk-width-1-1@s">
                <label for=""><span uk-icon="icon : calendar"></span> Au</label>
                <input type="date" v-model="filterFormData.au" class="uk-input uk-border-rounded">
            </div>
            <div class="uk-width-1-6@m uk-width-1-1@s">
                <button type="submit" class="uk-width-1-2@m uk-width-1-1@s uk-button uk-border-rounded uk-button-small uk-button-primary" style="margin-top : 28px;">
                    <!-- <span uk-icon="icon : arrow-right"></span>  -->
                    Filtrez
                </button>
            </div>
        </form>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@m uk-width-1-1@s">
                <div class="uk-card uk-card-default uk-card-small" style="box-shadow : none">
                    <div class="uk-card-header" style="border : none !important">
                        <h4 class="uk-card-title">Recrutement</h4>
                        <ve-histogram :data="recrutement"></ve-histogram>
                    </div>
                </div>
            </div>
            <div class="uk-width-1-2@m uk-width-1-1@s">
                <div class="uk-card uk-card-default uk-card-small" style="box-shadow : none">
                    <div class="uk-card-header" style="border : none !important">
                        <h4 class="uk-card-title">Reabonnement</h4>
                        <ve-histogram :data="reabonnement"></ve-histogram>
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
        created(){
            this.isLoading = true
        },
        mounted() {
            this.getRecrutementStat()
        },
        data() {
            return {
                recrutement: {
                    columns: ['date','quantite','ttc','commission'],
                    rows: []
                },
                reabonnement : {
                    columns : ['date','ttc','commission'],
                    rows : []
                },
                isLoading : false,
                fullPage : true,
                users : [],
                filterFormData : {
                    _token : "",
                    vendeurs : "",
                    du : "",
                    au : ""
                },
                errors : []
            }
        },
        methods : {
            getRecrutementStat : async function () {
                this.isLoading = true
                try {
                    let response = await axios.get('/admin/perform-obj/recrutement')
                    this.recrutement.rows = response.data

                    response = await axios.get('/admin/perform-obj/reabonnement')
                    this.reabonnement.rows = response.data

                    // get users li 
                    response = await axios.get('/admin/all-vendeurs')
                    this.users = response.data
                    
                    this.isLoading = false
                } catch(error) {
                    alert(error)
                }
            },
            makeFilter : async function() {
                try {
                    this.filterFormData._token = this.myToken
                    let response = await axios.post('/admin/perform-obj/filter',this.filterFormData)
                    this.recrutement.rows = response.data.recrutement
                    this.reabonnement.rows = response.data.reabonnement
                } catch(error) {
                    if(error.response.data.errors) {
                        let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                        this.errors.push(errorTab[prop][0])
                            // alert(errorTab[prop][0])
                        }
                    } else {
                        this.errors.push(error.response.data)
                        // alert(error.response.data)
                    }
                }
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
