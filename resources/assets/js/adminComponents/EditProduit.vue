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
            <li><a @click="$router.go(-1)">Entrepot</a></li>
            <li><span>Editer un produit</span></li>
        </ul>

        <h3>Editer un produit</h3>
        <hr class="uk-divider-small">

        <div class="uk-width-1-2@m">
            <div v-if="errors">
                <div class="uk-width-1-1@m  uk-alert-danger" v-for="(err,index) in errors" :key="index" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p>{{ err }}</p>
                </div>
            </div>
            <form @submit.prevent="sendEditForm()" class="uk-width-1-1@m uk-grid-small" uk-grid>
                <div class="uk-width-1-3@m">
                    <label for="">Libelle</label>
                    <input type="text" class="uk-input uk-border-rounded" v-model="editMaterialForm.libelle" placeholder="Libelle du Materiel">
                </div>
                <div class="uk-width-1-3@m">
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
                    <label for="">Marge Pdc</label>
                    <input type="number" class="uk-input uk-border-rounded" v-model="editMaterialForm.marge_pdc" placeholder="Marge Materiel">
                </div>
                <div class="uk-width-1-3@m">
                    <label for="">Marge Pdraf</label>
                    <input type="number" class="uk-input uk-border-rounded" v-model="editMaterialForm.marge_pdraf" placeholder="Marge Materiel">
                </div>
                <div class="uk-width-1-3@m">
                    <label for="">Quantite</label>
                    <span type="text" class="uk-input uk-border-rounded">{{ editMaterialForm.quantite }}</span>
                </div>
                <div class="uk-width-1-3@m">
                    <label for="">Interval Debut</label>
                    <input v-model="editMaterialForm.interval_serial_first" type="number" class="uk-input uk-border-rounded" placeholder="[xxx,">
                </div>
                <div class="uk-width-1-3@m">
                    <label for="">Interval Fin</label>
                    <input v-model="editMaterialForm.interval_serial_last" type="number" class="uk-input uk-border-rounded" placeholder="yyy]">
                </div>
                
                <div class="uk-width-1-2@m">
                    <label for="">Confirmez votre mot de passe</label>
                    <input type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe pour confirmer" v-model="editMaterialForm.password_confirmation">
                </div>
                <div class="uk-width-1-1@m">
                    <button class="uk-button-small uk-button uk-border-rounded uk-button-primary">Envoyez</button>
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
        this.getData()
    },
    data() {
        return {
            isLoading : false,
            fullPage : true,
            editMaterialForm : {
                _token : "",
                reference : "",
                libelle : "",
                prix_initial : 0,
                prix_unitaire : 0,
                marge : 0,
                marge_pdc : 0,
                marge_pdraf : 0,
                quantite : 0,
                interval_serial_first : 0,
                interval_serial_last : 0,
                password_confirmation : ""
            },
            errors : []
        }
    },
    methods : {
       getData : async function () {
           try {
               this.isLoading = true

               let response = await axios.get('/admin/material/edit/'+this.$route.params.id)
               if(response) {
                    this.editMaterialForm.reference = response.data.reference
                    this.editMaterialForm.libelle = response.data.libelle
                    this.editMaterialForm.prix_initial = response.data.prix_initial
                    this.editMaterialForm.prix_unitaire = response.data.prix_vente
                    this.editMaterialForm.marge = response.data.marge
                    this.editMaterialForm.quantite = response.data.quantite_centrale
                    this.editMaterialForm.marge_pdc = response.data.marge_pdc
                    this.editMaterialForm.marge_pdraf = response.data.marge_pdraf
                    this.editMaterialForm.interval_serial_first = response.data.intervals ? response.data.intervals.interval_serial_first : null
                    this.editMaterialForm.interval_serial_last = response.data.intervals ? response.data.intervals.interval_serial_last : null

                    this.isLoading = false
               }
               
           }
           catch(error) {
               alert(error)
           }
       },
       sendEditForm : async function () {
            try {
                this.isLoading = true
                this.editMaterialForm._token = this.myToken
                var theErrors = []

                axios.post('/admin/edit-material',this.editMaterialForm)
                    .then((response) => {
                        if(response.data == 'done') {
                            alert("Success !")
                            this.getData()
                        }
                    } , (error) => {
                            this.isLoading = false
                            if(error.response.data.errors) {
                                let errorTab = error.response.data.errors
                                for (var prop in errorTab) {
                                    theErrors.push(errorTab[prop][0])
                                }
                            } else {
                                theErrors.push(error.response.data)
                            }
                    })

                    this.errors = theErrors
                
            } catch(error) {
                this.isLoading = false
                if(error.response.data.errors) {
                    let errorTab = error.response.data.errors
                    for (var prop in errorTab) {
                        this.errors.push(errorTab[prop][0])
                        // alert(errorTab[prop][0])
                    }
                } else {
                    this.errors.push(error.response.data)
                    // alert(error.response.data)

                }
            }
        },
    }
}
</script>
