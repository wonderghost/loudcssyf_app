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
            <li><span>Reseaux Afrocash</span></li>
            <li><span>Affectation Pdraf -> Pdc</span></li>
        </ul>

        <h3 class="uk-margin-top">Affectation Pdraf -> Pdc</h3>
        <hr class="uk-divider-small">

        <!-- Erreor block -->
        <template v-if="errors.length">
            <div v-for="(error,index) in errors" :key="index" class="uk-alert-danger uk-width-1-2@m uk-border-rounded uk-box-shadow-hover-small" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
            </div>
        </template>

        <div class="uk-width-1-2@m">
            <form @submit.prevent="sendRequest()" class="uk-grid-small" uk-grid>
                <div class="uk-width-1-2@m">
                    <label for="">Pdraf</label>
                    <vue-single-select
                        v-model="pdraf_search"
                        :options="pdraf_list"
                        :max-results="500"
                        :required="true">
                    </vue-single-select>                    
                </div>
                <div class="uk-width-1-2@m">
                    <label for="">Pdc Origine</label>
                    <span v-if="getPdc" class="uk-input uk-border-rounded">{{getPdc.pdc.localisation}}</span>
                </div>
                <div class="uk-width-1-2@m">
                    <label for="">Pdc destination</label>
                    <select v-model="dataForm.pdc_destination" class="uk-select uk-border-rounded">
                        <option value="">-- Selectionnez un pdc --</option>
                        <option v-for="p in allPdc" :key="p.username" :value="p.username">{{p.localisation}}</option>
                    </select>
                </div>
                <div class="uk-width-1-2@m">
                    <label for="">Mot de passe</label>
                    <input v-model="dataForm.password" type="password" class="uk-border-rounded uk-input">
                </div>
                <div>
                    <button type="submit" class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
                </div>
            </form>
        </div>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import VueSingleSelect from "vue-single-select";

    export default {
        components : {
            Loading,
            'vue-single-select' : VueSingleSelect
        },
        mounted() {
            this.getData()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                pdraf_list : [],
                allPdraf : [],
                allPdc : [],
                pdraf_search : "",
                dataForm : {
                    _token : "",
                    pdraf_id : "",
                    pdc_origine : "",
                    pdc_destination : "",
                    password : ""
                },
                errors : []
            }
        },
        methods : {
            getData : async function () {
                try {
                    Object.assign(this.$data,this.$options.data())
                    let response = await axios.get('/admin/pdraf/all')

                    if(response) {
                        this.allPdc = response.data.pdc_list
                        this.allPdraf = response.data.pdraf_list
                        response.data.pdraf_list.forEach(p => {
                            this.pdraf_list.push(p.localisation)
                        })
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            sendRequest : async function () {
                try {
                    this.isLoading = true

                    this.dataForm._token = this.myToken
                    this.dataForm.pdc_origine = this.getPdc.pdc.username
                    this.dataForm.pdraf_id = this.getUsername

                    let response = await axios.post('/admin/pdraf/affectation/send-request',this.dataForm)
                    if(response && response.data == 'done') {
                        alert('Success !')
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
            },
            getUsername() {
                return this.allPdraf.filter((p) => {
                    return p.localisation == this.pdraf_search
                })[0].username
            },
            getPdc() {
                return this.allPdraf.filter((p) =>  {
                    return p.localisation  == this.pdraf_search
                })[0]
            }
        }
    }
</script>
