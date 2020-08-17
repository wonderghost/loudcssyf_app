<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
            :can-cancel="false"
            :is-full-page="fullPage"
            loader="dots"></loading>        

            <h3>Formule</h3>
            <hr class="uk-divider-small">
            
        <ul class="uk-subnav uk-subnav-pill" uk-switcher>
            <li><a class="uk-button uk-button-primary uk-border-rounded uk-button-small" href="#">Nouvelle Formule</a></li>
            <li><a class="uk-button uk-button-primary uk-border-rounded uk-button-small" href="#">Toutes les formules</a></li>
            <li><a class="uk-button uk-button-primary uk-border-rounded uk-button-small" href="#">NOuvelle Option</a></li>
            <li><a class="uk-button uk-button-primary uk-border-rounded uk-button-small" href="#">Toutes les options</a></li>
        </ul>

        <ul class="uk-switcher uk-margin">
            <li>
                <!-- AJOUTER UNE FORMULE -->
                <add-formule-component type="formule"></add-formule-component>
            </li>
            <li>
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m">
                        <ul class="uk-list uk-list-divider">
                            <li v-for="f in formules" :key="f.nom"> 
                                <span>{{f.nom}}</span>
                                <span class="uk-float-right">{{f.prix | numFormat}} GNF
                                    <button uk-tooltip="Cliquez pour modifier" class="uk-text-capitalize uk-button-primary uk-border-rounded"><span uk-icon="icon : pencil"></span></button>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
            <li>
                <!-- AJOUTER UNE OPTIONS -->
                <add-formule-component type="option"></add-formule-component>
            </li>
            <li>
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m">
                        <ul class="uk-list uk-list-divider">
                            <li v-for="o in options" :key="o.nom"> 
                                <span>{{o.nom}}</span>
                                <span class="uk-float-right">{{o.prix | numFormat}} GNF
                                    <button uk-tooltip="Cliquez pour modifier" class="uk-text-capitalize uk-button-primary uk-border-rounded"><span uk-icon="icon : pencil"></span></button>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
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
            this.getAllFormule()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                formules : [],
                options : []
            }
        },
        methods : {
            getAllFormule : async function () {
                try {
                    let response = await axios.get('/admin/formule/list')
                    this.formules = response.data.formules
                    this.options = response.data.options
                    console.log(response.data)
                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {

        }
    }
</script>
