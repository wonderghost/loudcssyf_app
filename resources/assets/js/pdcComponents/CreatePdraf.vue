<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

         <div class="uk-container uk-container-large">
            <h3 class="uk-margin-top">Creation Pdraf</h3>

            <hr class="uk-divider-small">
            <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>Agence</th>
                        <th>Adresse</th>
                        <th>Demande le</th>
                        <th>Repondu le </th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(l,index) in listRequest" :key="index">
                        <td>{{l.email}}</td>
                        <td>{{l.telephone}}</td>
                        <td>{{l.agence}}</td>
                        <td>{{l.adresse}}</td>
                        <td>{{l.created_at}}</td>
                        <!-- REPONDU LE  -->
                        <td>
                            <span v-if="l.confirmed_at && !l.removed_at">{{l.confirmed_at}}</span>
                            <span v-if="l.removed_at && !l.confirmed_at">{{l.removed_at}}</span>
                            <span v-if="!l.confirmed_at && !l.removed_at">-</span>
                        </td>
                        <!-- // -->
                        <!-- STATUS -->
                        <td>
                            <span v-if="!l.confirmed_at && !l.removed_at" class="uk-alert-warning uk-text-capitalize"> en attente</span>
                            <span v-if="l.confirmed_at && !l.removed_at" class="uk-alert-success uk-text-capitalize"> Confirmer</span>
                            <span v-if="!l.confirmed_at && l.removed_at" class="uk-alert-danger uk-text-capitalize"> Annuler</span>
                        </td>
                        <!-- // -->
                    </tr>
                </tbody>
            </table>
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
        this.getListCreateRequest()
    },
    data() {
        return {
            isLoading : false,
            fullPage : true,
            listRequest : []
        }
    },
    methods : {
        getListCreateRequest : async function () {
            try {
                this.isLoading = true
                let response = await axios.get('/user/pdc/get-create-request')
                if(response && response.data) {
                    this.isLoading = false
                    this.listRequest = response.data
                }
            } catch(error) {
                alert(error)
            }
        }
    }
}
</script>
