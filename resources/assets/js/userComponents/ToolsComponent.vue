<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>
        <!-- PAY COMISSION BUTTON -->
        <div v-if="typeUser == 'pdraf' || typeUser == 'pdc'" class="bonus-view uk-position-fixed uk-position-z-index">
            <button class="uk-button uk-button-small uk-button-primary uk-border-rounded"  uk-tooltip="Paiement comission" uk-toggle="target : #modal-pay-comission"> 
                <span class="">
                    <i class="material-icons uk-float uk-float-left">attach_money</i> 
                    Comission
                    <!-- {{comissionAfrocash | numFormat}} GNF -->
                </span>
            </button>
        </div>
        <!-- // -->
        <!-- MODAL PAY COMISSION -->
        <template v-if="typeUser == 'pdraf' || typeUser == 'pdc'">
            <div id="modal-pay-comission" uk-modal="esc-close : false ; bg-close : false">
                <div class="uk-modal-dialog">
                    <div class="uk-modal-header">
                        <h2 class="uk-modal-title">Paiement Comission</h2>
                    </div>
                    <div class="uk-modal-body">
                        <div v-if="errors">
                            <div v-for="(err,index) in errors" :key="index">
                                <a class="uk-alert-close" uk-close></a>
                                <p>
                                    {{err}}
                                </p>
                            </div>
                        </div>
                        <form @submit.prevent="sendPayComissionRequest()" class="uk-grid-small" uk-grid>
                            <!-- Error block -->
                            <template v-if="errors">
                                <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-1@m" uk-alert v-for="(error,index) in errors" :key="index">
                                    <a href="#" class="uk-alert-close" uk-close></a>
                                    <p>{{error}}</p>
                                </div>
                            </template>
                            <p uk-alert class="uk-alert-info uk-border-rounded">
                                <span uk-icon="icon : info"></span> Vous etes sur le point de confirmer le paiement de votre Comission qui s'eleve a : <span class="uk-text-bold">{{comissionAfrocash | numFormat}} GNF</span>
                            </p>
                            <div class="uk-width-1-1@m">
                                <label for=""><span uk-icon="icon : lock"></span> Confirmez votre mot de passe </label>
                                <input type="password" placeholder="Entrez votre mot de passe" v-model="password_confirm" class="uk-input uk-border-rounded">
                            </div>
                            <div>
                                <button class="uk-button uk-button-small uk-border-rounded uk-button-primary">
                                    Envoyez
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="uk-modal-footer uk-text-right">
                        <button class="uk-button uk-button-danger uk-border-rounded uk-button-small uk-modal-close" type="button">Fermer</button>
                    </div>
                </div>
            </div>
        </template>
        <!-- // -->
        <!-- BONUS BUTTON -->
        <div v-if="typeUser == 'v_da' || typeUser == 'v_standart'" class="bonus-view uk-position-fixed uk-position-z-index" uk-toggle="target : #modal-pay-bonus">
            <button class="uk-button uk-button-small uk-button-primary uk-border-rounded"  uk-tooltip="Bonus"> 
                <img src="/img/coins.png" width="30" alt=""> 
                <span class="uk-text-bold">
                    {{bonus | numFormat}} GNF
                </span>
            </button>
        </div>
        <!-- // -->
        <!-- MODAL BONUS PAIEMENT -->
        <div id="modal-pay-bonus" uk-modal="esc-close : false ; bg-close : false">
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Paiement Bonus</h2>
                </div>
                <div class="uk-modal-body">
                    <form @submit.prevent="confirmPayBonus()" class="uk-grid-small" uk-grid>
                        <!-- Erreor block -->
                        <template v-if="errors.length" v-for="error in errors">
                            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-1@m" uk-alert>
                                <a href="#" class="uk-alert-close" uk-close></a>
                                <p>{{error}}</p>
                            </div>
                        </template>
                        <p uk-alert class="uk-alert-info uk-border-rounded">
                            <span uk-icon="icon : info"></span> Vous etes sur le point de confirmer le paiement de votre bonus qui s'eleve a : <span class="uk-text-bold">{{bonus | numFormat}} GNF</span>
                        </p>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="icon : lock"></span> Confirmez votre mot de passe </label>
                            <input type="password" placeholder="Entrez votre mot de passe" v-model="password_confirm" class="uk-input uk-border-rounded">
                        </div>
                        <div>
                            <button class="uk-button uk-button-small uk-border-rounded uk-button-primary">
                                Envoyez
                            </button>
                        </div>
                    </form>
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-danger uk-border-rounded uk-button-small uk-modal-close" type="button">Fermer</button>
                </div>
            </div>
        </div>
        <!-- // -->
        <!-- MODAL PLUS -->
        <div id="modal-plus" class="uk-modal-container" uk-modal="esc-close : false ; bg-close : false">
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Plus</h2>
                </div>
                <div class="uk-modal-body" uk-overflow>
                    <!-- Erreor block -->
                    <template v-if="errors.length" v-for="error in errors">
                        <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                            <a href="#" class="uk-alert-close" uk-close></a>
                            <p>{{error}}</p>
                        </div>
                    </template>

                    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation : uk-animation-slide-bottom">
                        <li><a class="uk-button uk-button-small uk-border-rounded" href="#"><span uk-icon="icon : unlock"></span> Deblocage Compte Cga</a></li>
                        <li><a class="uk-button uk-button-small uk-border-rounded" href="#"><span uk-icon="icon : history"></span> Annulation de Saisie</a></li>
                    </ul>

                    <ul class="uk-switcher uk-margin">
                        <li>
                            <!-- DEBLOCAGE COMPTE -->
                            <div class="uk-alert-info uk-width-1-1@m uk-border-rounded" uk-alert>
                                <p class="uk-text-center"><span uk-icon="icon : info"></span> Envoyez une demande de deblocage de compte Cga</p>
                            </div>
                            <template v-if="theUser == 'v_da' || theUser == 'v_standart' || theUser == 'admin' || theUser == 'logistique'">
                                <form class="uk-grid-small" @submit.prevent="sendDeblocageForm()" uk-grid>   
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : check"></span> Numero Distributeur</label>
                                        <span class="uk-input uk-border-rounded uk-text-bold uk-text-center" v-if="theUser == 'v_da'">{{deblocageForm.num_dist}}</span>
                                        <input type="text" class="uk-input uk-border-rounded" v-model="deblocageForm.num_dist" v-else>
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : user"></span>Compte Utilisateur</label>
                                        <input type="text" class="uk-input uk-border-rounded" v-model="deblocageForm.compte_user">
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : info"></span> Nom et Prenom</label>
                                        <input type="text" class="uk-input uk-border-rounded" v-model="deblocageForm.nom_prenom">
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : lock"></span> Confirmez le mot de passe</label>
                                        <input type="password" class="uk-input uk-border-rounded" v-model="deblocageForm.password">
                                    </div>
                                    
                                    <div class="">
                                        <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                                    </div>
                                </form>
                            </template>
                        </li>
                        <li>    
                            <!-- ERREUR DE SAISIE -->
                            <div class="uk-alert-info uk-width-1-1@m" uk-alert>
                                <p class="uk-text-center"><span uk-icon="icon : info"></span> Envoyez une demande d'annulation de Saisie</p>
                            </div>
                            <template v-if="theUser == 'v_da' || theUser == 'v_standart' || theUser == 'admin'">
                                <form @submit.prevent="sendAnnuleSaisi()" class="uk-grid-small" uk-grid>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : check"></span> Numero Distributeur</label>
                                        <span v-if="theUser == 'v_da'" class="uk-input uk-border-rounded uk-text-bold uk-text-center">{{annuleSaisiForm.num_dist}}</span>
                                        <input type="text" class="uk-input uk-border-rounded" v-else v-model="annuleSaisiForm.num_dist">
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : user"></span> Numero Abonn&eacute;</label>
                                        <input type="text" class="uk-input uk-border-rounded" v-model="annuleSaisiForm.num_abonne">
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : pencil"></span> Saisie &eacute;rrone&eacute;</label>
                                        <input type="text" v-model="annuleSaisiForm.saisie_errone" class="uk-input uk-border-rounded">        
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : pencil"></span> Saisie correcte</label>
                                        <input type="text" v-model="annuleSaisiForm.saisie_correcte" class="uk-input uk-border-rounded">        
                                    </div>
                                    <div class="uk-margin uk-width-1-2@m uk-grid-small uk-child-width-auto uk-grid">
                                        <div>MODIFICATION</div>
                                        <label><input class="uk-radio" value="OUI" name="modification_state" v-model="annuleSaisiForm.modification_state" type="radio"> OUI</label>
                                        <label><input class="uk-radio" value="NON" name="modification_state" v-model="annuleSaisiForm.modification_state" type="radio"> NON</label>
                                    </div>
                                    <div class="uk-margin uk-width-1-2@m uk-grid-small uk-child-width-auto uk-grid">
                                        <div>ANNULATION</div>
                                        <label><input class="uk-radio" value="OUI" name="annulation_state" v-model="annuleSaisiForm.annulation_state" type="radio"> OUI</label>
                                        <label><input class="uk-radio" value="NON" name="annulation_state" v-model="annuleSaisiForm.annulation_state" type="radio"> NON</label>
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : calendar"></span> Date de saisie</label>
                                        <input type="date" v-model="annuleSaisiForm.date_saisie" class="uk-input uk-border-rounded">
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for=""><span uk-icon="icon : lock"></span> Confirmez votre mote de passe</label>
                                        <input type="password" v-model="annuleSaisiForm.password" class="uk-input uk-border-rounded">
                                    </div>
                                    <div class="uk-width-1-1@m">
                                        <label for=""><span uk-icon="icon : comment"></span> Commentaire</label>
                                        <VueTrix v-model="annuleSaisiForm.comment" placeholder="Contenu"/>
                                    </div>
                                    <div>
                                        <button class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                                    </div>
                                </form>
                            </template>
                        </li>
                    </ul>
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-modal-close">Fermer</button>
                </div>
            </div>
        </div>        
        <!-- // -->
        <!-- RIGHT ACCESSOIRES -->
        <radial-menu
        :itemSize="20"
        :radius="85"
        :angle-restriction="100">
        <radial-menu-item 
          v-for="(item, index) in items" 
          :key="index">
          <button :uk-toggle="'target: #'+modals[index]" class="more-tools uk-padding-remove uk-button-primary uk-button-small" :uk-tooltip="tooltips[index]">
              <i class="material-icons">{{item}}</i>
          </button>
        </radial-menu-item>
      </radial-menu>
        <!-- // -->
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import { Facebook } from 'vue-socialmedia-share';
import { WhatsApp } from 'vue-socialmedia-share';
import { RadialMenu,  RadialMenuItem } from 'vue-radial-menu'
import VueTrix from "vue-trix";


export default {
    components : {
        Facebook,
        WhatsApp,
        RadialMenu,
        RadialMenuItem,
        VueTrix,
        Loading
    },
    mounted() {
        this.getInfosForm()
        this.getBonusObjectif()
        this.getAfrocashComission()
    },
    props : {
        theUser : String
    },
    data(){
        return {
            items: ['chat','feedback','share','more'],
            tooltips : ['Chat','Boit a suggestion','Reseaux Sociaux','Plus'],
            modals : ['','modal-feedback','','modal-plus'],
            deblocageForm : {
                num_dist : "",
                _token : "",
                compte_user : "",
                nom_prenom : "",
                password : "",
                comment : ""
            },
            annuleSaisiForm : {
                num_dist : "",
                _token : "",
                num_abonne : "",
                saisie_errone : "",
                saisie_correcte : "",
                date_saisie : "",
                password : "",
                comment : "",
                modification_state : "NON",
                annulation_state : "OUI"
            },
            errors : [],
            isLoading : false,
            fullPage : true,
            bonus : 0 ,// marge arriere 
            password_confirm : "",
            comissionAfrocash : 0,
        }
    },
    methods : {
        // CONFIRMATION PAIEMENT COMISSION AFROCASH
        sendPayComissionRequest : async function () {
            this.errors = []
            try {
                UIkit.modal($("#modal-pay-comission")).hide()
                this.isLoading = true

                var url = ""

                if(this.typeUser == 'pdc') {
                    url = '/user/pdc/pay-comission-request'
                    var response = await axios.post(url,{
                        _token : this.myToken,
                        password_confirmation : this.password_confirm,
                        montant : Math.round(this.comissionAfrocash),
                    })
                }
                else if(this.typeUser == 'pdraf') {
                    url = '/user/pdraf/pay-comission-request'
                    var response = await axios.post(url,{
                        _token : this.myToken,
                        password_confirmation : this.password_confirm,
                        montant : Math.round(this.comissionAfrocash),
                    })
                }
                
                if(response) {
                    this.isLoading = false
                    alert("Success !")
                    location.reload()
                }
            } catch(error) {
                UIkit.modal($("#modal-pay-comission")).show()
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
        // RECUPERATION DU MONTANT TOTAL DE LA COMISSION
        getAfrocashComission : async function () {
            try {
                this.Loading = true
                let response = await axios.get('/user/reseaux-afrocash/get-comission')
                if(response) {
                    this.comissionAfrocash = response.data
                    this.isLoading = false
                }
            }
            catch(error) {
                alert(error)
            }
        },
        // confirmation du paiement du bonus 
        confirmPayBonus : async function () {
            try {
                this.isLoading = true
                UIkit.modal($("#modal-pay-bonus")).hide()
                let response = await axios.post('/user/objectif/pay-bonus-objectif',{
                    _token : this.myToken,
                    password_confirm : this.password_confirm
                })
                if(response.data == 'done') {
                    UIkit.modal.alert("<div uk-alert class='uk-alert-success'>Operation effectue avec success !</div>")
                        .then(function () {
                            location.reload()
                        })
                }
            } catch(error) {
                this.isLoading = false
                UIkit.modal($("#modal-pay-bonus")).show()
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
        // recuperation de la marge arriere (bonus)
        getBonusObjectif : async function () {
            try {
                let response = await axios.get('/user/objectif/get-bonus-objectif')
                if(response.data) {
                    this.bonus = response.data
                }
            } catch(error) {
                alert(error)
            }
        },
        sendDeblocageForm : async function () {
            this.isLoading = true
            UIkit.modal($("#modal-plus")).hide()
            try {
                this.deblocageForm._token = this.myToken
                let response = await axios.post('/user/tools/deblocage-cga',this.deblocageForm)
                if(response.data == 'done') {
                    this.isLoading = false
                    UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Demande Envoyee!</div>")
                        .then(function () {
                            location.reload()
                        })
                }
            } catch(error) {
                this.isLoading = false
                UIkit.modal($("#modal-plus")).show()
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
        sendAnnuleSaisi : async function() {
            this.isLoading = true
            UIkit.modal($("#modal-plus")).hide()
            try {
                this.annuleSaisiForm._token = this.myToken
                let response = await axios.post('/user/tools/annulation-saisie',this.annuleSaisiForm)
                if(response.data == 'done') {
                    UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Demande Envoyee !</div>")
                        .then(function() {
                            location.reload()
                        })
                }
            } catch(error) {

                this.isLoading = false

                UIkit.modal($("#modal-plus")).show()
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
        getInfosForm : async function() {
            try {
                let response = await axios.get('/user/deblocage/get-infos')
                this.deblocageForm.num_dist = response.data.num_dist
                this.annuleSaisiForm.num_dist = response.data.num_dist
            } catch(error) {
                alert(error)
            }
        }
    },
    computed : {
        myToken() {
            return this.$store.state.myToken
        },
        typeUser () {
            return this.$store.state.typeUser
        }
    }
}
</script>
