<template>
   <div>
       <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

       <div class="uk-container uk-container-large">
           <h3 class="uk-margin-top">Tous les Reabonnements ({{reaboCount}}) <a @click="getAllData()" class="uk-button" uk-tooltip="Rafraichir la liste"><span uk-icon="refresh"></span></a></h3>
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
                        <p class="">
                            La Comission allant du : 
                            <span v-if="typeUser == 'pdc' && pdcListFirst" class="uk-text-bold">{{pdcListFirst.created_at}}</span>
                            <span v-if="typeUser == 'pdraf' && pdrafFirst" class="uk-text-bold">{{pdrafFirst.created_at}}</span>
                             , 
                            au : 
                            <span v-if="typeUser == 'pdc' && pdcListLast" class="uk-text-bold">{{pdcListLast.created_at}}</span>
                            <span v-if="typeUser == 'pdraf' && pdrafLast" class="uk-text-bold">{{pdrafLast.created_at}}</span>
                             ,
                             d'un montant de :
                            <span v-if="typeUser == 'pdc'" class="uk-text-bold">{{totalCom.total | numFormat}} GNF</span> 
                            <span v-if="typeUser == 'pdraf'" class="uk-text-bold">{{totalComissionPdraf | numFormat}} GNF</span>
                        </p>
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
            <template v-if="typeUser == 'admin' || typeUser == 'commercial' || typeUser == 'gcga'">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-grid-small uk-width-1-2@m" uk-grid>
                        <div class="uk-width-1-4@m">
                            <label for=""><span uk-icon="users"></span> Utilisateur</label>
                            <select v-model="filterPdraf" class="uk-select uk-border-rounded">
                                <option value="">Tous</option>
                                <option v-for="(p,index) in pdrafList" :key="index" :value="p.user.username">{{p.user.localisation}}</option>
                            </select>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Paiement</label>
                            <select v-model="filterPayStatement" class="uk-select uk-border-rounded">
                                <option value="">Tous</option>
                                <option value="payer">payer</option>
                                <option value="impayer">impayer</option>
                            </select>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Etat</label>
                            <select v-model="filterEtat" class="uk-select uk-border-rounded">
                                <option value="">Tous</option>
                                <option value="confirme">confirme</option>
                                <option value="annule">annule</option>
                                <option value="en_instance">en instance</option>
                            </select>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Marge</label>
                            <select v-model="filterMargeStatement" class="uk-select uk-border-rounded">
                                <option value="">Tous</option>
                                <option value="payer">payer</option>
                                <option value="impayer">impayer</option>
                            </select>
                        </div>
                    </div>
                    <div class="uk-grid-small uk-margin-remove uk-width-1-2@m" uk-grid>
                        <div class="uk-width-1-4@m">
                            <label for="">Total Comission Pdraf</label>
                            <span class="uk-input uk-border-rounded">{{totalCom.com | numFormat }}</span>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Total Marge</label>
                            <span class="uk-input uk-border-rounded">{{totalCom.mar | numFormat }}</span>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for=""><span uk-icon="credit-card"></span> Comission Total</label>
                            <span class="uk-input uk-border-rounded uk-text-center">{{ totalCom.total | numFormat }}</span>
                        </div>
                    </div>
                </div>
            </template>
            <!-- // -->
            <template v-if="typeUser == 'pdc'">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-6@m">
                        <label for=""><span uk-icon="users"></span> Utilisateur</label>
                        <select v-model="filterPdraf" class="uk-select uk-border-rounded">
                            <option value="">Tous</option>
                            <option v-for="(p,index) in pdrafList" :key="index" :value="p.user.username">{{p.user.localisation}}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Paiement</label>
                        <select v-model="filterPayStatement" class="uk-select uk-border-rounded">
                            <option value="">Tous</option>
                            <option value="payer">payer</option>
                            <option value="impayer">impayer</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Etat</label>
                        <select v-model="filterEtat" class="uk-select uk-border-rounded">
                            <option value="">Tous</option>
                            <option value="confirme">confirme</option>
                            <option value="annule">annule</option>
                            <option value="en_instance">en instance</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Total Comission Pdraf</label>
                        <span class="uk-input uk-border-rounded">{{totalCom.com | numFormat }}</span>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Total Marge</label>
                        <span class="uk-input uk-border-rounded">{{totalCom.mar | numFormat }}</span>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for=""><span uk-icon="credit-card"></span> Comission Total</label>
                        <span class="uk-input uk-border-rounded uk-text-center">{{ totalCom.total | numFormat }}</span>
                        <span v-if="filterPdraf == '' && filterEtat == 'confirme' && filterPayStatement == 'impayer'">
                            <button uk-toggle="target : #modal-pay-comission " class="uk-button uk-text-capitalize uk-button-small uk-button-primary uk-border-rounded">se faire payer</button>
                        </span>
                    </div>
                </div>
            </template>
            <template v-if="typeUser == 'pdraf'">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-6@m">
                        <label for="">Comission Total (GNF)</label>
                        <span class="uk-input uk-text-center uk-border-rounded">{{totalComissionPdraf | numFormat}}</span>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Paiement</label>
                        <select v-model="filterPayStatement" class="uk-select uk-border-rounded">
                            <option value="">Tous</option>
                            <option value="payer">payer</option>
                            <option value="impayer">impayer</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Etat</label>
                        <select v-model="filterEtat" class="uk-select uk-border-rounded">
                            <option value="">Tous</option>
                            <option value="confirme">confirme</option>
                            <option value="annule">annule</option>
                        </select>
                        <span v-if="filterEtat == 'confirme' && filterPayStatement == 'impayer'">
                            <button uk-toggle="target : #modal-pay-comission " class="uk-button uk-text-capitalize uk-button-small uk-text-small uk-button-primary uk-border-rounded">se faire payer</button>
                        </span>
                    </div>
                </div>
            </template>
            
            
            <table class="uk-table uk-table-small uk-table-middle uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Materiel</th>
                        <th>Formule</th>
                        <th>Duree</th>
                        <th>Option</th>
                        <th>Montant Ttc</th>
                        <template v-if="typeUser != 'admin' && typeUser != 'gcga' && typeUser != 'commercial'">
                            <th>Comission</th>
                            <th>Telephone Client</th>
                        </template>
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
                        <tr v-for="(r,index) in listForMagePayState" :key="index">
                            <td>{{r.created_at}}</td>
                            <td>{{r.materiel}}</td>
                            <td>{{r.formule}}</td>
                            <td>{{r.duree}}</td>
                            <td>{{r.option}}</td>
                            <td>{{r.montant |numFormat}}</td>
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
                                    <span class="uk-alert-primary">en instance</span>
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
                                    <button @click="confirmRequestForAdmin(r)" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-text-small uk-text-capitalize">confirmer</button>
                                    <button @click="removeRequestForAdmin(r)" class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-text-small uk-text-capitalize">annuler</button>
                                </template>
                            </td>
                        </tr>
                    </template>
                    <template v-if="typeUser == 'pdc'">
                        <tr v-for="(r,index) in pdcListPayStateReabo" :key="index">
                            <td>{{r.created_at}}</td>
                            <td>{{r.materiel}}</td>
                            <td>{{r.formule}}</td>
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
                                    <span class="uk-alert-primary">en instance</span>
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
                        <tr v-for="(r,index) in pdrafListPayStateReabo" :key="index">
                            <td>{{r.created_at}}</td>
                            <td>{{r.materiel}}</td>
                            <td>{{r.formule}}</td>
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
                                    <span class="uk-alert-primary">en instance</span>
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
            this.getAllData()
        },
        data() {
            return {
                all : [],
                isLoading : false,
                fullPage : true,
                pdrafList : [],
                filterPdraf : "",
                password_confirmation : "",
                errors : [],
                filterEtat : "",
                filterPayStatement : "",
                filterMargeStatement : ""
            }
        },
        methods : {
            removeRequestForAdmin : async function(r) {
                try {
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
                try {

                    UIkit.modal($("#modal-pay-comission")).hide()
                    this.isLoading = true

                    var url = ""

                    if(this.typeUser == 'pdc') {
                        url = '/user/pdc/pay-comission-request'
                        var response = await axios.post(url,{
                            _token : this.myToken,
                            password_confirmation : this.password_confirmation,
                            montant : Math.round(this.totalCom.total),
                            debut : this.pdcListFirst.created_at,
                            fin : this.pdcListLast.created_at,
                        })
                    } 
                    else if(this.typeUser == 'pdraf') {
                        url = '/user/pdraf/pay-comission-request'
                        var response = await axios.post(url,{
                            _token : this.myToken,
                            password_confirmation : this.password_confirmation,
                            montant : Math.round(this.totalComissionPdraf),
                            debut : this.pdrafFirst.created_at,
                            fin : this.pdrafLast.created_at,
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
            getAllData : async function () {
                try {
                    this.isLoading = true
                    var response = await axios.get('/user/pdraf/get-reabo-afrocash')
                    if(response) {
                        this.isLoading = false
                        this.all = response.data
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
                        if(response) {
                            this.isLoading = false
                            this.pdrafList = response.data
                        }
                    }


                } catch(error) {
                    alert(error)
                    console.log(error)
                }
            }
        },
        computed : {
            totalComissionPdraf() {
                var som = 0
                this.pdrafListPayStateReabo.forEach(r => {
                    som += r.comission
                })
                return som
            },
            totalCom() {
                var som = 0
                var sum = 0
                var tot = 0
                if(this.typeUser == 'pdc') {

                    this.pdcListPayStateReabo.forEach(r => {
                        som += r.total
                        sum += r.comission
                        tot += r.marge
                    })
                }
                else if(this.typeUser == 'admin' || this.typeUser == 'commercial' || this.typeUser == 'gcga') {
                    this.listForMagePayState.forEach(r => {
                        som += r.total
                        // sum += r.comission
                        tot += r.marge
                    })

                    this.listForPayStateReabo.forEach( r => {
                        sum += r.comission
                    })
                }
                return {
                    total : som,
                    com : sum ,
                    mar : tot
                }
            },
            listForMagePayState() {
                 return this.listForPayStateReabo.filter((l) => {
                    if(this.filterMargeStatement == "") {
                        return l
                    }
                    else if(this.filterMargeStatement == "payer") {
                        return l.pay_comission_id != null
                    }
                    else {
                        return l.pay_comission_id == null
                    }
                })
            },
            listForPayStateReabo() {
                return this.listForAllbyEtat.filter((l) => {
                    if(this.filterPayStatement == "") {
                        return l
                    }
                    else if(this.filterPayStatement == "payer") {
                        return l.pay_at != null
                    }
                    else {
                        return l.pay_at == null
                    }
                })
            },
            listForAllbyEtat() {
                return this.listForAll.filter((l) => {
                    if(this.filterEtat == 'confirme') {
                        return l.confirm_at != null
                    }
                    else if(this.filterEtat == 'annule') {
                        return l.remove_at != null
                    }
                    else if(this.filterEtat == 'en_instance') {
                        return (l.confirm_at == null && l.remove_at == null)
                    }
                    else {
                        return l
                    }
                })
            },
            listForAll() {
                if(this.filterPdraf != "") {

                    return this.all.filter((r) => {
                        return r.pdraf.username == this.filterPdraf
                    })
                }
                else {
                    return this.all
                }
            },
            pdcListLast() {
                return this.pdcListReaboByPdraf[this.pdcListReaboByPdraf.length - 1]
            },
            pdcListFirst() {
                return this.pdcListReaboByPdraf[0]
            },
            pdcListPayStateReabo() {
                return this.pdcListEtatReabo.filter((l) => {
                    if(this.filterPayStatement == "") {
                        return l
                    }
                    else if(this.filterPayStatement == "payer") {
                        return l.pay_comission_id != null
                    }
                    else {
                        return l.pay_comission_id == null
                    }
                })
            },
            pdcListEtatReabo() {
                return this.pdcListReaboByPdraf.filter((l) => {
                    if(this.filterEtat == 'confirme') {
                        return l.confirm_at != null
                    }
                    else if(this.filterEtat == 'annule') {
                        return l.remove_at != null
                    }
                    else if(this.filterEtat == 'en_instance') {
                        return (l.confirm_at == null && l.remove_at == null)
                    }
                    else {
                        return l
                    }
                })
            },
            pdcListReaboByPdraf() {
                return this.pdcListReabo.filter((l) => {
                    return l.pdraf.username.match(this.filterPdraf)
                })
            },
            pdrafFirst() {
                return this.pdrafListEtatReabo[0]
            },
            pdrafLast() {
                return this.pdrafListEtatReabo[this.pdrafListEtatReabo.length - 1]
            },
            pdrafListPayStateReabo() {
                return this.pdrafListEtatReabo.filter((l) => {
                    if(this.filterPayStatement == "") {
                        return l
                    }
                    else if(this.filterPayStatement == "payer") {
                        return l.pay_at != null
                    }
                    else {
                        return l.pay_at == null
                    }
                })
            },
            pdrafListEtatReabo() {
                return this.pdrafListReabo.filter((l) => {
                    if(this.filterEtat == 'confirme') {
                        return (l.confirm_at != null)
                    }
                    else if(this.filterEtat == 'annule'){
                        return l.remove_at != null
                    }
                    else {
                        return l
                    }
                })
            },
            pdrafListReabo() {
                return this.all.filter((l) => {
                    return l.pdraf.username == this.userName
                })
            },
            pdcListReabo() {
                return this.all.filter((l) => {
                    return l.pdc_hote.username == this.userName
                })
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
            reaboCount() {
                if(this.typeUser == 'pdc' ) {
                    return this.pdcListPayStateReabo.length
                }
                else if(this.typeUser == 'pdraf') {
                    return this.pdrafListPayStateReabo.length
                }
                else {
                    return this.listForPayStateReabo.length
                }
            }

        }
    }
</script>
