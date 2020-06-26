<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>
        
        <div class="uk-width-1-1@m uk-grid-small" uk-grid>
            <div class="uk-width-1-6@m"></div>
            <div class="uk-width-1-2@m uk-inline">
                <span class="uk-form-icon uk-margin-small-left" uk-icon="icon: search"></span>
                <input type="text" class="uk-input uk-border-rounded uk-box-shadow-hover-small" placeholder="Recherche">
            </div>
            <div class="uk-width-1-6@m"></div>
            <div class="uk-width-1-1@m">
                <table class="uk-table uk-table-small uk-table-striped uk-table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prenom</th>
                            <th>Email</th>
                            <th>Telephone</th>
                            <th>Materiel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in contacts" :key="c.id">
                            <td>{{ c.nom }}</td>
                            <td>{{ c.prenom }}</td>
                            <td>{{ c.email }}</td>
                            <td>{{ c.phone }}</td>
                            <td>
                                <button uk-tooltip="voir le(s) materiel(s)" class="uk-button uk-button-small uk-button-primary uk-border-rounded"><span uk-icon="icon : more"></span></button>
                            </td>
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
            this.getAllContact()  
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                contacts : []
            }
        },
        methods : {
            getAllContact : async function () {
                try {
                    let response = await axios.get('/user/carnet-adresse/list')
                    if(response) {
                        this.contacts = response.data
                    }
                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {

        }
    }
</script>
