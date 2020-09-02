<template>
    <div class="uk-container uk-container-large">
         <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <h3>Entrepot</h3>
        <hr class="uk-divider-sma">

        <template v-if="typeUser == 'admin'">
            <!-- Erreor block -->
            <template v-if="errors.length" v-for="error in errors">
                <div class="uk-alert-danger uk-width-1-2@m uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{error}}</p>
                </div>
            </template>
            
            <ul class="uk-subnav uk-subnav-pill" uk-switcher>
                <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Nouveau Material</a></li>
                <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Nouvau Depot</a></li>
                <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Inventaire</a></li>
            </ul>
            
            <ul class="uk-switcher">
                <li>
                    <!-- NOUVEAU MATERIAL -->
                    <form @submit.prevent="addMaterial()" class="uk-grid-small uk-width-1-2@m" uk-grid>
                        <div class="uk-width-1-2@m">
                            <label for="">Libelle</label>
                            <input @blur="findMaterial()" type="text" class="uk-input uk-border-rounded" v-model="newMaterialForm.libelle">
                        </div>
                        <div class="uk-width-1-2@m">
                        <label for="">Prix Initial</label> 
                        <input type="text" class="uk-input uk-border-rounded" v-model="newMaterialForm.prix_initial">
                        </div>  
                        <div class="uk-width-1-3@m">
                            <label for="">Prix Unitaire</label>
                            <input type="text" class="uk-input uk-border-rounded" v-model="newMaterialForm.prix_unitaire">
                        </div>
                        <div class="uk-width-1-3@m">
                            <label for="">Marge</label>
                            <input type="text" class="uk-input uk-border-rounded" v-model="newMaterialForm.marge">
                        </div>
                        <div class="uk-width-1-3@m">
                            <label for="">Quantite</label>
                            <input type="number" class="uk-input uk-border-rounded" v-model="newMaterialForm.quantite">
                        </div>
                    
                        <div class="uk-width-1-1@m">
                            <label>
                                Avec S/N
                                <input type="checkbox" class="uk-checkbox" v-model="newMaterialForm.with_serial">
                            </label>
                        </div>
                        <div>
                            <button type="submit" class="uk-button uk-button-primary uk-button-small uk-border-rounded">validez</button>
                        </div>
                    </form>
                    <!-- // -->
                </li>
                <li></li>
                <li>
                    <!-- ENTREPOT -->
                    <div class="uk-child-width-1-4@m uk-grid-small" uk-grid>
                        <template>
                        <!-- INVENTAIRE DES MATERIELS -->
                            <div v-for="m in materials" :key="m.reference"  class="">
                                <div class="uk-card uk-border-rounded uk-box-shadow-hover-small uk-background-muted uk-dark uk-card-body uk-padding-small">
                                    <h3 class="uk-card-title">{{m.libelle}}</h3>
                                    <p>
                                        <ul class="uk-list uk-list-divider">
                                            <li>
                                                <span class="">Prix : {{m.prix_vente | numFormat}}</span>
                                            </li>
                                            <li>
                                                <span class="">Qte : {{m.quantite_centrale}}</span>
                                            </li>
                                            <li>
                                                <span class="">Marge : {{m.marge | numFormat}}</span>
                                            </li>
                                            <li>
                                                <button @click="editMaterialFunction(m)" uk-toggle="target : #modal-edit-material" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-hover-small">Editer <span uk-icon="icon : pencil"></span></button>
                                            </li>
                                        </ul>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                    <!-- // -->
                </li>
            </ul>        

            <!-- EDIT INFOS MATERIALS -->
            <div id="modal-edit-material" uk-modal>
                <div class="uk-modal-dialog">
                    <!-- <button class="uk-modal-close-default" type="button" uk-close></button> -->
                    <div class="uk-modal-header">
                        <h2 class="uk-modal-title">Editer Infos Materiels</h2>
                    </div>
                    <div class="uk-modal-body">
                        <form @submit.prevent="sendEditForm()" class="uk-width-1-1@m uk-grid-small" uk-grid>
                            <div class="uk-width-1-2@m">
                                <label for="">Libelle</label>
                                <input type="text" class="uk-input uk-border-rounded" v-model="editMaterialForm.libelle" placeholder="Libelle du Materiel">
                            </div>
                            <div class="uk-width-1-2@m">
                                <label for="">Prix initial</label>
                                <input type="number" class="uk-input uk-border-rounded" v-model="editMaterialForm.prix_initial" placeholder="Prix Initial">
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for="">Prix Unitaire</label>
                                <input type="number" class="uk-input uk-border-rounded" v-model="editMaterialForm.prix_unitaire" placeholder="Prix Unitaire">
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for="">Marge</label>
                                <input type="number" class="uk-input uk-border-rounded" v-model="editMaterialForm.marge" placeholder="Marge Materiel">
                            </div>
                            <div class="uk-width-1-3@m">
                                <label for="">Quantite</label>
                                <span type="text" class="uk-input uk-border-rounded">{{ editMaterialForm.quantite }}</span>
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Confirmez votre mot de passe</label>
                                <input type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe pour confirmer" v-model="editMaterialForm.password_confirmation">
                            </div>
                            <div>
                                <button class="uk-button-small uk-button uk-border-rounded uk-button-primary">Envoyez</button>
                            </div>
                        </form>
                    </div>
                    <div class="uk-modal-footer uk-text-right">
                        <button class="uk-button uk-button-danger uk-button-small uk-border-rounded uk-modal-close" type="button">Fermer</button>
                    </div>
                </div>
            </div>
            <!-- // -->
        </template>
        <template v-else>
            <div class="uk-alert-warning" uk-alert>
                <p class="uk-text-center">
                    <span uk-icon="icon : warning"></span> Vous n'etes pas autorise a effectuer cette , contactez l'administrateur.
                </p>
            </div>
        </template>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

export default {
    mounted() {
        UIkit.offcanvas($("#side-nav")).hide();
        this.etatEntrepot()
    },
    components : {
        Loading
    },
    data() {
        return {
            newMaterialForm : {
                _token : "",
                libelle : "",
                prix_initial : 0,
                prix_unitaire : 0,
                marge : 0,
                number : 0,
                with_serial : true,
                quantite : 0
            },

            isLoading : false,
            fullPage : true,
            errors : [],
            materials : [],

            editMaterialForm : {
                _token : "",
                reference : "",
                libelle : "",
                prix_initial : 0,
                prix_unitaire : 0,
                marge : 0,
                quantite : 0,
                password_confirmation : ""
            }
        }
    },
    methods : {
        editMaterialFunction : function (obj) {
            try {   
                this.editMaterialForm.reference = obj.reference
                this.editMaterialForm.libelle = obj.libelle
                this.editMaterialForm.prix_initial = obj.prix_initial
                this.editMaterialForm.prix_unitaire = obj.prix_vente
                this.editMaterialForm.marge = obj.marge
                this.editMaterialForm.quantite = obj.quantite_centrale

            } catch(error) {
                alert(error)
            }
        },
        sendEditForm : async function () {
            try {
                this.isLoading = true
                UIkit.modal($("#modal-edit-material")).hide()

                this.editMaterialForm._token = this.myToken

                axios.post('/admin/edit-material',this.editMaterialForm)
                    .then((response) => {
                        
                        if(response.data == 'done') {
                            alert("Success !")
                            // location.reload()
                            this.etatEntrepot()
                        }

                    } , (error) => {
                            this.isLoading = false
                            UIkit.modal($("#modal-edit-material")).show()
                            if(error.response.data.errors) {
                                let errorTab = error.response.data.errors
                                for (var prop in errorTab) {
                                    // this.errors.push(errorTab[prop][0])
                                    alert(errorTab[prop][0])
                                }
                            } else {
                                // this.errors.push(error.response.data)
                                alert(error.response.data)

                            }
                    })
                
            } catch(error) {
                this.isLoading = false
                UIkit.modal($("#modal-edit-material")).show()
                if(error.response.data.errors) {
                    let errorTab = error.response.data.errors
                    for (var prop in errorTab) {
                        // this.errors.push(errorTab[prop][0])
                        alert(errorTab[prop][0])
                    }
                } else {
                    // this.errors.push(error.response.data)
                    alert(error.response.data)

                }
            }
        },
        findMaterial : async function () {
            try {
                let response = await axios.post('/admin/add-depot/auto-complete',{
                    wordSearch : this.newMaterialForm.libelle
                })

                if(response.data == 'fail') {
                    throw "Aucune correspondance!"
                } 
                this.newMaterialForm.prix_initial = response.data.prix_initial
                this.newMaterialForm.prix_unitaire = response.data.prix_vente
                this.newMaterialForm.marge = response.data.marge
                this.newMaterialForm.quanite = 0

            } catch(error) {
                alert(error)
            }
        },
        addMaterial : async function () {
            try {
                this.isLoading = true
                this.newMaterialForm._token = this.myToken
                let response = await axios.post('/admin/add-material',this.newMaterialForm)
                if(response.data == 'done') {
                    UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Materiel ajoute !</div>")
                        .then(function() {
                            location.reload()
                        })
                }
            } catch(error) {
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
        etatEntrepot : async function() {
            try {
                this.isLoading = true
                let response = await axios.get('/admin/entrepot/all')
                this.materials = response.data
                this.isLoading = false
            } catch(error) {
                alert(error)
            }
        }
    },
    computed : {
        myToken() {
            return this.$store.state.myToken
        },
        materialToEdit() {
            if(this.editMaterial) {
                return this.materials.filter((m) => {
                    return m.reference.match(this.editMaterial)
                })[0]
            }
            else {
                return null
            }
        },
        typeUser() {
            return this.$store.state.typeUser
        }
    }
}
</script>
