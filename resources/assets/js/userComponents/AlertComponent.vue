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
                                    <tr :uk-tooltip="r.distributeur" v-for="(r,index) in relanceByUser.slice(start,end)" :key="index">
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
                                    <tr :uk-tooltip="r.distributeur" v-for="(r,index) in inactifByUser.slice(start,end)" :key="index">
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
                <div class="uk-modal-footer">
                    <div class="uk-text-center">
                        <span>Page : {{currentPage}}</span>
                        <button @click="previousPage()" class="uk-button uk-button-small uk-button-default uk-border-rounded">Precedent</button>
                        <button @click="nextPage()" class="uk-button uk-button-small uk-button-default uk-border-rounded">Suivant <span uk-icon="icon : carret-right"></span></button>
                    </div>
                    <div class="uk-text-right">
                        <button class="uk-button uk-button-danger uk-modal-close uk-button-small uk-border-rounded" type="button">Fermer</button>
                    </div>
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
        created() {
            // this.getAlertAbonnement()
        },
        mounted() {
            this.getAlertAbonnement()
            // this.getAlertCount()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                relance : [],
                inactif : [],
                users : [],
                userSelect : "",
                count : {
                    relance : 0,
                    inactif : 0
                },
                start : 0,
                end : 15,
                currentPage : 1
            }
        },
        methods : {
            nextPage : function () {
                try {
                    if(this.relanceByUser.length > this.end) {
                        let ecart = this.end - this.start
                        this.start = this.end
                        this.end += ecart
                        this.currentPage++
                    }
                } catch(error) {
                    alert(error)
                }
            },
            previousPage : function () {
                if(this.start > 0) {
                let ecart = this.end - this.start
                this.start -= ecart
                this.end -= ecart
                this.currentPage--
                }
            },
            getAlertCount : async function () {
                try {
                    let response = await axios.get('/admin/alert-abonnement/count')
                    this.count.relance = response.data.relance_count
                    this.count.inactif = response.data.inactif_count
                    // this.$store.commit('setAlertCount',this.count.relance)
                    // this.$store.commit('setAlertInactifCount',this.count.inactif)
                    
                } catch(error) {
                    alert(error)
                }
            },
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
