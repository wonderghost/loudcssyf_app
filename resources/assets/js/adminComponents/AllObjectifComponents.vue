<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <!-- modal details objectif -->
        <div id="modal-detailObjectifs" class="uk-modal-container" uk-modal="esc-close : false;bg-close : false">
            <div class="uk-modal-dialog">
                <!-- <button class="uk-modal-close-default" type="button" uk-close></button> -->
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Details Objectif</h2>
                </div>
                <div class="uk-modal-body">
                    <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-4@m">
                            <label for="">
                                <span uk-icon="icon : users"></span>
                                Vendeurs
                            </label>
                            <input type="search" v-model="autocompleteSearch" class="uk-input uk-border-rounded">
                        </div>
                    </div>
                    <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
                        <thead>
                            <tr>
                                <th>Vendeurs</th>
                                <th>Obj:Recru</th>
                                <th>Realise:Recru</th>
                                <th>Obj:Rea</th>
                                <th>Realise:Rea</th>
                                <th>Class:Recru</th>
                                <th>Class:Rea</th>
                                <th>Bonus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in listonDetailsObjectif" :key="d.vendeur.username">
                                <td>{{d.vendeur.localisation}}</td>
                                <td>{{d.obj_recru}}</td>
                                <td>{{d.realise.recrutement}} ({{(d.realise.recrutement/d.obj_recru) | numFormat('0.[00]%')}}) </td>
                                <td>{{d.obj_rea | numFormat}}</td>
                                <td>{{d.realise.reabonnement | numFormat}} ({{ (d.realise.reabonnement/ d.obj_rea) | numFormat('0.[00]%') }})</td>
                                <td>{{d.class_recru}}</td>
                                <td>{{d.class_rea}}</td>
                                <td>{{d.bonus | numFormat}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="uk-modal-footer uk-text-right">
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
                objectifs : [],
                detailsObjectif : [],
                autocompleteSearch : ""
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
                    this.isLoading = true
                    let response = await axios.post('/admin/objectif/get-details',{
                            _token : this.myToken,
                            id_objectif : id
                        })

                    this.detailsObjectif = response.data
                    this.isLoading = false

                } catch(error) {
                    this.isLoading = false
                    alert(error)
                }
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            },
            listonDetailsObjectif() {
                return this.detailsObjectif.filter( (word) => {
                    return word.vendeur.localisation.match(this.autocompleteSearch.toUpperCase())
                })
            }
        }
    }
</script>
