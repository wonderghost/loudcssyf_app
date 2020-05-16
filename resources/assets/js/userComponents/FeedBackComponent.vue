<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <div id="modal-feedback" class="uk-modal-container" uk-modal>
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Boite &agrave; suggestion</h2>
                </div>
                <div class="uk-modal-body">
                    <template v-if="errors.length" v-for="error in errors">
                        <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                            <a href="#" class="uk-alert-close" uk-close></a>
                            <p>{{error}}</p>
                        </div>
                    </template>
                    <form @submit.prevent="sendFeedback()" class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-1@m uk-margin-small">
                            <label for="">Commentaires </label>
                            <VueEditor v-model="commentaire"></VueEditor>
                        </div>
                        <div class="uk-width-1-1@m uk-margin-small">
                            <label for="">Confirmez le mot de passe</label>
                            <input type="password" v-model="passwordConfirmation" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe">
                        </div>
                        <div>
                            <button class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                        </div>
                    </form>
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-danger uk-border-rounded uk-button-small uk-modal-close" type="button">Fermer</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import { VueEditor } from 'vue2-editor'


    export default {
        components : {
            Loading,
            VueEditor
        },
        mounted() {
            
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                commentaire : "Ecrivez ici ..",
                passwordConfirmation : "",
                errors : []
            }
        },
        methods : {
            sendFeedback : async function () {
                try {
                    this.isLoading = true
                    UIkit.modal($("#modal-feedback")).hide()
                    let response = await axios.post('/user/feedback/send',{
                        _token : this.myToken,
                        commentaire : this.commentaire,
                        password : this.passwordConfirmation
                    })
                    if(response.data == 'done') {
                        UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Success !</div>")
                            .then(function () {
                                location.reload()
                            })
                    }
                } catch(error) {
                    this.isLoading = false
                    UIkit.modal($("#modal-feedback")).show()
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
