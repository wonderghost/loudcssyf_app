<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>
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
                    columns: ['month','quantite','ttc','commission'],
                    rows: []
                },
                reabonnement : {
                    columns : ['month','ttc','commission'],
                    rows : []
                },
                isLoading : false,
                fullPage : true
            }
        },
        methods : {
            getRecrutementStat : async function () {
                try {
                    let response = await axios.get('/admin/perform-obj/recrutement')
                    this.recrutement.rows = response.data

                    response = await axios.get('/admin/perform-obj/reabonnement')
                    this.reabonnement.rows = response.data

                    this.isLoading = false
                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {

        }
    }
</script>
