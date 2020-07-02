<template>
    <div>
        <loading :active.sync="isLoading"
            :can-cancel="false"
            :is-full-page="fullPage"
            loader="dots"></loading>
        <!-- Erreor block -->
        <template v-if="errors.length" v-for="error in errors">
            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-2@m" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
            </div>
        </template>
        <div class="uk-width-1-2@m">
            <form @submit.prevent="sendAffectation()" class="uk-grid-small" uk-grid>
                <div class="uk-width-1-6@m">
                    <label for="">Quantite</label>
                    <input @change="updateQuantity()" @keyup="updateQuantity()" v-model="formData.quantite" type="number" class="uk-input uk-border-rounded" min="0" max="10">
                </div>

                <div v-for="entry in parseInt(formData.quantite)" :key="entry" class="uk-width-1-1@m uk-grid-small" uk-grid>
                    <div class="uk-width-1-2@m">
                        <label for="">Materiel</label>
                        <input type="text" class="uk-input uk-border-rounded" v-model="formData.serial_number[entry-1]">
                    </div>
                    <div class="uk-width-1-2@m">
                        <label for="">Depot</label>
                        <select class="uk-select uk-border-rounded" v-model="formData.depots[entry-1]">
                            <option value="">--Depot--</option>
                            <option :value="d.localisation" v-for="d in depotList" :key="d.localisation">{{ d.localisation }}</option>
                        </select>
                    </div>
                </div>
                <div class="uk-width-1-1@m">
                    <label for=""><span uk-icon="icon : lock"></span> Confirmez votre mot de passe</label>
                    <input v-model="formData.confirmation_password" type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe pour confirmer">
                </div>
                <div class="uk-width-1-1@m">
                    <button type="submit" class="uk-button uk-button-small uk-border-rounded uk-button-primary">
                        Validez
                    </button>
                </div>
            </form>
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
            this.getInfosAffectation()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                formData : {
                    quantite : 0,
                    serial_number : [""],
                    depots : [""],
                    confirmation_password : "",
                    _token : ""
                },
                depotList : [],
                serialInfos : [],
                errors : []
            }
        },
        methods: {
            updateQuantity : function () {
                try {
                    if(this.formData.quantite < 0) {
                        throw "Quantite minimum 1 !"
                    }
                    
                    if(this.formData.serial_number.length > this.formData.quantite) {
                        let diff = this.formData.serial_number.length - this.formData.quantite
                        for(var j = 0 ; j < diff ; j++) {
                            this.formData.serial_number.pop()
                            this.formData.depots.pop()
                        }
                    }

                    for(var i = 0 ; i < this.formData.quantite ; i++) {
                        if(this.serialInfos[i]) {
                            Vue.set(this.formData.serial_number,i,this.serialInfos[i].serial_number)
                        }
                        else {
                            this.formData.quantite--
                            throw "Indisponible!"
                        }
                    }
                    
                } catch(error) {
                    alert(error)
                }
            },
            // recuperer les informations lies au materiels
            getInfosAffectation : async function () {
                try {
                    let response = await axios.get('/admin/inventory/depot')
                    this.depotList = response.data

                    response = await axios.get('/admin/infos-affectation')
                    if(response) {
                        this.serialInfos = response.data
                    }

                } catch(error) {
                    alert(error)
                }
            },
            sendAffectation : async function () {
                try {
                    this.isLoading = true
                    this.formData._token = this.myToken
                    let response = await axios.post('/admin/send-affectation',this.formData)
                    
                    if(response.data == 'done') {
                        this.isLoading = false
                        UIkit.modal.alert("<div class='uk-alert uk-alert-success'>Operation reussie !</div>")
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
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
