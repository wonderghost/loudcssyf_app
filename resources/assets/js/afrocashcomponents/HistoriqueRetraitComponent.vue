<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <ul class="uk-breadcrumb">
            <li><router-link uk-tooltip="Tableau de bord" to="/dashboard"><span uk-icon="home"></span></router-link></li>
            <li><span>Afrocash</span></li>
            <li><span>Historique de Retrait</span></li>
        </ul>

        <h3>Historique de Retrait</h3>
        <hr class="uk-divider-small">

        <nav uk-navbar>
            <div class="uk-navbar-left">
                <ul class="uk-navbar-nav">
                    <li class="uk-button"><router-link to="/afrocash/historique/depot">Historique de Depot</router-link></li>
                </ul>
            </div>
        </nav>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-6@m">
                <label for="">Recherche</label>
                <input type="text" class="uk-input uk-border-rounded" placeholder="Recherche ...">
            </div>
            <div class="uk-width-1-6@m">
                <label for="">Status</label>
                <select class="uk-select uk-border-rounded">
                    <option value="none">Tous</option>
                </select>
            </div>
            <div class="uk-width-1-6@m">
                <label for="">Paiement</label>
                <select class="uk-select uk-border-rounded">
                    <option value="none">Tous</option>
                </select>
            </div>
            <div class="uk-width-1-6@m">
                <label for="">Comission (GNF)</label>
                <span class="uk-input uk-text-center uk-border-rounded">{{ comission | numFormat }}</span>
            </div>
            <!-- paginate component -->
            <div class="uk-width-1-3@m uk-margin-top">
                <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
                <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
                <button @click="onInit()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
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
        <div>
            <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Initiateur</th>
                        <th>Destinateur</th>
                        <th>Montant(GNF)</th>
                        <th>Frais(GNF)</th>
                        <th>Comission Pdraf(GNF)</th>
                        <th v-show="typeUser == 'pdc'">Comission Pdc(GNF)</th>
                        <th>Status</th>
                        <th>Paiement</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(r,index) in retraits" :key="index">
                        <td>{{ r.date }}</td>
                        <td>{{ r.initiateur }}</td>
                        <td>{{ r.destinateur }}</td>
                        <td>{{ r.montant | numFormat }}</td>
                        <td>{{ r.frais | numFormat }}</td>
                        <td>{{ r.comission | numFormat }}</td>
                        <td v-show="typeUser == 'pdc'">{{ r.pdc_comission | numFormat }}</td>
                        <td v-if="r.status" class="uk-text-success"><span uk-icon="check"></span></td>
                        <td v-else class="uk-text-primary"><span uk-icon="more"></span></td>
                        <td v-if="r.pay_state" class="uk-text-success"><span uk-icon="check"></span></td>
                        <td v-else class="uk-text-primary"><span uk-icon="more"></span></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        components : {
            Loading
        },
        mounted() {
            this.onInit()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                retraits : [],
                comission : 0,
                // paginate
                nextUrl : "",
                lastUrl : "",
                perPage : "",
                currentPage : 1,
                firstPage : "",
                firstItem : 1,
                total : 0,
                // #####       
            }
        },
        methods : {
            onInit : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/user/afrocash/historique/retrait')
                    if(response) {
                        this.retraits = response.data.all
                        this.comission = response.data.total_comission

                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.perPage = response.data.per_page
                        this.firstItem = response.data.first_item
                        this.total = response.data.total

                        this.isLoading = false
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            paginateFunction : async function (url) {
                try {
                    let response = await axios.get(url)
                    if(response && response.data) {
                        
                        this.all = response.data.all
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
            }
        },
        computed : {
            typeUser() {
                return this.$store.state.typeUser
            }
        }
    }
</script>
