<template>
    <div>
         <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>
        
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
                        <div v-for="m in materials"  class="">
                            <div class="uk-card uk-border-rounded uk-box-shadow-hover-small uk-background-muted uk-dark uk-card-body uk-padding-small">
                            <h3 class="uk-card-title">{{m.libelle}}</h3>
                            <p>
                                <ul class="uk-list uk-list-divider">
                                <li>
                                    <span class="uk-card-title">Prix : {{m.prix_vente | numFormat}}</span> ,
                                </li>
                                <li>
                                    <span class="uk-card-title">Qte : {{m.quantite_centrale}}</span>
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
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

export default {
    created() {
        this.isLoading = true
    },
    mounted() {
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
            materials : []
        }
    },
    methods : {
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
        }
    }
}
</script>
