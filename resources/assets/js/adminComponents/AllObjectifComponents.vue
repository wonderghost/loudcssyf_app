<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <!-- modal details objectif -->
        <div id="modal-detailObjectifs" class="uk-modal-container" uk-modal="esc-close : false;bg-close : false">
            <div class="uk-modal-dialog">
                <button class="uk-modal-close-default" type="button" uk-close></button>
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Details Objectif</h2>
                </div>
                <div class="uk-modal-body">
                    
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <!-- <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button> -->
                    <button class="uk-button uk-button-danger uk-border-rounded uk-button-small uk-text-capitalize uk-modal-close" type="button">Fermer</button>
                </div>
            </div>
        </div>
        <!-- // -->

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
                        <button @click="getDetailsObjectif(o.id)" uk-toggle="target : #modal-detailObjectifs" uk-tooltip="Cliquez pour voir les details" class="uk-text-capitalize uk-button uk-button-small uk-button-primary uk-border-rounded"><span uk-icon="icon : more"></span></button>
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
            },
            getDetailsObjectif : async function (id) {
                try {
                    let response = await axios.post('/admin/objectif/get-details',{
                            _token : this.myToken,
                            id_objectif : id
                        })

                    console.log(response.data)
                } catch(error) {
                    alert(error)
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
