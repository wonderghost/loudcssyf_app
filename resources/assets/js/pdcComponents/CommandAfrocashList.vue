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

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-1-3@m">
                <button class="uk-button-default uk-border-rounded" @click="getData()">reload</button>
            </div>
        </div>

        <div>
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
                        <td v-if="c.status == 'instance'" class="uk-text-primary">{{c.status}}</td>
                        <td v-if="c.status == 'confirmed'">{{c.status}}</td>
                        <td v-if="c.livraison == 'instance'" class="uk-text-primary">{{c.livraison}}</td>
                        <td>
                            <button @click="showConfirmCode(c.confirm_code)" v-if="typeUser == 'pdc'" uk-tooltip="Code de confirmation" class="uk-border-rounded uk-button-primary uk-padding-remove">
                                <i class="material-icons">preview</i>
                            </button>
                            <button uk-tooltip="Details" class="uk-border-rounded uk-padding-remove uk-button-default">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <button v-if="typeUser == 'v_standart'" uk-tooltip="Confirmer" class="uk-border-rounded uk-padding-remove uk-button-primary">
                                <router-link :to="'/pdc/command/confirmation/'+c.id">
                                    <i style="color : #fff" class="material-icons">check</i>
                                </router-link>
                            </button>
                            <button @click="deleteCommandAfrocashRequest(c.id)" v-if="typeUser == 'v_standart'" uk-tooltip="Annuler" class="uk-border-rounded uk-padding-remove uk-button-danger">
                                <i class="material-icons">delete</i>
                            </button>
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
                newCommandUrl : ""
            }
        },
        watch : {
            '$route' : 'getData'
        },
        methods : {
            showConfirmCode : function (code) {
                try {
                    alert("Code de confirmation : "+code)
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
                        this.isLoading = false
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

                    let response = await axios.post('/pdc/command/remove',{
                        _token : this.myToken,
                        id_commande : commandId
                    })

                    if(response && response.data == 'done') {
                        alert("Success!")
                        Object.assign(this.$data,this.$options.data())
                        this.getData()
                    }
                }
                catch(error) {
                    alert(error)
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
