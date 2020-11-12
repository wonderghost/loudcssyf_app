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
            <li><router-link to="/setting/formule">Formule/Options</router-link></li>
            <li><span>Editer une formule / option</span></li>
        </ul>

        <template v-if="type == 'formule'">
            <h4>Editer Une formule</h4>
            <hr class="uk-divider-small">
            <div class="uk-width-1-3@m">
                <!-- EDITION DES INFOS DES FORMULES -->
                <form @submit.prevent="sendEditRequest()" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-1@m">
                        <label for="">Nom de la formule</label>
                        <input v-model="dataFormEdit.formule.title" type="text" class="uk-input uk-border-rounded" placeholder="Nom de la formule">
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Prix</label>
                        <input v-model="dataFormEdit.formule.prix" type="number" class="uk-input uk-border-rounded" placeholder="Prix de la formule">
                    </div>
                    <div class="uk-width-1-1@s uk-width-1-3@m">
                        <button class="uk-button uk-border-rounded uk-button-small uk-button-primary">Envoyez</button>
                    </div>
                </form>
                <!-- // -->
            </div>
        </template>
        <template v-else-if="type == 'option'">
            <h4>Editer une option</h4>
            <hr class="uk-divider-small">
            <div class="uk-width-1-3@m">
                <!-- EDITION DES INFOS DES OPTIONS -->
                <form @submit.prevent="sendEditRequest()" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-1@m">
                        <label for="">Nom de l'option</label>
                        <input v-model="dataFormEdit.option.title" type="text" class="uk-input uk-border-rounded"  placeholder="Entrez le nom de l'option">
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Prix</label>
                        <input v-model="dataFormEdit.option.prix" type="number" class="uk-input uk-border-rounded" placeholder="Entrez le prix de l'option">
                    </div>
                    <div class="uk-width-1-1@s uk-width-1-3@m">
                        <button class="uk-button-small uk-button uk-button-primary uk-border-rounded">
                            Envoyez
                        </button>
                    </div>
                </form>
                <!-- // -->
            </div>
        </template>
        <template v-else>

        </template>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
    export default {
        components : {
            Loading
        },
        props : {
            type : String
        },
        mounted() {
            this.getData()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                dataFormEdit : {
                    formule : {},
                    option : {}
                }
            }
        },
        methods : {
            getData : async function () {
                try {

                    let response 
                    
                    if(this.type == 'formule') {
                        let response = await axios.get('/admin/formule/'+this.$route.params.id+'/edit')
                        if(response) {
                            this.dataFormEdit.formule =  response.data
                        }
                    }
                    else if(this.type == 'option') {
                        let response = await axios.get('/admin/option/'+this.$route.params.id+'/edit')
                        if(response) {
                            this.dataFormEdit.option = response.data
                        }
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            sendEditRequest : async function () {
                try {
                    this.isLoading = true
                    this.dataFormEdit.formule._token = this.myToken
                    this.dataFormEdit.option._token = this.myToken
                    
                    var response 

                    if(this.type == 'formule') {
                        response = await axios.post('/admin/formule/'+this.$route.params.id+'/edit',this.dataFormEdit.formule)
                    }
                    else if(this.type == 'option') {
                        response = await axios.post('/admin/option/'+this.$route.params.id+'/edit',this.dataFormEdit.option)
                    }

                    if(response && response.data == 'done') {
                        
                        this.isLoading = false
                        alert('success')
                        this.$router.push('/setting/formule')
                    }
                }
                catch(error) {
                    console.log(error)
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
