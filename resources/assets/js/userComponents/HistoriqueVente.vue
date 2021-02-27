<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <ul class="uk-breadcrumb">
            <li><router-link uk-tooltip="Tableau de bord" to="/dashboard"><span uk-icon="home"></span></router-link></li>
            <li><span>Vente</span></li>
            <li><span>Historique de ventes</span></li>
        </ul>

        <h3 class="uk-margin-top">Historique de ventes</h3>
        <hr class="uk-divider-small">

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-6@m">
                <label for="">Type</label>
                <select class="uk-select uk-border-rounded">
                    <option value="recrutement">Recrutement</option>
                    <option value="reabonnement">Reabonnement</option>
                    <option value="upgrade">Upgrade</option>
                </select>
            </div>
            <div class="uk-width-1-1@m">
                <table class="uk-table uk-table-small uk-table-hover uk-table-striped uk-table-responsive">
                    <thead>
                        <tr>
                            <th v-for="(h,index) in headers" :key="index">{{ h }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(v,index) in ventes" :key="index">
                            <td>{{ v.date }}</td>
                            <td>{{ v.materiel }}</td>
                            <td>{{ v.user }}</td>
                            <td>{{ v.nom }}</td>
                            <td>{{ v.prenom }}</td>
                            <td>{{ v.formule }}</td>
                            <td>{{ v.montant | numFormat }}</td>
                            <td>{{ v.duree }}</td>
                            <td>{{ v.telephone }}</td>
                            <td>{{ v.type }}</td>
                        </tr>
                    </tbody>
                </table>
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
        mounted() {
            this.onInit()
        },
        data : () => {
            return {
                isLoading : false,
                fullPage : true,
                headers : ['date','materiel','Utilisateur','nom','prenom','formule','montant (GNF)','duree','telephone','type'],
                ventes : []
            }
        },
        methods : {
            onInit : async function () {
                try {
                    let response = await axios.get('/ventes')
                    if(response) {
                        this.ventes = response.data
                    }
                }
                catch(error) {
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
