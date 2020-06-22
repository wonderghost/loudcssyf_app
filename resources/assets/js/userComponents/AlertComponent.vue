<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <div id="modal-alert-abonnement" class="uk-modal-container" uk-modal="esc-close : false; bg-close : false">
            <div class="uk-modal-dialog">
                <button class="uk-modal-close-default" type="button" uk-close></button>
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Relance Abonnement</h2>
                </div>
                <div class="uk-modal-body">
                    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-left-medium">
                        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Relance <span class="uk-badge">{{relanceByUser.length}}</span> </a></li>
                        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Inactif <span class="uk-badge">{{inactifByUser.length}}</span></a></li>
                    </ul>

                    <div v-if="typeUser == 'admin'" class="uk-width-1-3@m">
                        <label for="">
                            <span uk-icon="icon : users"></span>
                            Vendeurs
                        </label>
                        <select v-model="userSelect"  class="uk-select uk-border-rounded">
                            <option value="">Tous</option>
                            <option v-for="u in users" :value="u.localisation" :key="u.username">{{u.localisation}}</option>
                        </select>
                    </div>
                    <div v-if="typeUser == 'v_da' || typeUser == 'v_standart'" class="uk-width-1-3@m">
                        <label for="">
                            <span uk-icon="icon : users"></span>
                            Vendeurs
                        </label>
                        <span class="uk-input uk-border-rounded">{{userLocalisation}}</span>
                    </div>
                    <ul class="uk-switcher uk-overflow-auto uk-height-medium">
                        <li>
                            <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider">
                                <thead>
                                    <tr>
                                        <th>Materiel</th>
                                        <th>Distributeur</th>
                                        <th>Debut Abonnement</th>
                                        <th>Fin Abonnement</th>
                                        <td>Jour(s) Restants</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr :uk-tooltip="r.distributeur" v-for="(r,index) in relanceByUser" :key="index">
                                        <td>{{r.serial}}</td>
                                        <td>{{r.distributeur.substring(0,10)}}...</td>
                                        <td>{{r.debut}}</td>
                                        <td>{{r.fin}}</td>
                                        <td>{{r.jours}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                        <li>
                            <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider">
                                <thead>
                                    <tr>
                                        <th>Materiel</th>
                                        <th>Distributeur</th>
                                        <th>Debut</th>
                                        <th>Fin Abonnement</th>
                                        <th>Jour(s)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr :uk-tooltip="r.distributeur" v-for="(r,index) in inactifByUser" :key="index">
                                        <td>{{r.serial}}</td>
                                        <td>{{r.distributeur.substring(0,10)}}...</td>
                                        <td>{{ r.debut }}</td>
                                        <td>{{r.fin}}</td>
                                        <td>{{r.jours}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                    </ul>
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-danger uk-modal-close uk-button-small uk-border-rounded" type="button">Fermer</button>
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
            this.getAlertAbonnement()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                relance : [],
                inactif : [],
                users : [],
                userSelect : ""
            }
        },
        methods : {
            getAlertAbonnement : async function () {
                try {
                    let response = await axios.get('/admin/alert-abonnement/all')
                    this.relance = response.data.relance
                    this.inactif = response.data.inactif

                    if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                        this.userSelect = this.userLocalisation
                    }
                    response = await axios.get('/user/all-vendeurs')
                    this.users = response.data
                    this.$store.commit('setAlertCount',this.relanceByUser.length)
                    this.$store.commit('setAlertInactifCount',this.inactifByUser.length)
                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {
            typeUser() {
                return this.$store.state.typeUser
            },
            userLocalisation() {
                return this.$store.state.userLocalisation
            },
            myToken() {
                return this.$store.state.myToken
            },
            relanceByUser() {
                return this.relance.filter((r) => {
                    return r.distributeur.match(this.userSelect) 
                })
            },
            inactifByUser() {
                return this.inactif.filter((i) => {
                    return i.distributeur.match(this.userSelect)
                })
            }
        }
    }
</script>
