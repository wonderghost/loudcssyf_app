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
            <li><span>Details</span></li>
        </ul>
        <div class="uk-card-title"><button @click="$router.go(-1)" class="uk-button-default uk-border-rounded"><i class="material-icons">arrow_back</i></button> Details</div>
        <hr class="uk-divider-small">
        
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@m">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-6@m">
                        <label for="">Titre</label>
                        <span class="uk-input uk-border-rounded">{{ vente.titre }}</span>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Nom</label>
                        <span class="uk-input uk-border-rounded">{{ vente.nom }}</span>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Prenom</label>
                        <span class="uk-input uk-border-rounded">{{ vente.prenom }}</span>
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Ville</label>
                        <span class="uk-input uk-border-rounded">{{ vente.ville }}</span>
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Adresse Postale</label>
                        <span class="uk-input uk-border-rounded">{{ vente.adresse_postal }}</span>
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Telephone</label>
                        <span class="uk-input uk-border-rounded">{{ vente.telephone_client }}</span>
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Email</label>
                        <span class="uk-input uk-border-rounded">{{ vente.email }}</span>
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Identifiant Alonwa</label>
                        <span class="uk-input uk-border-rounded">{{ vente.technicien.service_plus_id }}</span>
                    </div>
                </div>
                        
            </div>
            <div class="uk-width-1-2@m">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m">
                        <label for="">Date</label>
                        <span class="uk-input uk-border-rounded">{{ vente.date }}</span>
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for="">Materiel</label>
                        <span class="uk-input uk-border-rounded">{{ vente.serial }}</span>
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for="">Formule</label>
                        <span class="uk-input uk-border-rounded">{{ vente.formule_name }}</span>
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for="">Option</label>
                        <span class="uk-input uk-border-rounded"></span>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Duree</label>
                        <span class="uk-input uk-border-rounded">{{ vente.duree }}</span>
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Montant Ttc</label>
                        <span class="uk-input uk-border-rounded">{{ vente.montant_ttc | numFormat }}</span>
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Comission</label>
                        <span class="uk-input uk-border-rounded">{{ vente.comission | numFormat}}</span>
                    </div>
                    <div v-if="typeUser != 'pdraf'" class="uk-width-1-3@m">
                        <label for="">Marge</label>
                        <span class="uk-input uk-border-rounded">{{ vente.marge | numFormat }}</span>
                    </div>
                </div>
                        
            </div>
        </div>
        <!-- // -->
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
        data() {
            return {
                isLoading : false,
                fullPage : false,
                vente : {},
            }
        },
        methods : {
            onInit : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/user/pdraf/get-vente-details/'+this.$route.params.id)
                    if(response) {
                        this.vente = response.data
                        this.isLoading = false
                    }
                }
                catch(error) {
                    this.isLoading = false
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
                    this.$router.go(-1)
                }
            }
        },
        computed : {
            typeUser () {
                return this.$store.state.typeUser
            }
        }
    }
</script>
