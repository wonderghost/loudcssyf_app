<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <h3>Nouveau Contact</h3>
        <hr class="uk-divider-small">
        <!-- Error block -->
        <template v-if="errors.length" v-for="error in errors">
            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
                <a href="#" class="uk-alert-close" uk-close></a>
                <p>{{error}}</p>
            </div>
        </template>
        <!-- // -->
        <form @submit.prevent="addContact()" class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@m">
                <div class="uk-grid-small" uk-grid>

                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : user"></span> Nom</label>
                        <input v-model="formData.nom" type="text" class="uk-input uk-border-rounded" placeholder="Entrez le nom">
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : user"></span> Prenom</label>
                        <input v-model="formData.prenom" type="text" class="uk-input uk-border-rounded" placeholder="Entrez le prenom">
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : mail"></span> Email</label>
                        <input v-model="formData.email" type="email" class="uk-input uk-border-rounded" placeholder="Entrez l'Email">
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : phone"></span> Telephone</label>
                        <input v-model="formData.phone" type="text" class="uk-input uk-border-rounded" placeholder="Entrez le Telephone">
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : location"></span> Adresse</label>
                        <input v-model="formData.adresse" type="text" class="uk-input uk-border-rounded" placeholder="Entrez l'Adresse">
                    </div>
                </div>

            </div>
            
            <div class="uk-width-1-2@m">
                <div class="uk-grid-small" uk-grid>
                    <div v-for=" m in formData.quantite_materiel" :key="m" class="uk-width-1-3@m">
                        <label for="">Materiel-{{m}}</label>
                        <input type="text" v-model="formData.serial[m-1]" class="uk-input uk-border-rounded" :placeholder="'Materiel-'+m">
                    </div>
                </div>

            </div>
            
            <div class="uk-width-1-1@m">
                <button class="uk-button-small uk-button-primary uk-border-rounded uk-button">Envoyer</button>
                <a @click="addMateriel()" uk-tooltip="Ajouter un materiel" class="uk-button uk-button-small uk-button-default uk-border-rounded"><span uk-icon="icon : plus"></span></a>
                <a @click="removeMateriel()" uk-tooltip="Retirer le dernier element" class="uk-button uk-button-small uk-button-default uk-border-rounded"><span uk-icon="icon : minus"></span></a>
            </div>
        </form>
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
            
        },
        data() {
            return {
                formData : {
                    _token : "",
                    nom : "",
                    prenom : "",
                    email : "",
                    phone : "",
                    adresse : "",
                    quantite_materiel : 0,
                    serial : [],
                },
                errors: [],
                isLoading : false,
                fullPage : true
            }
        },
        methods : {
            addMateriel : function () {
                this.formData.quantite_materiel++
            },
            removeMateriel : function () {
                if(this.formData.quantite_materiel > 0) {
                    this.formData.quantite_materiel--
                }

                var diff = this.formData.serial.length - this.formData.quantite_materiel
            },
            addContact : async function () {
                try {
                    this.isLoading = true
                    this.formData._token = this.myToken
                    let response = await axios.post('/user/carnet-adresse/add-client',this.formData)
                    if(response.data == 'done') {
                        UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Un contact ajoute !</div>")
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
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
