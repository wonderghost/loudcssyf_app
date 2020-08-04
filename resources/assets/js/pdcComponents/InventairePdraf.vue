<template>
   <div>
       <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

       <div class="uk-container uk-container-large">
           <h3 class="uk-margin-top">Inventaire des PDRAF</h3>
           <hr class="uk-divider-small">

           <div class="uk-width-2-3@m">
                <table class="uk-table uk-table-small uk-table-striped uk-table-hover">
                    <thead>
                        <tr>
                            <th>Utilisateurs</th>
                            <th>Solde (GNF)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(i,index) in inventory" :key="index">
                            <td>{{i.utilisateur}}</td>
                            <td>{{i.solde | numFormat }}</td>
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
            this.getSoldePdraf()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                inventory : []
            }
        },
        methods : {
            getSoldePdraf : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/user/pdc/get-pdraf-solde')
                    if(response) {
                        this.isLoading = false
                        this.inventory = response.data
                    }
                } catch(error) {
                    this.isLoading = false
                    alert(error)
                }
            }
        },
        computed : {
            userLocalisation() {
                return this.$store.state.userLocalisation
            }
        }
    }
</script>
