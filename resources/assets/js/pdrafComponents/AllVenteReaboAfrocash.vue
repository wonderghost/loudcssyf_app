<template>
   <div>
       <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

       <div class="uk-container uk-container-large">
           <h3 class="uk-margin-top">Tous les Reabonnements</h3>
            <hr class="uk-divider-small">

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
                </div>
            </div>
            <table class="uk-table uk-table-small uk-table-striped uk-table-hover uk-table-divider">
                <thead>
                    <tr>
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
                filterPdraf : ""
            }
        },
        methods : {
            getAllData : async function () {
                try {
                    this.isLoading = true
                    var response = await axios.get('/user/pdraf/get-reabo-afrocash')
                    if(response) {
                        this.all = response.data
                    }

                    response = await axios.get('/user/get-pdraf-list')
                    if(response) {
                        this.pdrafList = response.data
                    }

                    this.isLoading = false
                    

                } catch(error) {
                    alert(error)
                    console.log(error)
                }
            }
        },
        computed : {
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
            }
        }
    }
</script>
