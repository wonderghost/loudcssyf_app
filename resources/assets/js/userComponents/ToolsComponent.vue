<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>
        <!-- BONUS BUTTON -->
        <div class="bonus-view uk-position-fixed uk-position-z-index">
            <button class="uk-button uk-button-small uk-button-primary uk-border-rounded"  uk-tooltip="Bonus"> 
                <img src="/img/coins.png" width="30" alt=""> 
                <span class="uk-text-bold">
                    {{bonus | numFormat}} GNF
                </span>
            </button>
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
            bonus : 0 // marge arriere 
        }
    },
    methods : {
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
            // this.isLoading = true
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
        }
    }
}
</script>
