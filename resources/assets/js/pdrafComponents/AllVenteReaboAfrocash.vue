<template>
   <div>
       <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

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
                        <p class="">
                            La Comission allant du : <span v-if="pdcListFirst" class="uk-text-bold">{{pdcListFirst.created_at}}</span> , 
                            au : <span v-if="pdcListLast" class="uk-text-bold">{{pdcListLast.created_at}}</span>, d'un montant de :
                            <span class="uk-text-bold">{{totalCom.total | numFormat}} GNF</span> 
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
            <template v-if="typeUser == 'pdc'">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-4@m">
                        <label for=""><span uk-icon="users"></span> Utilisateur</label>
                        <select v-model="filterPdraf" class="uk-select uk-border-rounded">
                            <option value="">Tous</option>
                            <option v-for="(p,index) in pdrafList" :key="index" :value="p.user.username">{{p.user.localisation}}</option>
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
                        <span v-if="filterPdraf == ''">
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
                </div>
            </template>
            
            <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Materiel</th>
                        <th>Formule</th>
                        <th>Duree</th>
                        <th>Option</th>
                        <th>Montant Ttc</th>
                        <th>Comission</th>
                        <th>Telephone Client</th>
                        <th>Pdraf</th>
                        <template v-if="typeUser == 'pdc'">
                            <th>Marge</th>
                            <th>Total</th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="typeUser == 'pdc'">
                        <tr v-for="(r,index) in pdcListReaboByPdraf" :key="index">
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
                        </tr>
                    </template>
                    <template v-if="typeUser == 'pdraf'">
                        <tr v-for="(r,index) in pdrafListReabo" :key="index">
                            <td>{{r.created_at}}</td>
                            <td>{{r.materiel}}</td>
                            <td>{{r.formule}}</td>
                            <td>{{r.duree}}</td>
                            <td>{{r.option}}</td>
                            <td>{{r.montant |numFormat}}</td>
                            <td>{{r.comission | numFormat}}</td>
                            <td>{{r.telephone_client}}</td>
                            <td>{{r.pdraf.localisation}}</td>
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
                errors : []
            }
        },
        methods : {
            sendPayComissionRequest : async function () {
                try {
                    let response = await axios.post('/user/pdc/pay-comission-request',{
                        _token : this.myToken,
                        password_confirmation : this.password_confirmation,
                        montant : Math.round(this.totalCom.total),
                        debut : this.pdcListFirst.created_at,
                        fin : this.pdcListLast.created_at,
                    })

                    if(response) {
                        console.log(response.data)
                    }
                } catch(error) {
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
            getAllData : async function () {
                try {
                    this.isLoading = true
                    var response = await axios.get('/user/pdraf/get-reabo-afrocash')
                    if(response) {
                        this.all = response.data
                    }
                    if(this.typeUser == 'pdc') {
                        response = await axios.get('/user/get-pdraf-list')

                        if(response) {
                            this.pdrafList = response.data
                        }
                    }

                    this.isLoading = false
                    

                } catch(error) {
                    alert(error)
                    console.log(error)
                }
            }
        },
        computed : {
            totalComissionPdraf() {
                var som = 0
                this.pdrafListReabo.forEach(r => {
                    som += r.comission
                })
                return som
            },
            totalCom() {
                var som = 0
                var sum = 0
                var tot = 0
                this.pdcListReaboByPdraf.forEach(r => {
                    som += r.total
                    sum += r.comission
                    tot += r.marge
                })
                return {
                    total : som,
                    com : sum ,
                    mar : tot
                }
            },
            pdcListLast() {
                return this.pdcListReaboByPdraf[this.pdcListReaboByPdraf.length - 1]
            },
            pdcListFirst() {
                return this.pdcListReaboByPdraf[0]
            },
            pdcListReaboByPdraf() {
                return this.pdcListReabo.filter((l) => {
                    return l.pdraf.username.match(this.filterPdraf)
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
            }
        }
    }
</script>
