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
            <li><span>Inventaire stock</span></li>
        </ul>

        <h3 class="uk-margin-top">Inventaire Materiel</h3>
        <hr class="uk-divider-small">

        <div class="uk-grid-small" uk-grid>
            <div v-for="(mat,index) in stockVendeur" :key="index" class="uk-width-1-4@m">
                <div class="uk-card uk-border-rounded uk-box-shadow-hover-small uk-background-muted uk-dark uk-card-body uk-padding-small">
                    <h3 class="uk-card-title">{{ mat.produit }}</h3>
                    <p>
                    <ul class="uk-list uk-list-divider">
                        <li>
                            <span>Qte : {{mat.quantite}}</span> ,
                            <span>TTC : {{mat.ttc | numFormat}}</span>
                        </li>
                        <li>
                            <span>HT : {{mat.ht | numFormat}}</span> ,
                            <span>Marge : {{mat.marge | numFormat}}</span>
                        </li>
                    </ul>
                    </p>
            </div>
            </div>
        </div>

        <div class="uk-width-1-1@m">
             <!-- paginate component -->
            <div class="uk-width-1-3@m uk-margin-top">
                <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
                <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
                <button @click="getData()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
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
            <table class="uk-table uk-table-small uk-table-hover uk-table-divider uk-table-striped">
                <thead>
                    <tr>
                        <th>Numero Materiel</th>
                        <th>Utilisateur</th>
                        <th>Article</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(l,index) in listMateriel" :key="index">
                        <td v-for="(c,name) in l" :key="name">
                            <span v-if="name == 'status' && c == 'inactif'" class="uk-alert-primary"><span uk-icon="minus"></span></span>
                            <span v-else-if="name == 'status' && c == 'actif'" class="uk-alert-success"><span uk-icon="check"></span></span>
                            <span v-else>{{c}}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components : {
            Loading
        },
        mounted() {
            this.getData()
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
                listMateriel : [],
                stockVendeur : []
            }
        },
        methods : {
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
            },
            getData : async function () {
                try {
                    this.isLoading = true

                    let response = await axios.get('/pdc/material/inventory')
                    if(response) {
                        this.listMateriel = response.data.all
                        this.stockVendeur = response.data.stock

                        this.isLoading = false

                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.currentPage = response.data.current_page
                        this.firstPage = response.data.first_page
                        this.firstItem = response.data.first_item,
                        this.total = response.data.total
                    }
                }
                catch(error) {
                    alert(error)
                }
            }
        }
    }
</script>
