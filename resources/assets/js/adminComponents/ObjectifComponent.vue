<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>


        <h3 class="uk-margin-top"><router-link to="/objectifs/visu"><button class="uk-button uk-button-default uk-button-small uk-border-rounded" uk-tooltip="Visu Objectif"><span uk-icon="arrow-left"></span></button></router-link> Nouvel Objectif</h3>
        <hr class="uk-divider-small">    
        <!-- Erreor block -->
        <template v-if="errors.length" v-for="error in errors">
            <div class="uk-alert-danger uk-border-rounded" uk-alert>
                <a href="#" class="uk-alert-close" uk-close></a>
                <p>{{error}}</p>
            </div>
        </template>

        <v-stepper ref="stepper" :steps="steps" v-model="step"></v-stepper>
        <template v-if="step == 1">
            <h4>Etape 1 (Initialisation des informations)</h4>

            <form @submit.prevent="" uk-grid class="uk-grid-small">
                <div class="uk-width-1-2@m">
                    <label for="">Nom Objectif</label>
                    <input v-model="firstStepForm.objectif_name" type="text" class="uk-input uk-border-rounded">
                </div>
                <div class="uk-width-1-2@m uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : calendar"></span> Du</label>
                        <input type="date" class="uk-input uk-border-rounded" v-model="firstStepForm.debut">
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : calendar"></span> Au</label>
                        <input type="date" class="uk-input uk-border-rounded" v-model="firstStepForm.fin">
                    </div>
                </div>
                <div class="uk-width-1-6@m">
                    <label for="">Marge arriere</label>
                    <input type="text" v-model="firstStepForm.marge_arriere" class="uk-input uk-border-rounded">
                </div>
                <div class="uk-width-1-4@m">
                    <label for=""><span uk-icon=""></span>Evaluation</label>
                    <select v-model="firstStepForm.evaluation" class="uk-select uk-border-rounded">
                        <option value="">-- Periode d'Evaluation --</option>
                        <option :value="e" :key="e" v-for="e in evaluations">{{e}} Mois</option>
                    </select>
                </div>
                    
            </form>
            <div class="uk-align-right">
                <button @click="ClassificationVendeur()" class="uk-button uk-button-small uk-box-shadow-hover uk-border-rounded">Suivant <span uk-icon="icon : arrow-right"></span></button>
            </div>
        </template>
        <template v-if="step == 2">
            <h4>Etape 2 (Classification des Vendeurs) </h4>
            <div>
                <!-- classification des vendeurs -->
                <div class="uk-grid-small uk-grid-divider" uk-grid>
                    <div class="uk-width-1-2@m">
                        <h5>Classification en Recrutement</h5>
                        <hr class="uk-divider-small">
                        <ul uk-accordion>
                            <li>
                                <a class="uk-accordion-title" href="#">Classe A</a>
                                <div class="uk-accordion-content">
                                    <ul class="uk-list uk-list-divider">
                                        <li v-for="u in recrutementClassA" ><span>{{u.user}}</span><span class="uk-badge uk-align-right">{{u.moyenne_recrutement}}</span></li>
                                    </ul>
                                </div>
                            </li>
                            <li class="">
                                <a class="uk-accordion-title" href="#">Class B</a>
                                <div class="uk-accordion-content">
                                    <ul class="uk-list uk-list-divider">
                                        <li v-for="u in recrutementClassB" ><span>{{u.user}}</span><span class="uk-badge uk-align-right">{{u.moyenne_recrutement}}</span></li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="uk-accordion-title" href="#">Class C</a>
                                <div class="uk-accordion-content">
                                    <ul class="uk-list uk-list-divider">
                                        <li v-for="u in recrutementClassC" ><span>{{u.user}}</span><span class="uk-badge uk-align-right">{{u.moyenne_recrutement}}</span></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="uk-width-1-2@m">
                        <h5>Classification en Reabonnement</h5>
                        <hr class="uk-divider-small">
                        <ul uk-accordion>
                            <li>
                                <a class="uk-accordion-title" href="#">Class A</a>
                                <div class="uk-accordion-content">
                                    <ul class="uk-list uk-list-divider">
                                        <li v-for="u in reabonnementClassA" :key="u.user"><span>{{u.user}}</span><span class="uk-badge uk-align-right">{{u.moyenne_reabonnement | numFormat}}</span></li>
                                    </ul>
                                </div>
                            </li>
                            <li class="">
                                <a class="uk-accordion-title" href="#">Class B</a>
                                <div class="uk-accordion-content">
                                    <ul class="uk-list uk-list-divider">
                                        <li v-for="u in reabonnementClassB" :key="u.user"><span>{{u.user}}</span><span class="uk-badge uk-align-right">{{u.moyenne_reabonnement | numFormat}}</span></li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a class="uk-accordion-title" href="#">Class C</a>
                                <div class="uk-accordion-content">
                                    <ul class="uk-list uk-list-divider">
                                        <li v-for="u in reabonnementClassC" :key="u.user"><span>{{u.user}}</span><span class="uk-badge uk-align-right">{{u.moyenne_reabonnement | numFormat}}</span></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="uk-margin-small uk-align-left">
                <button @click="$refs.stepper.previous()" class="uk-button uk-button-small uk-border-rounded"><span uk-icon="icon : arrow-left"></span> Retour</button>
            </div>
            <div class="uk-align-right uk-margin-small">
                <button @click="$refs.stepper.next()" class="uk-button uk-button-small uk-box-shadow-hover uk-border-rounded">Suivant <span uk-icon="icon : arrow-right"></span></button>
            </div>
        </template>
        <template v-if="step == 3">
            <h4>Etape 3 (Assignation des objectifs)</h4>
            <!-- PARAMETRAGE DES OBJECTIFS DE VENTES -->
            <form @submit.prevent="finaliseMakeObjectif()" class="uk-grid-small uk-grid-divider" uk-grid>
                <div class="uk-width-1-6@m">
                    <h5>Recrutement</h5>
                    <div class="uk-margin-small">
                        <label for="">Class A</label>
                        <input v-model="firstStepForm.plafond_recrutement.class_a" type="text" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-margin-small">
                        <label for="">Class B</label>
                        <input type="text" v-model="firstStepForm.plafond_recrutement.class_b" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-margin-small">
                        <label for="">Class C</label>
                        <input type="text" v-model="firstStepForm.plafond_recrutement.class_c" class="uk-input uk-border-rounded">
                    </div>
                </div>
                <div class="uk-width-1-5@m">
                    <h5>Reabonnement</h5>
                    <div class="uk-margin-small">
                        <label for="">Class A</label>
                        <input type="text" v-model="firstStepForm.plafond_reabonnement.class_a" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-margin-small">
                        <label for="">Class B</label>
                        <input type="text" v-model="firstStepForm.plafond_reabonnement.class_b" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-margin-small">
                        <label for="">Class C</label>
                        <input type="text" v-model="firstStepForm.plafond_reabonnement.class_c" class="uk-input uk-border-rounded">
                    </div>
                </div>
                <div class="uk-margin-smal uk-width-1-1@m">
                    <button @click="$refs.stepper.previous()" class="uk-button uk-button-small uk-box-shadow-hover uk-border-rounded"><span uk-icon="icon : arrow-left"></span> Retour</button>
                    <button type="submit"  class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez <span uk-icon="icon :check"></span></button>
                </div>
            </form>
            <!-- // -->
        </template>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import { VStepper } from 'vue-stepper-component'
    export default {
        components : {
            VStepper,
            Loading
        },
        mounted() {
                    
        },
        data () {
            return {
                steps : 3 , 
                step : undefined,
                fullPage : true,
                isLoading : false,
                evaluations : [3,6,9,12],
                firstStepForm : {
                    _token : "",
                    objectif_id : "",
                    objectif_name : "",
                    debut : "",
                    fin : "",
                    evaluation : 3,
                    marge_arriere : 0.01,
                    plafond_recrutement : {
                        'class_a' : "",
                        'class_b' : "",
                        'class_c' : ""
                    },
                    plafond_reabonnement : {
                        'class_a' : "",
                        'class_b' : "",
                        'class_c' : ""
                    }
                },
                errors : [],
                usersClassify : []
            }
        },
        methods : {
            finaliseMakeObjectif : async function () {
                try {
                    this.isLoading = true
                    this.firstStepForm._token = this.myToken
                    this.firstStepForm.users = this.usersClassify

                    let response = await axios.post('/admin/objectifs/finalise-make-objectif',this.firstStepForm)
                    
                    if(response.data == 'done') {
                        this.isLoading = false
                        UIkit.modal.alert("<div class='uk-alert-success' uk-alert>Operation effectuee avec success !</div>")
                            .then(function () {
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
            ClassificationVendeur : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.post('/admin/objectifs/make-classify',{
                        _token : this.myToken,
                        evaluation : this.firstStepForm.evaluation
                    })
                    
                    this.usersClassify = response.data
                    this.$refs.stepper.next()
                    this.isLoading = false

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
            myToken () {
                return this.$store.state.myToken
            },
            // RECRUTEMENT CLASSIFICATION
            recrutementClassA() {
                return this.usersClassify.filter( (user) => {
                    return user.class_recrutement == 'A'
                })
            },
            recrutementClassB() {
                return this.usersClassify.filter( (user) => {
                    return user.class_recrutement == 'B'
                })
            },
            recrutementClassC() {
                return this.usersClassify.filter( (user) => {
                    return user.class_recrutement == 'C'
                })
            },
            // REABONNEMENT CLASSIFICATION
            reabonnementClassA() {
                return this.usersClassify.filter( (user) => {
                    return user.class_reabonnement == 'A'
                })
            },
            reabonnementClassB() {
                return this.usersClassify.filter( (user) => {
                    return user.class_reabonnement == 'B'
                })
            },
            reabonnementClassC() {
                return this.usersClassify.filter( (user) => {
                    return user.class_reabonnement == 'C'
                })
            }
        }
    }
</script>
