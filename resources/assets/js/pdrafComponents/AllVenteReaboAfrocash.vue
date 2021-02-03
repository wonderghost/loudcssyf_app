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
            <li v-if="$route.path == '/all-ventes-pdraf'"><span>Reabonnement Afrocash</span></li>
            <li v-else><span>Recrutement Afrocash</span></li>
        </ul>

        <h3 v-if="$route.path == '/all-ventes-pdraf'">Tous les Reabonnements</h3>
        <h3 v-else >Tous les Recrutements</h3>
        <hr class="uk-divider-small">

        <nav class="" uk-navbar>
            <div class="uk-navbar-left">
                <ul class="uk-navbar-nav">
                    <li v-if="$route.path == '/all-ventes-pdraf'" ><router-link to="/all-ventes-pdraf/recrutement-afrocash">Recrutements</router-link></li>
                    <li v-else><router-link to="/all-ventes-pdraf">Reabonnements</router-link></li>
                </ul>
            </div>
        </nav>
       
        <download-to-excel :data-to-export="all" :data-fields="field_export" file-name="reabonnement-afrocash"></download-to-excel>        
        <!-- // -->
        <!-- ADMIN -->
        <template>
            <template v-if="typeUser == 'admin' || typeUser == 'commercial' || typeUser == 'gcga' || typeUser == 'pdc'">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-grid-small uk-width-1-2@m" uk-grid>
                        <template v-if="typeUser != 'pdc'">
                            <div class="uk-width-1-4@m">
                                <label for="">PDC</label>
                                <select @change="filterRequest()" v-model="filterData.pdc" class="uk-select uk-border-rounded">
                                    <option value="all">Tous</option>
                                    <option v-for="(p,index) in pdcList" :key="index" :value="p.user.username">{{p.user.localisation}}</option>
                                </select>
                            </div>
                        </template>
                        <div class="uk-width-1-4@m">
                            <label for=""><span uk-icon="users"></span> Utilisateur</label>
                            <vue-single-select
                                v-model="userText"
                                :options="userList"
                                :max-results="500"
                                :required="true">
                            </vue-single-select>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Paiement</label>
                            <select @change="filterRequest()" v-model="filterData.payState" class="uk-select uk-border-rounded">
                                <option value="all">Tous</option>
                                <option value="payer">payer</option>
                                <option value="impayer">impayer</option>
                            </select>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Etat</label>
                            <select @change="filterRequest()" v-model="filterData.state" class="uk-select uk-border-rounded">
                                <option value="all">Tous</option>
                                <option value="confirme">confirme</option>
                                <option value="annule">annule</option>
                                <option value="en_instance">en instance</option>
                            </select>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Marge</label>
                            <select @change="filterRequest()" v-model="filterData.margeState" class="uk-select uk-border-rounded">
                                <option value="all">Tous</option>
                                <option value="payer">payer</option>
                                <option value="impayer">impayer</option>
                            </select>
                        </div>
                    </div>
                    <div class="uk-grid-small uk-margin-remove uk-width-1-2@m" uk-grid>
                        <div class="uk-width-1-4@m">
                            <label for="">Total Comission Pdraf</label>
                            <span class="uk-input uk-text-center uk-border-rounded">{{comission | numFormat}}</span>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Total Marge</label>
                            <span class="uk-input uk-text-center uk-border-rounded">{{marge | numFormat}}</span>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for=""><span uk-icon="credit-card"></span> Comission Total</label>
                            <span class="uk-input uk-border-rounded uk-text-center">{{comission + marge | numFormat}}</span>
                            
                        </div>
                        
                    </div>
                </div>
            </template>
        </template>
        <!-- // -->
        <template>
            <template v-if="typeUser == 'pdraf'">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-6@m">
                        <label for="">Comission Total (GNF)</label>
                        <span class="uk-input uk-border-rounded uk-text-center">{{comission | numFormat}}</span>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Paiement</label>
                        <select @change="filterRequest()" v-model="filterData.payState" class="uk-select uk-border-rounded">
                            <option value="all">Tous</option>
                            <option value="payer">payer</option>
                            <option value="impayer">impayer</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Etat</label>
                        <select @change="filterRequest()" v-model="filterData.state" class="uk-select uk-border-rounded">
                            <option value="all">Tous</option>
                            <option value="confirme">confirme</option>
                            <option value="annule">annule</option>
                        </select>
                    </div>
                </div>
            </template>
        </template>
        <div class="uk-grid-small uk-flex uk-flex-right" uk-grid>
            <!-- paginate component -->
            <div class="uk-width-1-3@m uk-margin-top">
                <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
                <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
                <button @click="getAllData()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
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
        <table class="uk-table uk-table-small uk-table-middle uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Materiel</th>
                    <th>Formule</th>
                    <th>Duree</th>
                    <th>Option</th>
                    <th>Montant Ttc</th>
                    <template v-if="typeUser != 'admin' && typeUser != 'gcga' && typeUser != 'commercial'">
                        <th>Comission</th>
                    </template>
                    <th>Telephone Client</th>
                    <th>Pdraf</th>
                    <template v-if="typeUser == 'pdc' || typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'commercial'">
                        <th>Marge</th>
                        <th>Total</th>
                    </template>
                    <th>Etat</th>
                    <th>Paiement</th>
                    <!-- <template v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'commercial'"> -->
                    <th cols="3">-</th>
                    <!-- </template> -->
                </tr>
            </thead>
            <tbody>
                <template v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'commercial'">
                    <tr v-for="(r,index) in all" :key="index">
                        <td>{{r.created_at}}</td>
                        <td>{{r.hour}}</td>
                        <td>{{r.materiel}}</td>
                        <template v-if="r.upgrade_state == null">
                            <td>{{r.formule}}</td>
                        </template>
                        <template v-else>
                            <td class="uk-alert uk-alert-primary uk-text-bold">{{r.upgrade_state.from_formule}} -> {{r.formule}} (UPGRADE)</td>
                        </template>
                        <td>{{r.duree}}</td>
                        <td>{{r.option}}</td>
                        <td>{{r.montant |numFormat}}</td>
                        <td>{{r.telephone_client}}</td>
                        <td>{{r.pdraf.localisation}}</td>
                        <td>
                            <template v-if="r.pay_comission_id">
                                <span class="uk-label uk-label-success">{{r.marge | numFormat}}</span>
                            </template>
                            <template v-else>
                                <span class="uk-label uk-label-warning">{{r.marge | numFormat}}</span>
                            </template>

                        </td>
                        <td>{{r.total | numFormat }}</td>
                        <td>
                            <template v-if="r.confirm_at">
                                <span class="uk-label uk-label-success">confirme</span>
                            </template>
                            <template v-else-if="r.remove_at">
                                <span class="uk-label uk-label-danger">annule</span>
                            </template>
                            <template v-else>
                                <span class="uk-label uk-label-primary">instance</span>
                            </template>
                        </td>
                        <td>
                            <template v-if="r.pay_at">
                                <span class="uk-label-success" uk-icon="check"></span>
                            </template>
                            <template v-else>
                                <span class="uk-label-danger" uk-icon="close"></span>
                            </template>
                        </td>
                        <td>
                            <button @click="$router.push('/vente-afrocash/'+r.id)" class="uk-button-default uk-border-rounded" uk-tooltip="Details"><i class="material-icons" style="cursor:pointer">more_vert</i></button>
                        </td>
                        <td>
                            <template v-if="!r.confirm_at && !r.remove_at">
                                <button @click="confirmRequestForAdmin(r)" class="uk-padding-remove uk-button uk-button-primary uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Confirmer"><i class="material-icons" style="font-size : 20px;cursor:pointer">done</i></button>
                            </template>
                        </td>
                        <td>
                            <template v-if="$route.path == '/all-ventes-pdraf'">
                                <template v-if="!r.confirm_at && !r.remove_at">
                                    <button @click="removeRequestForAdmin(r)" class="uk-padding-remove uk-button uk-button-danger uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Annuler"><i class="material-icons" style="font-size : 20px;cursor:pointer">delete</i></button>
                                </template>
                            </template>
                        </td>
                    </tr>
                </template>
                <template v-if="typeUser == 'pdc'">
                    <tr v-for="(r,index) in all" :key="index">
                        <td>{{r.created_at}}</td>
                        <td>{{r.hour}}</td>
                        <td>{{r.materiel}}</td>
                        <template v-if="r.upgrade_state == null">
                            <td>{{r.formule}}</td>
                        </template>
                        <template v-else>
                            <td class="uk-alert uk-alert-primary uk-text-bold">{{r.upgrade_state.from_formule}} -> {{r.formule}} (UPGRADE)</td>
                        </template>
                        <td>{{r.duree}}</td>
                        <td>{{r.option}}</td>
                        <td>{{r.montant |numFormat}}</td>
                        <td>{{r.comission | numFormat}}</td>
                        <td>{{r.telephone_client}}</td>
                        <td>{{r.pdraf.localisation}}</td>
                        <td>{{r.marge | numFormat}}</td>
                        <td>{{r.total | numFormat }}</td>
                        <td>
                            <template v-if="r.confirm_at">
                                <span class="uk-label uk-label-success">confirme</span>
                            </template>
                            <template v-else-if="r.remove_at">
                                <span class="uk-label uk-label-danger">annule</span>
                            </template>
                            <template v-else>
                                <span class="uk-label uk-label-primary">instance</span>
                            </template>
                        </td>
                        <td>
                            <template v-if="r.pay_comission_id">
                                <span class="uk-label uk-label-success" uk-icon="check"></span>
                            </template>
                            <template v-else>
                                <span class="uk-label uk-label-danger" uk-icon="close"></span>
                            </template>
                        </td>
                        <td>
                            <button @click="venteId = r.id" uk-toggle="target : #modal-container" class="uk-padding-remove uk-button uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Details"><i class="material-icons" style="font-size : 20px;cursor:pointer">more_vert</i></button>
                        </td>
                    </tr>
                </template>
                <template v-if="typeUser == 'pdraf'">
                    <tr v-for="(r,index) in all" :key="index">
                        <td>{{r.created_at}}</td>
                        <td>{{r.hour}}</td>
                        <td>{{r.materiel}}</td>
                        <template v-if="r.upgrade_state == null">
                            <td>{{r.formule}}</td>
                        </template>
                        <template v-else>
                            <td class="uk-alert uk-alert-primary uk-text-bold">{{r.upgrade_state.from_formule}} -> {{r.formule}} (UPGRADE)</td>
                        </template>
                        <td>{{r.duree}}</td>
                        <td>{{r.option}}</td>
                        <td>{{r.montant |numFormat}}</td>
                        <td>{{r.comission | numFormat}}</td>
                        <td>{{r.telephone_client}}</td>
                        <td>{{r.pdraf.localisation}}</td>
                        <td>
                            <template v-if="r.confirm_at">
                                <span class="uk-label uk-label-success">confirme</span>
                            </template>
                            <template v-else-if="r.remove_at">
                                <span class="uk-label uk-label-danger">annule</span>
                            </template>
                            <template v-else>
                                <span class="uk-label uk-label-primary">instance</span>
                            </template>
                        </td>
                        <td>
                            <template v-if="r.pay_at">
                                <span class="uk-label uk-label-success" uk-icon="check"></span>
                            </template>
                            <template v-else>
                                <span class="uk-label uk-label-danger" uk-icon="close"></span>
                            </template>
                        </td>
                        <td>
                            <button @click="venteId = r.id" uk-toggle="target : #modal-container" class="uk-padding-remove uk-button uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Details"><i class="material-icons" style="font-size : 20px;cursor:pointer">more_vert</i></button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div class="uk-flex uk-flex-center">
            <button class="uk-button uk-button-small uk-border-rounded" uk-scroll uk-tooltip="revenir en haut"><span uk-icon="triangle-up"></span></button>
        </div>
   </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import detailVente from './DetailReaboAfrocash.vue';
import VueSingleSelect from "vue-single-select";

    export default {
        components : {
            Loading,
            detailVente,
            'vue-single-select' : VueSingleSelect
        },
        mounted () {
            this.getAllData()
            
        },
        data() {
            return {
                venteId : "",
                dataUrl : "",
                field_export : {
                    'Date' : 'created_at',
                    'Heure' : 'hour',
                    'Materiel' : 'materiel',
                    'Formule' : 'formule',
                    'Duree' : 'duree',
                    'Option(s)' : 'option',
                    'Montant Ttc' : 'montant',
                    'Telephone Client' : 'telephone_client',
                    'Point de Recharge' : 'pdraf.localisation',
                    'Marge' : 'marge',
                    'Comission' : 'comission',
                    'Total' : 'total',
                    'Confirmer' : 'confirm_at',
                    'Payer' : 'pay_at'
                },
        // paginate
                nextUrl : "",
                lastUrl : "",
                perPage : "",
                currentPage : 1,
                firstPage : "",
                firstItem : 1,
                total : 0,
        // #####                
                all : [],
                pdcList : [],

                comission : 0,
                marge : 0,
                totalComission : 0,

                isLoading : false,
                fullPage : true,
                pdrafList : [],
                userList : [],
                password_confirmation : "",
                errors : [],
                filterData : {
                    filterState : true,
                    _token : "" , 
                    pdc : "all",
                    user : "all",
                    payState : "all",
                    state : "all",
                    margeState : "all"
                },
                userText : "",
                confirmUrl : "",
                removeUrl : "",
                getComissionUrl : "",
                filterRequestUrl : ""
            }
        },
        watch : {
            '$route' : 'getAllData',
            'filterData.user' : 'filterRequest',
            'userText' : 'filterRequest'
        },
        methods : {
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
            },
            removeRequestForAdmin : async function(r) {
                try {
                    var conf = confirm("Vous etes sur de vouloir annuler ?")
                    if(!conf) {
                        return 0
                    }
                    // this.isLoading = true
                    if(this.$route.path == '/all-ventes-pdraf') {

                        if(this.typeUser == 'admin') {

                            this.removeUrl = "/admin/pdraf/remove-reabo-afrocash"
                        }
                    }
                    
                    var response = await axios.post(this.removeUrl,{
                        _token : this.myToken,
                        id : r.id
                    })

                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Success !")
                        this.getAllData()
                    }

                } catch(error) {
                    this.errors = []
                    if(error.response.data.errors) {
                        let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                            this.errors.push(errorTab[prop][0])
                        }
                    } else {
                        this.errors.push(error.response.data)
                    }
                }
            },
            confirmRequestForAdmin : async function (r) {
                this.errors = []
                try {

                    var conf = confirm("Vous etes sur de vouloir confirmer ?")
                    if(!conf) {
                        return 0
                    }

                    if(this.$route.path == '/all-ventes-pdraf') {
                        if(this.typeUser == 'admin') {
                            this.confirmUrl = '/admin/pdraf/confirm-reabo-afrocash'
                            
                        }
                        else if(this.typeUser == 'gcga') {
                           this.confirmUrl = '/user/pdraf/confirm-reabo-afrocash'
                        }
                        
                    }
                    else if(this.$route.path == '/all-ventes-pdraf/recrutement-afrocash') {

                        if(this.typeUser == 'admin') {
                            this.confirmUrl = '/admin/pdraf/confirm-recrutement-afrocash'
                        }
                        else if(this.typeUser == 'gcga') {
                            this.confirmUrl = '/user/pdraf/confirm-recrutement-afrocash'
                        }

                    }

                    var response = await axios.post(this.confirmUrl,{
                            _token : this.myToken,
                            id : r.id
                        })
                    
                    
                    if(response && response.data) {
                        UIkit.modal.alert("<div class='uk-alert uk-alert-success'>Success</div>")
                        Object.assign(this.$data,this.$options.data())
                        this.getAllData()
                    }
                } catch(error) {
                    
                    if(error.response.data.errors) {
                        let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                            this.errors.push(errorTab[prop][0])
                            alert(errorTab[prop][0])
                        }
                    } else {
                        this.errors.push(error.response.data)
                        UIkit.modal.alert("<div class='uk-alert uk-alert-danger'>"+error.response.data+"</div>")
                    }
                    this.getAllData()
                }
            },
            filterRequest : async function () {
                try {
                    this.isLoading = true
                    this.filterData._token = this.myToken

                    if(this.$route.path == '/all-ventes-pdraf') {

                        this.filterRequestUrl = "/user/pdraf/filter-reabo-afrocash/"
                    }
                    else if(this.$route.path == '/all-ventes-pdraf/recrutement-afrocash') {

                        this.filterRequestUrl = '/user/pdraf/filter-recrutement-afrocash/'
                    }

                    let response = await axios
                        .get(this.filterRequestUrl+this.filterData.pdc+'/'+this.getUsername+'/'+this.filterData.payState+'/'+this.filterData.state+'/'+this.filterData.margeState)

                    if(response) {
                        
                        this.all = response.data.all                        
                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.perPage = response.data.per_page
                        this.firstItem = response.data.first_item
                        this.total = response.data.total

                        this.comission = response.data.comission
                        this.marge = response.data.marge

                        this.isLoading = false

                    }
                }
                catch(error) {
                    alert("Erreur")
                }
            },
            getAllData : async function () {
                try {
                    Object.assign(this.$data,this.$options.data())

                    if(this.$route.path == '/all-ventes-pdraf') {
                        this.dataUrl = '/user/pdraf/get-reabo-afrocash'
                        this.getComissionUrl = '/user/reabo-afrocash/get-comission'
                    }
                    else if(this.$route.path == '/all-ventes-pdraf/recrutement-afrocash') {
                        this.dataUrl = '/user/pdraf/get-recrutement-afrocash'
                        this.getComissionUrl = '/user/recrutement-afrocash/get-comission'
                    }

                    this.isLoading = true
                    var response = await axios.get(this.dataUrl)
                    var theResponse = await axios.get(this.getComissionUrl)


                    if(response && theResponse) {
                        this.isLoading = false
                        this.all = response.data.all
                        
                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.perPage = response.data.per_page
                        this.firstItem = response.data.first_item
                        this.total = response.data.total

                        this.comission = theResponse.data.comission
                        this.marge = theResponse.data.marge

                    }
                    if(this.typeUser == 'pdc') {
                        this.isLoading = true
                        response = await axios.get('/user/get-pdraf-list')

                        if(response) {
                            this.isLoading = false
                            this.pdrafList = response.data
                        }
                    }
                    else if(this.typeUser == 'commercial' || this.typeUser == 'admin' || this.typeUser == 'gcga') {
                        this.isLoading = true
                        response = await axios.get('/user/get-all-pdraf')
                        var myResponse = await axios.get('/user/get-all-pdc')

                        if(response && myResponse) {
                            this.isLoading = false
                            this.pdrafList = response.data
                            this.pdcList = myResponse.data

                        }
                    }
                    this.pdrafList.forEach(p => {
                        this.userList.push(p.user.localisation)
                    })

                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {
            getUsername() {
                if(this.userText != null && this.userText != '') {

                    return this.pdrafList.filter((p) => {
                        return p.user.localisation == this.userText
                    })[0].user.username

                }
                return "all"
            },
            typeUser() {
                return this.$store.state.typeUser
            },
            userName() {
                return this.$store.state.userName
            },
            myToken() {
                return this.$store.state.myToken
            },
        }
    }
</script>
