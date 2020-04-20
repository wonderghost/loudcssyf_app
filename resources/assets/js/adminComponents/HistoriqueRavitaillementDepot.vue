<template>
   <div class="">
       <loading :active.sync="isLoading"
        :can-cancel="true"
        :is-full-page="fullPage"
        loader="dots"></loading>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-4@m">
                <label for="">Article</label>
                <select v-model="articleFilter" class="uk-select uk-border-rounded">
                    <option value="">Tous</option>
                    <option :value="p.libelle" :key="p.reference" v-for="p in produits">{{p.libelle}} </option>
                </select>
            </div>
            <div class="uk-width-1-4@m">
                <label for="">Du</label>
                <input type="date" class="uk-input uk-border-rounded">
            </div>
            <div class="uk-width-1-4@m">
                <label for="">Au</label>
                <input type="date" class="uk-input uk-border-rounded">
            </div>
            <div class="uk-width-1-4@m">
                <label for="">Depots</label>
                <select v-model="depotFilter" class="uk-select uk-border-rounded">
                    <option value="">Tous</option>
                    <option :value="d.localisation" :key="d.localisation" v-for="d in depots">{{d.localisation}}</option>
                </select>
            </div>
        </div>
        <table class="uk-table uk-table-small uk-table-striped uk-table-divider uk-table-hover uk-table-responsive">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Article</th>
                    <th>Quantite</th>
                    <th>Depot</th>
                    <th>Origine</th>
                    <th>-</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="h in historiqueByProduit.slice(start,end)">
                    <td>{{h.date}}</td>
                    <td>{{h.article}}</td>
                    <td>{{h.quantite}}</td>
                    <td>{{h.depot}} </td>
                    <td>{{h.origine}} </td>
                    <!-- <td>'
                        <button class="uk-button-small uk-button uk-button-default uk-border-rounded">Details</button>
                    </td>' -->
                </tr>
            </tbody>
        </table>
        <ul class="uk-pagination uk-flex uk-flex-center" uk-margin>
            <li> <span> Page active : {{currentPage}} </span> </li>
            <li> <button @click="previousPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> <span uk-pagination-previous></span> Precedent </button> </li>
            <li> <button @click="nextPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> Suivant <span uk-pagination-next></span>  </button> </li>
        </ul>
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
            this.getHistoriqueRavitaillementDepot()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                historique : [],
                depots : [],
                produits : [],
                start : 0 , 
                end : 10,
                currentPage : 1,
                depotFilter : "",
                articleFilter : ""
            }
        },
        methods : {
            getHistoriqueRavitaillementDepot : async function() {
                this.isLoading = true
                try {
                    if(this.typeUser == 'admin') {
                        var response = await axios.get('/admin/depot/historique-depot')
                        var depotResponse = await axios.get('/admin/inventory/depot')
                        var produitResponse = await axios.get('/admin/all-produits')
                    } else {
                        var response = await axios.get('/user/depot/historique-depot')
                        var depotResponse = await axios.get('/user/inventory/depot')
                        var produitResponse = await axios.get('/user/all-produits')
                    }
                    this.historique = response.data
                    this.depots = depotResponse.data
                    this.produits = produitResponse.data
                    this.isLoading = false
                } catch(error) {
                    alert(error)
                }
            },
            nextPage : function () {
                if(this.historiqueByProduit.length > this.end) {
                let ecart = this.end - this.start
                this.start = this.end
                this.end += ecart
                this.currentPage++
                }
            },
            previousPage : function () {
                if(this.start > 0) {
                let ecart = this.end - this.start
                this.start -= ecart
                this.end -= ecart
                this.currentPage--
                }
            }
        },
        computed : {
            typeUser() {
                return this.$store.state.typeUser
            },
            historiqueByDepot() {
                return this.historique.filter( (h) => {
                    return h.depot.match(this.depotFilter)
                })
            },
            historiqueByProduit() {
                return this.historiqueByDepot.filter((h) => {
                    return h.article.match(this.articleFilter)
                })
            }
        }
    }
</script>
