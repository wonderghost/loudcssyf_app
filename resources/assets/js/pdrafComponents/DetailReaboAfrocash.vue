<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <!-- MODAL CONTENT -->
        <div id="modal-container" class="uk-modal-container" uk-modal>
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Details</h2>
                </div>
                <div class="uk-modal-body uk-grid-divider" uk-grid>
                    <div class="uk-width-1-2@m uk-grid-small" uk-grid>
                        <!-- INFOS CLIENTS -->
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
                    <div class="uk-width-1-2@m uk-grid-small" uk-grid>
                        <!-- INFOS MATERIELS -->
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
                <div class="uk-modal-footer uk-text-right">
                    <button  class="uk-modal-close uk-button-small uk-button uk-button-danger uk-border-rounded">Fermer</button>
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
        props : {
            venteId : String
        },
        mounted() {},
        data() {
            return {
                isLoading : false,
                fullPage : false,
                vente : {},
            }
        },
        watch : {
            'venteId' : 'getData'
        },
        methods : {
            getData : async function () {
                try {
                    if(this.venteId == "")  {
                        return 0
                    }
                    let response = await axios.get('/user/pdraf/get-vente-details/'+this.venteId)
                    if(response) {
                        this.isLoading = true
                        this.vente = response.data
                        this.isLoading = false
                    }
                }
                catch(error) {
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
            }
        },
        computed : {
            typeUser () {
                return this.$store.state.typeUser
            }
        }
    }
</script>
