[<template>
    <div>
        <loading :active.sync="isLoading"
            :can-cancel="false"
            :is-full-page="fullPage"
            loader="dots"></loading>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-2@m">
                <!-- Erreor block -->
                <template v-if="errors.length" v-for="error in errors">
                    <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert :key="error">
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{error}}</p>
                    </div>
                </template>
                <!-- formulaire de transfert materiel d'un depot a un autre-->
                <form @submit.prevent="sendTransfertMaterial()" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m">
                        <label for="">Depot d'origine</label>
                        <select v-model="formData.origine" class="uk-select uk-border-rounded">
                            <option value="">-- Choisissez le depot --</option>
                            <option :value="d.localisation" :key="d.localisation" v-for="d in materials">{{d.localisation}}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for="">Depot de destination</label>
                        <select v-model="formData.destination" class="uk-select uk-border-rounded">
                            <option value="">-- Choisissez le depot --</option>
                            <option :value="d.localisation" :key="d.localisation" v-for="d in materials">{{d.localisation}}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Quantite</label>
                        <input v-model="formData.quantite" type="number" class="uk-input uk-border-rounded" min="1" max="10">
                    </div>
                    <div v-for="i in parseInt(formData.quantite)" :key="i" class="uk-width-1-2@m">
                        <label for="">Materiel-{{i}}</label>
                        <input v-model="formData.serials[i-1]" type="text" class="uk-input uk-border-rounded">
                    </div>
                    <div class="uk-width-1-1@m">
                        <label for="">Confirmez votre mot de passe</label>
                        <input type="password" class="uk-input uk-border-rounded">
                    </div>
                    <div >
                        <button type="submit" class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
                    </div>
                </form>
                <!-- // -->
            </div>
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
            
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                formData : {
                    _token : "",
                    origine : "",
                    destination : "",
                    quantite : 0,
                    serials : []
                },
                errors : []
            }
        },
        methods : {
            sendTransfertMaterial : async function () {
                try {
                    this.formData._token = this.myToken
                    let response = await axios.post('/admin/inventory-depot/transfert-material', this.formData)
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
            },
            materials() {
                return this.$store.state.materials
            }
        }
    }
</script>
