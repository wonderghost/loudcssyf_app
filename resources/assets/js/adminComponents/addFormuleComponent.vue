<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <div class="uk-grid-small" uk-grid>
            <!-- <div class="uk-width-1-4@m"></div> -->
            <div class="uk-width-1-4@m">
                <template v-if="type == 'formule'">
                    <h3>Ajoutez une formule</h3>
                    <hr class="uk-divider-small">
                    <form @submit.prevent="addFormule()" class="uk-grid-small" uk-grid>
                        <!-- Erreor block -->
                        <template v-if="errors.length" v-for="error in errors">
                            <div class="uk-alert-danger uk-border-rounded uk-width-1-1@m uk-width-1-1@s" uk-alert>
                                <a href="#" class="uk-alert-close" uk-close></a>
                                <p>{{error}}</p>
                            </div>
                        </template>
                        <div class="uk-margin-small uk-width-1-1@m">
                            <label for="">Nom de la formule</label>
                            <input v-model="formData.name" type="text" class="uk-input uk-border-rounded" placeholder="Nom de la formule">
                        </div>
                        <div class="uk-margin-small uk-width-1-1@m">
                            <label for="">Prix</label>
                            <input v-model="formData.price" type="number" class="uk-input uk-border-rounded" placeholder="Prix de la formule">
                        </div>
                        <div class="uk-width-1-1@s uk-width-1-3@m">
                            <button class="uk-button uk-border-rounded uk-button-small uk-button-primary">Envoyez</button>
                        </div>
                    </form>
                </template>
                <template v-else>
                    <h3>Ajoutez une options</h3>
                    <hr class="uk-divider-small">
                    <form @submit.prevent="addOption()" class="uk-grid-small" uk-grid>
                        <!-- Erreor block -->
                        <template v-if="errors.length" v-for="error in errors">
                            <div class="uk-alert-danger uk-border-rounded uk-width-1-1@m uk-width-1-1@s" uk-alert>
                                <a href="#" class="uk-alert-close" uk-close></a>
                                <p>{{error}}</p>
                            </div>
                        </template>
                        <div class="uk-width-1-1@m uk-margin-small">
                            <label for="">Nom de l'option</label>
                            <input type="text" v-model="formDataOption.name" class="uk-input uk-border-rounded"  placeholder="Entrez le nom de l'option">
                        </div>
                        <div class="uk-width-1-1@m uk-margin-small">
                            <label for="">Prix</label>
                            <input type="number" v-model="formDataOption.price" class="uk-input uk-border-rounded" placeholder="Entrez le prix de l'option">
                        </div>
                        <div class="uk-width-1-1@s uk-width-1-3@m">
                            <button class="uk-button-small uk-button uk-button-primary uk-border-rounded">
                                Envoyez
                            </button>
                        </div>
                    </form>
                </template>
            </div>
            <div class="uk-width-1-4@m"></div>
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
        props : {
            type : String
        },
        mounted() {

        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                formData : {
                    _token : "",
                    name : "",
                    price : ""
                },
                formDataOption : {
                    _token : "",
                    name : "",
                    price : 0
                },
                errors : []
            }
        },
        methods : {
            addOption : async function() {
                try {
                    this.isLoading = true
                    this.formDataOption._token = this.myToken
                    let response = await axios.post('/admin/option/add',this.formDataOption)
                    if(response.data == 'done') {
                        UIkit.modal.alert("<div class='uk-alert-sucess uk-border-rounded' uk-alert>Operation effectue avec success !</div>")
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
            },
            addFormule : async function () {
                try {
                    this.isLoading = true
                    this.formData._token = this.myToken
                    let response = await axios.post('/admin/formule/add',this.formData)
                    if(response.data == 'done') {
                        alert("Success !")
                        Object.assign(this.$data,this.$options.data())
                        // UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Operation effectue avec success !</div>")
                        //     .then(function() {
                        //         location.reload()
                        //     })
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
