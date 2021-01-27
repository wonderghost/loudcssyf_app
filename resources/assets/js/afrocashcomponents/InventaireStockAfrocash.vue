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
            <li><span>Reseaux Afrocash</span></li>
            <li><span>Inventaire Stock</span></li>
        </ul>

        <h3>Inventaire Stock Reseaux Afrocash ({{ searchByPdraf.length }})</h3>
        <hr class="uk-divider-small">
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-5@m">
                <label for="">Pdc</label>
                <input v-model="pdcSearch" type="text" class="uk-input uk-border-rounded" placeholder="Pdc ...">
            </div>
            <div class="uk-width-1-5@m">
                <label for="">Pdraf</label>
                <input v-model="pdrafSearch" type="text" class="uk-input uk-border-rounded" placeholder="Pdraf ...">
            </div>
            <div class="uk-width-1-5@m">
                <label for="">Status</label>
                <select v-model="status" class="uk-select uk-border-rounded">
                    <option value="none">Tous</option>
                    <option value="inactif">inactif</option>
                    <option value="actif">actif</option>
                </select>
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
            <table class="uk-table-small uk-table uk-table-divider uk-table-striped">
                <thead>
                    <tr>
                        <th>Numero Materiel</th>
                        <th>PDC</th>
                        <th>PDRAF</th>
                        <th>Article</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                   <tr v-for="(m,index) in searchByPdraf" :key="index">
                       <td>{{ m.numero_materiel }}</td>
                       <td>{{ m.pdc }}</td>
                       <td>{{ m.pdraf }}</td>
                       <td>{{ m.article }}</td>
                       <td>{{ m.status }}</td>
                   </tr>
                </tbody>
            </table>
            <div class="uk-flex uk-flex-center">
                <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
            </div>
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
                listMateriel : [],
                pdcSearch : "",
                pdrafSearch : "",
                status : "none",
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
                    
                    let response = await axios.get('/admin/afrocash/inventaire-stock')
                    if(response) {
                        this.listMateriel = response.data.all

                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.perPage = response.data.per_page
                        this.firstItem = response.data.first_item
                        this.total = response.data.total
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
                        
                        this.listMateriel = response.data.all

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
            searchByPdc () {
                return this.listMateriel.filter((l) => {
                    return l.pdc.toUpperCase().match(this.pdcSearch.toUpperCase())
                })
            },
            searchByPdraf() {
                return this.searchByPdc.filter((l) => {
                    return l.pdraf.toUpperCase().match(this.pdrafSearch.toUpperCase())
                })
            }
        }
    }
</script>
