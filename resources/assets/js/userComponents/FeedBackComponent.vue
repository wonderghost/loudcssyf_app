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
                    <ul class="uk-subnav uk-subnav-pill" uk-switcher>
                        <li><a href="#">Envoyez une suggestion</a></li>
                        <li v-if="typeUser == 'admin'"><a href="#">Historique</a></li>
                    </ul>

                    <ul class="uk-switcher uk-margin">
                        <li>
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
                        </li>
                        <li v-if="typeUser == 'admin'">
                            <table class="uk-table uk-table-small uk-table-divider uk-table-striped">
                                <thead>
                                    <tr>
                                        <th>Vendeurs</th>
                                        <th>Commentaire</th>
                                        <th>Date</th>
                                        <th>Vu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="l in list" :key="l.id">
                                        <td>{{l.vendeurs}}</td>
                                        <td v-html="l.commentaire.substring(0,100)"></td>
                                        <td>
                                            {{l.date}}
                                        </td>
                                        <td>
                                            {{l.read_at}}
                                        </td>
                                        <td>
                                            <button class="uk-button uk-text-capitalize uk-button-small uk-button-primary uk-border-rounded">
                                                marquer comme lu
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                    </ul>
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
            this.listFeedBack()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                commentaire : "Ecrivez ici ..",
                passwordConfirmation : "",
                errors : [],
                list : []
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
            },
            listFeedBack : async function () {
                try {
                    let response = await axios.get('/user/feedback/list')
                    this.list = response.data
                    console.log(response.data)
                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            },
            typeUser() {
                return this.$store.state.typeUser
            }
        }
    }
</script>
