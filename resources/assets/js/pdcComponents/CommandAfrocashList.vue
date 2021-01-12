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
            <li><span>Toutes les commandes</span></li>
        </ul>

        <h3 class="uk-margin-top">Toutes les commandes</h3>
        <hr class="uk-divider-small">

        <nav v-if="typeUser == 'pdc' || typeUser == 'pdraf'" class="" uk-navbar>
          <div class="uk-navbar-left">
              <ul class="uk-navbar-nav">
                  <li class=""><router-link :to="newCommandUrl">Nouvelle Commande</router-link></li>
                  <li v-if="$route.params.state == 2 && typeUser == 'pdc'">
                      <router-link to="/pdc/command/list/1">Mes Commandes</router-link>
                  </li>
                  <li v-if="$route.params.state == 1">
                      <router-link to="/pdc/command/list/2">Commande Pdraf</router-link>
                  </li>
              </ul>
          </div>
        </nav>

        <div>
            <div class="uk-grid-small" uk-grid>
                <div v-if="$route.params.state == 2 && typeUser == 'pdc'" class="uk-width-1-6@m">
                    <label for="">Vendeurs</label>
                    <select @change="filterRequest()" v-model="filterData.user" class="uk-select uk-border-rounded">
                        <option value="none">Tous</option>
                        <option :value="u.user.username" v-for="(u,index) in pdrafUser" :key="index">{{ u.user.localisation }}</option>
                    </select>
                </div>
                <div class="uk-width-1-6@m">
                    <label for="">Status</label>
                    <select @change="filterRequest()" v-model="filterData.status" class="uk-select uk-border-rounded">
                        <option value="instance">Instance</option>
                        <option value="confirmer">Confirmer</option>
                        <option value="annuler">Annuler</option>
                    </select>
                </div>
            </div>
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
            <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Vendeur</th>
                        <th>Article</th>
                        <th>Quantite</th>
                        <th>Status</th>
                        <th>Livraison</th>
                        <th class="uk-text-center">-</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(c,index) in list" :key="index">
                        <td>{{c.date}}</td>
                        <td>{{c.heure}}</td>
                        <td>{{c.vendeur}}</td>
                        <td>{{c.article}}</td>
                        <td>{{c.quantite}}</td>
                        <td v-if="c.status" class="uk-text-success"><span uk-icon="check"></span></td>
                        <td v-if="!c.status && c.remove_status" class="uk-text-danger"><span uk-icon="close"></span></td>
                        <td v-if="!c.status && !c.remove_status" class="uk-text-primary"><span uk-icon="more"></span></td>
                        <td v-if="c.livraison" class="uk-text-success"><span uk-icon="check"></span></td>
                        <td v-if="!c.livraison && c.livraison_remove" class="uk-text-danger"><span uk-icon="close"></span></td>
                        <td v-if="!c.livraison && !c.livraison_remove" class="uk-text-primary"><span uk-icon="more"></span></td>
                        <td>
                            <button @click="showConfirmCode(c.confirm_code)" v-if="(typeUser == 'pdc' && $route.params.state == 1) || (typeUser == 'pdraf' && $route.params.state == 2)" uk-tooltip="Code de confirmation" class="uk-border-rounded uk-button-primary uk-padding-remove">
                                <i class="material-icons">preview</i>
                            </button>
                            <button uk-tooltip="Details" class="uk-border-rounded uk-padding-remove uk-button-default">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <template v-if="!c.status && !c.remove_status">
                                <button v-if="typeUser == 'pdc' && $route.params.state == 2" uk-tooltip="Confirmer" class="uk-border-rounded uk-padding-remove uk-button-primary">
                                    <router-link :to="'/pdraf/command/confirmation/'+c.id">
                                        <i style="color : #fff" class="material-icons">check</i>
                                    </router-link>
                                </button>
                                <button @click="deleteCommandAfrocashRequest(c.id)" v-if="typeUser == 'pdc' && $route.params.state == 2" uk-tooltip="Annuler" class="uk-border-rounded uk-padding-remove uk-button-danger">
                                    <i class="material-icons">delete</i>
                                </button>
                            </template>
                            <button v-if="typeUser == 'v_standart' && $route.params.state == 1" uk-tooltip="Confirmer" class="uk-border-rounded uk-padding-remove uk-button-primary">
                                <router-link :to="'/pdc/command/confirmation/'+c.id">
                                    <i style="color : #fff" class="material-icons">check</i>
                                </router-link>
                            </button>
                            
                            <button @click="deleteCommandAfrocashRequest(c.id)" v-if="typeUser == 'v_standart' && $route.params.state == 1" uk-tooltip="Annuler" class="uk-border-rounded uk-padding-remove uk-button-danger">
                                <i class="material-icons">delete</i>
                            </button>
                            <!-- <button @click="deleteCommandAfrocashRequest(c.id)" v-if="typeUser == 'pdc' && $route.params.state == 2" uk-tooltip="Annuler" class="uk-border-rounded uk-padding-remove uk-button-danger">
                                <i class="material-icons">delete</i>
                            </button> -->
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

            if(this.typeUser == 'pdraf')  {
                this.newCommandUrl = '/pdraf/command/new'
            }
            else {
                this.newCommandUrl = '/pdc/command/new'
            }
        },
        data() {
            return {
                list : [],
                isLoading : false,
                fullPage : true,
                newCommandUrl : "",

                // paginate
                nextUrl : "",
                lastUrl : "",
                perPage : "",
                currentPage : 1,
                firstPage : "",
                firstItem : 1,
                total : 0,
        // #####
                filterData : {
                    user : "none",
                    status : "instance",
                    livraison : "instance"
                },
                pdrafUser : []
            }
        },
        watch : {
            '$route' : 'getData'
        },
        methods : {
            paginateFunction : async function (url) {
                try {
                
                    let response = await axios.get(url)
                    if(response && response.data) {
                        
                        this.list = response.data.all

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
            showConfirmCode : function (code) {
                try {
                    alert("Code de confirmation : "+code)
                }
                catch(error) {
                    alert(error)
                }
            },
            filterRequest : async function () {
                try {
                    let response = await axios.get('/pdc/command/'+this.$route.params.state+'/filter/'+
                        this.filterData.user+'/'+this.filterData.status+'/'+this.filterData.livraison)

                    if(response) {
                        this.list = response.data.all

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
            },
            getData : async function (id) {
                try {
                    this.isLoading = true
                    var response = await axios.get('/pdc/command/list/'+this.$route.params.state)
                    

                    if(response) {
                        this.list = response.data.all

                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.currentPage = response.data.current_page
                        this.firstPage = response.data.first_page
                        this.firstItem = response.data.first_item,
                        this.total = response.data.total

                        this.isLoading = false
                    }

                    if(this.typeUser == 'pdc') {
                        
                        response = await axios.get('/user/get-pdraf-list') 
                        if(response) {
                            this.pdrafUser = response.data
                        }
                    }

                }
                catch(error) {
                    alert(error)
                }
            },
            deleteCommandAfrocashRequest : async function (commandId) {
                try {
                    let confirmState = confirm("Vous etes sur de vouloir poursuivre cette action ?")
                    
                    if(!confirmState) {
                        return 0
                    }

                    if(this.typeUser == 'v_standart') {

                        var response = await axios.post('/pdc/command/remove',{
                            _token : this.myToken,
                            id_commande : commandId
                        })
                    }
                    else if(this.typeUser == 'pdc') {
                        var response = await axios.post('/pdraf/command/remove',{
                            _token : this.myToken,
                            id_commande : commandId
                        })
                    }

                    if(response && response.data == 'done') {
                        alert("Success!")
                        Object.assign(this.$data,this.$options.data())
                        this.getData()
                    }
                }
                catch(error) {
                    if(error.response.data.errors) {
                        let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                            // this.errors.push(errorTab[prop][0])
                            alert(errorTab[prop][0])
                        }
                    } else {
                        // this.errors.push(error.response.data)
                        alert(error.response.data)
                    }
                }
            }
        },
        computed : {
            typeUser () {
                return this.$store.state.typeUser
            },
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
