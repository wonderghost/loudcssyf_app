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
            <li><router-link to="/dashboard"><span uk-icon="home"></span></router-link></li>
            <li><router-link to="/material/affectation">Affectation Materiel</router-link></li>
            <li><span>Depot -> Vendeur</span></li>
        </ul>

        <h3 class="uk-margin-top">Depot -> Vendeur</h3>
        <hr class="uk-divider-small">

        <!-- Erreor block -->
        <template v-if="errors.length">
            <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
            </div>
        </template>

        <div class="uk-width-1-2@m">
            <form @submit.prevent="sendTransfert()" uk-grid class="uk-grid-small">
                <div class="uk-width-1-2@m">
                    <label for="">Depot Origine</label>
                    <select v-model="formData.depot" class="uk-select uk-border-rounded">
                        <option value="">-- Selectionnez un depot --</option>
                        <option v-for="d in depots" :key="d.localisation" :value="d.localisation">{{d.localisation}}</option>
                    </select>
                </div>
                <div class="uk-width-1-2@m">
                    <label for="">Vendeurs</label>
                    <select v-model="formData.vendeur" class="uk-select uk-border-rounded">
                        <option value="">-- Selectionnez un vendeur --</option>
                        <option v-for="u in users" :key="u.username" :value="u.username">{{u.localisation}}</option>
                    </select>
                </div>
                <div class="uk-width-1-2@m">
                    <label for="">Numero Materiel</label>
                    <input v-model="formData.serial_number" type="text" class="uk-input uk-border-rounded" placeholder="Numero de serie du materiel">
                </div>
                <div class="uk-width-1-2@m">
                    <label for="">Confirmez votre mot de passe</label>
                    <input v-model="formData.password_confirmation" type="password" class="uk-border-rounded uk-input" placeholder="Entrez votre mot de passe">
                </div>
                <div>
                    <button class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                </div>
            </form>
        </div>


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
            this.getData()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                formData : {
                    _token : "",
                    depot : "",
                    vendeur : "",
                    serial_number : "",
                    password_confirmation : ""
                },
                errors : [],
                users : [],
                depots : []
            }
        },
        methods : {
            getData : async function () {
                try {
                    this.isLoading = true
                    Object.assign(this.$data,this.$options.data())

                    let response = await axios.get('/admin/all-vendeurs')
                    let theResponse = await axios.get('/admin/inventory/depot')

                    if(response && theResponse) {
                        this.users = response.data
                        this.depots = theResponse.data

                        this.isLoading = false
                    }
                }
                catch(error){
                    alert(error)
                }
            },
            sendTransfert : async function () {
                try {
                    this.isLoading = true
                    this.formData._token = this.myToken
                    let response = await axios.post('/admin/material/affectation/depot-vendeur',this.formData)
                    if(response && response.data == 'done') {

                        alert("Success !")
                        this.getData()
                        this.isLoading = false      
                    }
                }
                catch(error) {
                    this.isLoading = false
                    if(error.response.data.errors) {
                        let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                        this.errors.push(errorTab[prop][0])
                        }
                    } else {
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
