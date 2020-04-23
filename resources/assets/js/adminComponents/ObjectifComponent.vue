<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <v-stepper ref="stepper" :steps="steps" v-model="step"></v-stepper>

        <template v-if="step == 1">
            <h4>Etape 1</h4>
            <!-- Erreor block -->
            <template v-if="errors.length" v-for="error in errors">
                <div class="uk-alert-danger uk-border-rounded" uk-alert>
                <a href="#" class="uk-alert-close" uk-close></a>
                <p>{{error}}</p>
                </div>
            </template>

            <form @submit.prevent="firstStepFormSubmit()" uk-grid class="uk-grid-small">
                <div class="uk-width-1-5@m">
                    <label for="">Nom Objectif</label>
                    <input v-model="firstStepForm.objectif_name" type="text" class="uk-input uk-border-rounded">
                </div>
                <div class="uk-width-2-5@m uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : calendar"></span> Du</label>
                        <input type="date" class="uk-input uk-border-rounded" v-model="firstStepForm.debut">
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for=""><span uk-icon="icon : calendar"></span> Au</label>
                        <input type="date" class="uk-input uk-border-rounded" v-model="firstStepForm.fin">
                    </div>
                </div>
                <div class="uk-width-2-5@m">
                    <label for=""><span uk-icon=""></span>Evaluation</label>
                    <select v-model="firstStepForm.evaluation" class="uk-select uk-border-rounded">
                        <option value="">-- Periode d'Evaluation --</option>
                        <option :value="e" :key="e" v-for="e in evaluations">{{e}} Mois</option>
                    </select>
                </div>
                <div class="">
                    <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">ok</button>
                </div>
            </form>
            <div v-if="nextStatus" class="uk-align-right">
                <button @click="$refs.stepper.next()" class="uk-button uk-button-small uk-box-shadow-hover uk-border-rounded">Suivant <span uk-icon="icon : arrow-right"></span></button>
            </div>
        </template>
        <template v-if="step == 2">
            <h4>Etape 2</h4>
            <div v-if="nextStatus" class="uk-align-right">
                <button @click="$refs.stepper.next()" class="uk-button uk-button-small uk-box-shadow-hover uk-border-rounded">Suivant <span uk-icon="icon : arrow-right"></span></button>
            </div>
        </template>
        <template v-if="step == 3">
            <h4>Etape 3</h4>
            <div v-if="nextStatus" class="uk-align-right">
                <button @click="$refs.stepper.next()" class="uk-button uk-button-small uk-box-shadow-hover uk-border-rounded">Suivant <span uk-icon="icon : arrow-right"></span></button>
            </div>
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
                    objectif_name : "",
                    debut : "",
                    fin : "",
                    evaluation : ""
                },
                nextStatus : false,
                errors : []
            }
        },
        methods : {
            firstStepFormSubmit : async function () {
                try {
                    this.firstStepForm._token = this.myToken
                    let response = await axios.post('/admin/objectifs/fisrt-step-validation',this.firstStepForm)
                    console.log(response.data)
                } catch(error) {
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
            }
        }
    }
</script>
