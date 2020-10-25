<template>
    <div class="uk-container uk-container-large">

        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

    <h3><router-link to="/objectifs/visu"><button class="uk-button uk-button-default uk-button-small uk-border-rounded" uk-tooltip="Visu Objectif"><span uk-icon="arrow-left"></span></button></router-link> Tous les objectifs</h3>
    <hr class="uk-divider-small">            

        <!-- modal details objectif -->
        <div id="modal-detailObjectifs" class="uk-modal-container" uk-modal>
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
                    <!-- EXPORTER LES DONNES VIA EXCEL -->
                    <download-to-excel :data-to-export="detailsObjectif" :data-fields="field_export" file-name="etat-des-objectifs"></download-to-excel>
                    <!-- // -->
                    <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
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

        <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
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
                        <button @click="getDetailsObjectif(o.id)" uk-tooltip="Cliquez pour voir les details" class="uk-text-capitalize uk-button uk-button-small uk-button-primary uk-border-rounded"><span uk-icon="icon : more"></span></button>
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
            UIkit.offcanvas($("#side-nav")).hide();
            this.getAllObjectifs()
        },
        data() {
            return {
                field_export : {
                    'Vendeurs' : 'vendeur.localisation',
                    'Objectif Recrutement' : 'obj_recru',
                    'Realise Recrutement' : 'realise.recrutement',
                    'Objectif Reabonnement' : 'obj_rea',
                    'Realise Reabonnement' : 'realise.reabonnement',
                    'Classe Recrutement' : 'class_recru',
                    'Classe Reabonnement' : 'class_rea',
                    'Bonus' : 'bonus'
                },
                // 
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
                    this.isLoading = true
                    let response = await axios.get('/admin/objectifs/list-all')
                    if(response && response.data) {
                        this.objectifs = response.data
                        this.isLoading = false
                    }
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
                    if(response && response.data) {
                        UIkit.modal($("#modal-detailObjectifs")).show()
                        this.detailsObjectif = response.data
                        this.isLoading = false
                    }

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
