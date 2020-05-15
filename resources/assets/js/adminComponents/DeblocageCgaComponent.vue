<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>
        <!-- modal confirm password -->
        <div id="modal-deblocage-confirm-password" uk-modal>
            <div class="uk-modal-dialog uk-modal-body">
                <h2 class="uk-modal-title">Confirmez votre mot de passe</h2>
                <!-- Erreor block -->
                      <template v-if="errors.length" v-for="error in errors">
                      <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                        <a href="#" class="uk-alert-close" uk-close></a>
                        <p>{{error}}</p>
                      </div>
                    </template>
                <form @submit.prevent="confirmDeblocage()">
                    <div class="uk-margin-small">
                        <label for="">Entrez votre mot de passe</label>
                        <input type="password" v-model="passwordConfirmation" class="uk-input uk-border-rounded" autofocus placeholder="Entrez votre mot de passe">
                    </div>
                    <button class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                </form>
                <p class="uk-text-right">
                    <button class="uk-button uk-button-danger uk-button-small uk-border-rounded uk-modal-close" type="button">Fermer</button>
                </p>
            </div>
        </div>
        <!-- // -->
        <div id="modal-deblocage" class="uk-modal-container" uk-modal>
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">DEBLOCAGE CGA</h2>
                </div>
                <div class="uk-modal-body">
                    <template v-if="autorizeState">

                        <table class="uk-table uk-table-small uk-table-hover uk-table-striped uk-table-divider">
                            <thead>
                                <tr>
                                    <th>Nom Prenom</th>
                                    <th>Compte Utilisateur</th>
                                    <th>Vendeurs</th>
                                    <th>Num dist</th>
                                    <th>Status</th>
                                    <th>-</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="l in list" :key="l.id">
                                    <td>{{l.nom_prenom}}</td>
                                    <td>{{l.user_account}}</td>
                                    <td>{{l.vendeurs}}</td>
                                    <td>{{l.num_dist}}</td>
                                    <template v-if="l.state == 0">
                                        <td>non confirme</td>
                                        <td>
                                            <button @click="deblocageId = l.id" uk-toggle="target : #modal-deblocage-confirm-password" class="uk-text-capitalize uk-button uk-button-small uk-button-primary uk-border-rounded">confirme</button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td >confirme</td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                    <template v-else>
                        <div class="uk-alert-warning" uk-alert>
                            <p class="uk-text-center"><span uk-icon="icon : warning"></span> Vous n'etes pas autorise a effectue cette action!</p>
                        </div>
                    </template>
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-danger uk-button-small uk-border-rounded uk-modal-close" type="button">Fermer</button>
                </div>
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
            this.getDeblocageList()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                list : [],
                autorizeState : true,
                passwordConfirmation : "",
                deblocageId : "",
                errors : []
            }
        },
        methods : {
            getDeblocageList : async function () {
                try {
                    let response = await axios.get('/user/deblocage/list')
                    this.list = response.data
                    this.$store.commit('setDeblocageCount',this.unconfirmedDeblocage.length)
                } catch(error) {
                    if(error.response.data == 'not_autorize') {
                        this.autorizeState = false
                    }
                }
            },
            confirmDeblocage : async function () {
                try {
                    this.isLoading = true
                    UIkit.modal($("#modal-deblocage-confirm-password")).hide()
                    let response = await axios.post('/user/deblocage/confirm-state',{
                        _token : this.myToken,
                        password : this.passwordConfirmation,
                        deblocage_id : this.deblocageId
                    })
                    if(response.data == 'done') {
                        UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert>Success !</div>")
                            .then(function () {
                                location.reload()
                            })
                    }
                } catch(error) {
                    this.isLoading = false
                    UIkit.modal($("#modal-deblocage-confirm-password")).show()
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
            },
            unconfirmedDeblocage() {
                return this.list.filter( (l) => {
                    return l.state == 0
                })
            }
        }
    }
</script>
