<template>
    <div>
        <div id="modal-detail-rapport" class="uk-modal-container" uk-modal>
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h4 class="">
                        <span>Rapport du : {{rapportInfos.date}}</span>,
                        <span>Enregistre le : {{rapportInfos.created_at}}</span>,
                        <span>Pour : {{rapportInfos.vendeurs}}</span>,
                        <span>{{rapport.length}} Materiel(s)</span>
                    </h4>
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
                                <td class="uk-alert uk-alert-primary" v-if="d.upgrade_state">{{d.old_formule}} -> {{d.formule}} <span class="uk-text-bold" v-if="d.upgrade_state">({{ d.upgrade_state }})</span></td>
                                <td v-else>{{d.formule}}</td>
                                <td>{{d.duree}}</td>
                                <td>{{d.debut}}</td>
                                <td>{{d.fin}}</td>
                                <td>
                                    <span v-for="o in d.option" :key="o.id_option">{{o.id_option}} , </span>
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
