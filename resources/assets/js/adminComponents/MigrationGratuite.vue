<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <h3>Migration Gratuite</h3>
        <hr class="uk-divider-small">

        <div class="uk-card uk-card-default uk-box-shadow-small" style="margin : auto 10% 10% ; padding  : 2%">
            <div style="margin : auto 30% auto 30%">
                <div v-for="(err,index) in errors" :key="index">
                    <div class="uk-alert-danger" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        <p>{{ err }}</p>
                    </div>
                </div>
            </div>
            <form @submit.prevent="sendRapport()">
                <div class="uk-grid uk-grid-small">
                    <div class="uk-width-1-3@m">
                        <label for="">Date</label>
                        <input v-model="form.date" type="date" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Vendeurs</label>
                        <select v-model="form.vendeurs" class="uk-select uk-border-rounded">
                            <option value="">--Selectionnez un vendeur --</option>
                            <option v-for="(u,index) in users" :key="index" :value="u.username">{{ u.localisation }}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Quantite</label>
                        <input v-model="form.quantite_materiel" min="1" type="number" class="uk-input uk-border-rounded">
                    </div>
                    <template v-for="input in parseInt(form.quantite_materiel)">
                        <div class="uk-width-1-3@m uk-margin-small" :key="input">
                            <input v-model="form.serial_number[input-1]" type="text" class="uk-input uk-border-rounded" :placeholder="'Materiel-'+input">
                        </div>
                    </template>
                </div>
                <button type="submit" class="uk-button uk-button-primary uk-border-rounded">Envoyez</button>
            </form>
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
                errors : [],
                form : {
                    _token : "",
                    date : "",
                    promo_id : "none",
                    quantite_materiel : 1,
                    serial_number : [],
                    vendeurs : ""
                },
                users : [],
                errors : []
            }
        },
        methods : {
            onInit : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/user/all-vendeurs')
                    if(response) {
                        this.users = response.data
                        this.isLoading = false
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            sendRapport : async function () {
                try {
                    this.isLoading = true
                    this.form._token = this.myToken

                    let response = await axios.post('/user/send-rapport/migration-gratuite',this.form)
                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Rapport ajoute .")
                        location.reload()
                    }
                }
                catch(error) {
                    this.isLoading = false
                    if(error.response.data.errors) {
                    let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                            this.errors.push(errorTab[prop][0])
                        }
                    } 
                    else {
                        this.errors.push(error.response.data)
                    }
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
