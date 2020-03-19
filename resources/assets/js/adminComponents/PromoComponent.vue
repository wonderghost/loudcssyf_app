<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>
        <!-- MODAL PROMO -->
	<div id="modal-promo" class="uk-modal-container" uk-modal>
        <div class="uk-modal-dialog  uk-margin-auto-vertical">
            <div class="uk-modal-header">
                <h3 class="uk-modal-title">Parametre Promo</h3>
            </div>
            <div class="uk-modal-body">
                <div>
                    <!-- Erreor block -->
                      <template v-if="errors.length" v-for="error in errors">
                      <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                        <a href="#" class="uk-alert-close" uk-close></a>
                        <p class="uk-text-center"><span uk-icon="icon : warning"></span> {{error}}</p>
                      </div>
                    </template>

                    <template v-if="!promoStatus">
                        <h4 v-if="formState == 'add'">Nouvel Promo</h4>
                        <h4 v-else>Editer</h4>
                        <hr class="uk-divider-small">
                        <form @submit.prevent="addPromo()" class="uk-grid-small" uk-grid>
                            <div class="uk-width-1-2@m">
                                <label for=""><span uk-icon="icon : calendar"></span> Debut de la promo</label>
                                <input type="date" class="uk-input uk-border-rounded" v-model="formData.debut">
                            </div>
                            <div class="uk-width-1-2@m">
                                <label for=""><span uk-icon="icon : calendar"></span> Fin de la promo</label>
                                <input type="date" class="uk-input uk-border-rounded" v-model="formData.fin">
                            </div>
                            <div class="uk-width-1-2@m">
                                <label for=""><span uk-icon="icon : pencil"></span> Intitutle Promo</label>
                                <input type="text" class="uk-input uk-border-rounded" v-model="formData.intitule">
                            </div>
                            <div class="uk-width-1-2@m">
                                <label for=""><span uk-icon="icon : credit-card"></span> Subvention</label>
                                <input type="number" class="uk-input uk-border-rounded" v-model="formData.subvention">
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for=""><span uk-icon="icon : comment"></span> Description</label>
                                <textarea class="uk-textarea uk-border-rounded" v-model="formData.description" cols="30" rows="10"></textarea>
                                <div class="uk-margin-small">
                                    <button v-if="formState == 'edit'" @click="abortEdit()" type="button" class="uk-border-rounded uk-button uk-button-small uk-button-danger"><span uk-icon="icon : close"></span> Annuler</button>
                                    <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                                </div>
                            </div>
                        </form>
                    </template>
                    <template v-else>
                        <h4>Promo en cours</h4>
                        <hr class="uk-divider-small">
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-1-4@m">
                                <label for="">Du</label>
                                <span class="uk-input uk-border-rounded">{{ activePromo.debut}} </span>
                            </div>
                            <div class="uk-width-1-4@m">
                                <label for="">Au</label>
                                <span class="uk-input uk-border-rounded">{{activePromo.fin}} </span>
                            </div>
                            <div class="uk-width-1-4@m">
                                <label for="">Intitule de la promo</label>
                                <span class="uk-input uk-border-rounded">{{activePromo.intitule}} </span>
                            </div>
                            <div class="uk-width-1-4@m">
                                <label for="">Subvention</label>
                                <span class="uk-input uk-border-rounded">{{activePromo.subvention | numFormat}} </span>
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Description</label>
                                <span class="uk-textarea uk-border-rounded">{{activePromo.description}} </span>
                            </div>
                            <div class="uk-width-1-1@m">
                                <button @click="editPromo()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded"><span uk-icon="icon : pencil"></span> Edit</button>
                                <button @click="abortPromo()" type="button" class="uk-button uk-button-small uk-button-danger uk-border-rounded"><span uk-icon="icon : ban"></span> Interrompre</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

	<!-- // -->
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
        this.getInfosPromo()
    },
    data() {
        return {
            formData : {
                _token : "",
                intitule : "",
                description : "",
                debut : "",
                fin : "",
                subvention : 0,
                id_promo : ""
            },
            promoStatus : false,
            isLoading : false,
            fullPage : true,
            errors : [],
            activePromo : {},
            formState : ""
        }
    },
    methods : {
        abortPromo : async function () {
            // ENVOI DE LA REQUETE D'INTERRUPTION DE LA PROMO EN COURS 
            try {
                this.isLoading = true
                this.formData.id_promo = this.activePromo.id
                this.formData._token = this.myToken
                let response = await axios.post('/admin/promo/interrompre',this.formData)
                if(response.data == 'done') {
                    this.isLoading = false
                    UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Promo Interrompue !</div>")
                        .then(function () {
                            location.reload()
                        })
                }
            } catch(error) {
                alert(error)
            }
        },
        abortEdit : function () {
            this.promoStatus = true
        },
        editPromo : function () {
            try {
                this.promoStatus = false
                this.formData._token = this.myToken
                this.formData.intitule = this.activePromo.intitule
                this.formData.description = this.activePromo.description
                this.formData.subvention = this.activePromo.subvention
                this.formData.debut = this.activePromo.debut
                this.formData.fin = this.activePromo.fin
                this.formData.id_promo = this.activePromo.id
            } catch(error) {
                alert(error)
            }
        },
        addPromo : async function() {
            this.isLoading = true
            UIkit.modal($("#modal-promo")).hide()
            try {
                this.formData._token = this.myToken
                if(this.formState == 'add') {
                    var response = await axios.post('/admin/promo/add',this.formData)
                    if(response.data == 'done') {
                    UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Promo ajoute avec success !</div>")
                        .then(function () {
                            location.reload()
                        })
                    }
                } else if(this.formState == 'edit') {
                    var response = await axios.post('/admin/promo/edit',this.formData)
                    if(response.data == 'done') {
                        UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Promo Modifie !</div>")
                            .then(function() {
                                location.reload()
                            })
                    }
                }
            } catch(error) {
                this.isLoading = false
                UIkit.modal($("#modal-promo")).show()
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
        getInfosPromo : async function() {
            try {
                let response = await axios.get('/admin/promo/list')
                if(response.data == 'fail') {
                    // la promo n'existe pas
                    this.promoStatus = false
                    this.formState = 'add'
                } else {
                    // la promo existe
                    this.promoStatus = true
                    this.activePromo = response.data
                    this.formState = 'edit'
                }
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
