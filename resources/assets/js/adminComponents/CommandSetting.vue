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

        <div class="uk-grid-small uk-grid-divider" uk-grid>
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
            <div class="uk-width-1-2@m">
                <ul uk-accordion="collapsible: true">
                    <li v-for="(a,index) in articles" :key="index">
                        <a class="uk-accordion-title" href="#">{{ a.name }}</a>
                        <div class="uk-accordion-content" uk-grid>
                            <div v-for="(item,index) in a.articles" :key="index">
                                <div class="uk-width-1-1@m">
                                    <label for="">{{ item.libelle }}</label>
                                    <span uk-icon="icon : check"></span>
                                    <!-- <input v-model="form.materiels" checked :id="item.produit" type="checkbox" :value="item.produit" class="uk-border uk-checkbox">     -->
                                </div>
                            </div>
                            <div class="uk-width-1-1@m">
                                <button @click="onEditArticleName(a.slug)" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Editer</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
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
        beforeMount() {
            UIkit.offcanvas($("#side-nav")).hide();
            this.getData()
            this.onInit()
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
                errors : [],
                articles : [],
                form : {
                    _token : "",
                    materiels : []
                }
            }
        },
        methods : {
            onInit : async function () {
                try {
                    let response = await axios.get('/admin/kits')
                    if(response)
                    {
                        this.articles = response.data
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
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
            },
            onEditArticleName : async function (slug)
            {
                try
                {
                    var newName = prompt("Entrez le text ici : ")
                    if(newName == "")
                    {
                        alert("Veuillez remplir le champ")
                        return 0
                    }

                    this.isLoading = true
                    let response = await axios.post('/admin/commandes/edit-kits',{
                        _token : this.myToken,
                        slug : slug,
                        new_name : newName
                    })
                    if(response && response.data == 'done')
                    {
                        alert('Success.')
                        this.isLoading = false
                        this.getData()
                        this.onInit()
                    }
                }
                catch(error)
                {
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
