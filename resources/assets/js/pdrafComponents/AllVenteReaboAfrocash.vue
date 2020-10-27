<template>
   <div>
       <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

       <div class="uk-container uk-container-large">
           <h3 class="uk-margin-top">Tous les Reabonnements</h3>
           <hr class="uk-divider-small">
           

            <!-- MODAL CONFIRM PAIEMENT COMISSION -->
            <div id="modal-pay-comission" uk-modal="esc-close : false ; bg-close : false">
                <div class="uk-modal-dialog">
                    <div class="uk-modal-header">
                        <h2 class="uk-modal-title">Paiement Comission</h2>
                    </div>
                    <div class="uk-modal-body">
                        <div class="uk-alert-danger" uk-alert v-for="(err,index) in errors" :key="index">
                            <a class="uk-alert-close" uk-close></a>
                            <p>{{err}}</p>
                        </div>
                        <p class=""></p>
                        <form @submit.prevent="sendPayComissionRequest()">
                            <div class="uk-margin-small">
                                <label for="">Confirmez votre mot de passe</label>
                                <input v-model="password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez le mot de passe">
                            </div>
                            <button class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
                        </form>
                    </div>
                    <div class="uk-modal-footer uk-text-right">
                        <button class="uk-button uk-button-danger uk-border-rounded uk-modal-close uk-button-small" type="button">Cancel</button>
                    </div>
                </div>
            </div>            
            <!-- // -->
            <!-- ADMIN -->
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
                            <select @change="filterRequest()" v-model="filterData.user" class="uk-select uk-border-rounded">
                                <option value="all">Tous</option>
                                <option v-for="(p,index) in pdrafList" :key="index" :value="p.user.username">{{p.user.localisation}}</option>
                            </select>
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
                            <template v-if="typeUser == 'pdc'">
                                <button uk-toggle="target : #modal-pay-comission" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-button-primary uk-margin-small">se faire payer</button>
                            </template>
                        </div>
                        
                    </div>
                </div>
            </template>
            <!-- // -->
           
            <template v-if="typeUser == 'pdraf'">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-6@m">
                        <label for="">Comission Total (GNF)</label>
                        <span class="uk-input uk-border-rounded uk-text-center">{{comission | numFormat}}</span>
                        <button uk-toggle="target : #modal-pay-comission" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-button-primary uk-margin-small">se faire payer</button>
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
                        <template v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'commercial'">
                            <th>-</th>
                        </template>
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
                                    <span class="uk-alert-success">{{r.marge | numFormat}}</span>
                                </template>
                                <template v-else>
                                    <span class="uk-alert-warning">{{r.marge | numFormat}}</span>
                                </template>

                            </td>
                            <td>{{r.total | numFormat }}</td>
                            <td>
                                <template v-if="r.confirm_at">
                                    <span class="uk-alert-success">confirme</span>
                                </template>
                                <template v-else-if="r.remove_at">
                                    <span class="uk-alert-danger">annule</span>
                                </template>
                                <template v-else>
                                    <span class="uk-alert-primary">instance</span>
                                </template>
                            </td>
                            <td>
                                <template v-if="r.pay_at">
                                    <span class="uk-alert-success" uk-icon="check"></span>
                                </template>
                                <template v-else>
                                    <span class="uk-alert-danger" uk-icon="close"></span>
                                </template>
                            </td>
                            <td>
                                <template v-if="!r.confirm_at && !r.remove_at">
                                    <button @click="confirmRequestForAdmin(r)" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-text-small uk-text-capitalize" uk-tooltip="Confirmer"><span uk-icon="check"></span></button>
                                    <button @click="removeRequestForAdmin(r)" class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-text-small uk-text-capitalize" uk-tooltip="Annuler"><span uk-icon="close"></span></button>
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
                                    <span class="uk-alert-success">confirme</span>
                                </template>
                                <template v-else-if="r.remove_at">
                                    <span class="uk-alert-danger">annule</span>
                                </template>
                                <template v-else>
                                    <span class="uk-alert-primary">instance</span>
                                </template>
                            </td>
                            <td>
                                <template v-if="r.pay_comission_id">
                                    <span class="uk-alert-success" uk-icon="check"></span>
                                </template>
                                <template v-else>
                                    <span class="uk-alert-danger" uk-icon="close"></span>
                                </template>
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
                                    <span class="uk-alert-success">confirme</span>
                                </template>
                                <template v-else-if="r.remove_at">
                                    <span class="uk-alert-danger">annule</span>
                                </template>
                                <template v-else>
                                    <span class="uk-alert-primary">instance</span>
                                </template>
                            </td>
                            <td>
                                <template v-if="r.pay_at">
                                    <span class="uk-alert-success" uk-icon="check"></span>
                                </template>
                                <template v-else>
                                    <span class="uk-alert-danger" uk-icon="close"></span>
                                </template>
                            </td>
                        </tr>
                    </template>
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
        mounted () {
            UIkit.offcanvas($("#side-nav")).hide();
            this.getAllData()
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
                all : [],
                pdcList : [],

                comission : 0,
                marge : 0,
                totalComission : 0,

                isLoading : false,
                fullPage : true,
                pdrafList : [],
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
                }
            }
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
                    this.isLoading = true
                    if(this.typeUser == 'admin') {
                        var response = await axios.post('/admin/pdraf/remove-reabo-afrocash',{
                            _token : this.myToken,
                            id : r.id
                        })
                    }

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
                    
                    if(this.typeUser == 'admin') {

                        var response = await axios.post('/admin/pdraf/confirm-reabo-afrocash',{
                            _token : this.myToken,
                            id : r.id
                        })
                    }
                    else if(this.typeUser == 'gcga') {
                        var response = await axios.post('/user/pdraf/confirm-reabo-afrocash',{
                            _token : this.myToken,
                            id : r.id
                        })
                    }
                    if(response && response.data) {
                        alert("Success!")
                        Object.assign(this.$data,this.$options.data())
                        this.getAllData()
                    }
                } catch(error) {
                    
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
            sendPayComissionRequest : async function () {
                this.errors = []
                try {

                    UIkit.modal($("#modal-pay-comission")).hide()
                    this.isLoading = true

                    var url = ""

                    if(this.typeUser == 'pdc') {
                        url = '/user/pdc/pay-comission-request'
                        var response = await axios.post(url,{
                            _token : this.myToken,
                            password_confirmation : this.password_confirmation,
                            montant : Math.round(this.marge + this.comission),
                        })
                    } 
                    else if(this.typeUser == 'pdraf') {
                        url = '/user/pdraf/pay-comission-request'
                        var response = await axios.post(url,{
                            _token : this.myToken,
                            password_confirmation : this.password_confirmation,
                            montant : Math.round(this.comission),
                        })
                    }
                    
                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Success !")
                        this.getAllData()
                    }
                } catch(error) {
                    UIkit.modal($("#modal-pay-comission")).show()
                    this.isLoading = false
                    
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
            filterRequest : async function () {
                try {
                    this.isLoading = true
                    this.filterData._token = this.myToken

                    let response = await axios
                        .get('/user/pdraf/filter-reabo-afrocash/'+this.filterData.pdc+'/'+this.filterData.user+'/'+this.filterData.payState+'/'+this.filterData.state+'/'+this.filterData.margeState)

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
                    console.log(error)
                }
            },
            getAllData : async function () {
                try {
                    Object.assign(this.$data,this.$options.data())
                    this.isLoading = true
                    var response = await axios.get('/user/pdraf/get-reabo-afrocash')
                    var theResponse = await axios.get('/user/reabo-afrocash/get-comission')

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

                } catch(error) {
                    alert(error)
                    console.log(error)
                }
            }
        },
        computed : {
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
