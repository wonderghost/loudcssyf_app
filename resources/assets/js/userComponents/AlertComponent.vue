<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <h3><router-link class="uk-button uk-button-small uk-border-rounded uk-button-default uk-text-small" uk-tooltip="Retour" to=""><span uk-icon="arrow-left"></span></router-link> Relance Abonnement</h3>
        <hr class="uk-divider-small">

        <div class="uk-grid-small" uk-grid>
            <!-- paginate component -->
            <div class="uk-width-1-3@m">
                <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
                <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
                <button @click="getAlertAbonnement()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
                <template v-if="lastUrl">
                <button @click="paginateFunction(lastUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Precedent">
                    <span uk-icon="chevron-left"></span>
                </button>
                </template>
                <template v-if="nextUrl">
                <button @click="paginateFunction(nextUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize u-t uk-text-small" uk-tooltip="Suivant">
                    <span uk-icon="chevron-right"></span>
                </button>
                </template>
            </div>
          <!-- // -->
        </div>

        <ul uk-tab>
            <li><a href="#">Relance</a></li>
            <li><a href="#">Inactif</a></li>
        </ul>

        <ul class="uk-switcher uk-margin">
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
                        <tr :uk-tooltip="r.distributeur" v-for="(r,index) in relance" :key="index">
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
                        <tr :uk-tooltip="r.distributeur" v-for="(r,index) in inactif" :key="index">
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
        <div class="uk-flex uk-flex-center">
            <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
        </div>

        <!-- <div class="">
            <div>
                <div class="">
                    <h2 class="">Relance Abonnement</h2>
                </div>
                <div class="">
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
                </div>
            </div>
        </div> -->
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
            // this.getAlertCount()
        },
        data() {
            return {
                // paginate
                nextUrl : "",
                lastUrl : "",
                perPage : "",
                currentPage : 1,
                firstPage : "",
                firstItem : 1,
                total : 0,
        // #####
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
            paginateFunction : async function (url) {
                try {
                
                let response = await axios.get(url)
                if(response && response.data) {
                    
                        this.relance = response.data.relance
                        this.inactif = response.data.inactif

                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.currentPage = response.data.current_page
                        this.firstPage = response.data.first_page
                        this.firstItem = response.data.first_item,
                        this.total = response.data.total
                    }
                }
                catch(error) {
                    alert("Erreur!")
                    console.log(error)
                }
            },
            getAlertAbonnement : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/admin/alert-abonnement/all')
                    
                    if(response) {

                        this.relance = response.data.relance
                        this.inactif = response.data.inactif

                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.perPage = response.data.per_page
                        this.firstItem = response.data.first_item
                        this.total = response.data.total
                    }

                    if(this.typeUser == 'v_da' || this.typeUser == 'v_standart') {
                        this.userSelect = this.userLocalisation
                    }
                    response = await axios.get('/user/all-vendeurs')
                    this.users = response.data


                    this.isLoading = false

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
