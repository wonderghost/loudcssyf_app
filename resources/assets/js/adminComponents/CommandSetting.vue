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
            <li><span>Commandes</span></li>
            <li><span>Parametres Commande</span></li>
        </ul>

        <h3>Parametres commande materiel</h3>
        <hr class="uk-divider-small">

        <div class="uk-width-1-2@m">
            <template v-if="errors">
                <div v-for="(error,index) in errors" class="uk-width-1-1@m uk-border-rounded uk-alert-danger" uk-alert :key="index">
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>
                        {{error}}
                    </p>
                </div>
            </template>
            <form @submit.prevent="saveKitRequest()" class="uk-grid-small" uk-grid>
                <div class="uk-width-1-2@m">
                    <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-1@m">
                            <label for="">Intitule</label>
                            <input v-model="dataForm.intitule" type="text" class="uk-input uk-border-rounded" placeholder="ex : Kit materiel">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Description</label>
                            <textarea v-model="dataForm.description" class="uk-textarea uk-border-rounded" placeholder="Decrivez le materiel"></textarea>
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Confirmez votre mot de passe</label>
                            <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe">
                        </div>
                    </div>
                </div>
                <div class="uk-width-1-2@m">
                    <div class="uk-grid-small uk-margin-top" uk-grid>
                        <div v-for="(m,index) in materials" :key="index" class="uk-width-1-2@m">
                            <label for="">{{m.libelle}}</label>
                            <input v-model="dataForm.materiels" type="checkbox" :id="m.reference" :value="m.reference" class="uk-border uk-checkbox">    
                        </div>
                    </div>
                </div>
                <div class="uk-width-1-1@m">
                    <button class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
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
                materials : [],
                dataForm : {
                    _token : "",
                    intitule : "",
                    description : "",
                    password_confirmation : "",
                    materiels : []
                },
                errors : []
            }
        },
        methods : {
            saveKitRequest : async function () {
                try {
                    this.isLoading = true
                    this.dataForm._token = this.myToken
                    let response = await axios.post('/admin/commandes/save-kits',this.dataForm)
                    if(response && response.data == 'done') {
                        alert("Success!")
                        Object.assign(this.$data,this.$options.data())
                        this.getData()
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
            },
            getData : async function () {
                try {   
                    let response = await axios.get('/admin/entrepot/all')
                    if(response) {
                        this.materials = response.data
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
