<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider">
            <thead>
                <tr>
                    <th>Debut</th>
                    <th>Fin</th>
                    <th>Evaluation</th>
                    <th>M-A</th>
                    <th>Recru:Class A</th>
                    <th>Recru:Class B</th>
                    <th>Recru:Class C</th>
                    <th>Rea:Class A</th>
                    <th>Rea:Class B</th>
                    <th>Rea:Class C</th>
                    <th>-</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="o in objectifs" :key="o.id">
                    <td>{{o.debut}}</td>
                    <td>{{o.fin}}</td>
                    <td>{{o.evaluation}} Mois</td>
                    <td>{{o.marge_arriere}}</td>
                    <td>{{o.recrutement.a}}</td>
                    <td>{{o.recrutement.b}}</td>
                    <td>{{o.recrutement.c}}</td>
                    <td>{{o.reabonnement.a}}</td>
                    <td>{{o.reabonnement.b}}</td>
                    <td>{{o.reabonnement.c}}</td>
                    <td>
                        <button uk-tooltip="Cliquez pour voir les details" class="uk-text-capitalize uk-button uk-button-small uk-button-primary uk-border-rounded"><span uk-icon="icon : more"></span></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components : {
            Loading
        },
        mounted() {
            this.getAllObjectifs()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                objectifs : []
            }
        },
        methods : {
            getAllObjectifs : async function () {
                try {
                    let response = await axios.get('/admin/objectifs/list-all')
                    this.objectifs = response.data
                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {

        }
    }
</script>
