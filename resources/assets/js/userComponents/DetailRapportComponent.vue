<template>
    <div>
        <div id="modal-detail-rapport" class="uk-modal-container" uk-modal>
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Rapport du : <span>{{rapportInfos.date}}</span></h2>
                </div>
                <div class="uk-modal-body">
                    <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
                        <thead>
                            <tr>
                                <th>Materiel</th>
                                <th>Formule</th>
                                <th>Duree</th>
                                <th>Debut</th>
                                <th>Fin</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(d,index) in rapport" :key="index">
                                <td>{{d.serial}}</td>
                                <td>{{d.formule}}</td>
                                <td>{{d.duree}}</td>
                                <td>{{d.debut}}</td>
                                <td>{{d.fin}}</td>
                                <td>
                                    <span v-for="o in d.option" :key="o.nom">{{o.nom}} , </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-danger uk-button-small uk-border-rounded uk-modal-close" type="button">Fermer</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        components : {
            Loading
        },
        props : {
            rapport : Array,
            rapportInfos : Object
        },
        mounted() {
            
        },
        data() {
            return {

            }
        },
        methods : {
            
        },
        computed : {
            
        }
    }
</script>
