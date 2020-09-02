<template>
   <div>
       <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#fff"
        background-color="#083050"></loading>

       <div class="uk-container uk-container-large">
           <h3 class="uk-margin-top">Operation Afrocash</h3>
           <hr class="uk-divider-small">
           <div class="uk-grid-small uk-grid-divider" uk-grid>

            <!-- FORMULAIRE DDE DEPOT PDC -> PDRAF -->
                <div class="uk-width-1-3@m">
                    <div v-for="e in errors" :key="e" class="uk-alert-danger" uk-alert>
                        <a href="#" class="uk-alert-close" uk-close></a>
                        <p>
                            {{e}}
                        </p>
                    </div>
                    <form @submit.prevent="sendTransaction()" class="uk-width-1-1@m">
                        <div class="uk-width-1-1@m ">
                            <label for="">PDRAF *</label>
                            <select v-model="dataForm.pdraf_id" class="uk-select uk-border-rounded">
                                <option value="">-- Point de Recharge Afrocash --</option>
                                <option :value="u.user.username" v-for="u in pdrafUsers" :key="u.user.username">{{u.user.localisation}}</option>
                            </select>
                        </div>
                        <div class="uk-width-1-1@m">                
                            <label for="">Montant *</label>
                            <input v-model="dataForm.montant" type="number" class="uk-input uk-border-rounded" placeholder="Montant de la transaction">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Confirmez le mot de passe *</label>
                            <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe pour confirmer la transaction">
                        </div>
                        <div class="uk-margin-top">
                            <button class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
                        </div>
                    </form>
                </div>
            <!-- // -->
            <!-- SOLDE AFROCASH -->
                <div class="uk-width-1-3@m">
                    <div class="uk-card uk-card-default uk-border-rounded" style="box-shadow : none !important ; border : solid 1px #ddd !important;">
                        <div class="uk-card-header">
                            <h3 class="uk-card-title">SOLDE AFROCASH (GNF)</h3>
                        </div>
                        <div class="uk-card-body uk-text-center">
                            <span class=" uk-card-title">{{pdcAccount.solde | numFormat}}</span>
                        </div>
                    </div>                
                </div>
            <!-- // -->
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
            UIkit.offcanvas($("#side-nav")).hide();
            this.getPdrafList()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                pdrafUsers : [],
                dataForm : {
                    _token : "",
                    pdraf_id : "",
                    montant : 0,
                    password_confirmation :""
                },
                errors : [],
                pdcAccount : {}
            }
        },
        methods : {
            getPdrafList : async function () {
                try {
                    this.isLoading = true
                    
                    let response = await axios.get('/user/get-pdraf-list')
                    this.pdrafUsers = response.data
                    
                    response = await axios.get('/user/pdc/get-solde')
                    this.pdcAccount = response.data

                    this.isLoading = false

                } catch(error) {
                    console.log(error)
                }
            },
            
            sendTransaction : async function () {
                try {
                    this.isLoading = true
                    this.dataForm._token = this.myToken
                    let response = await axios.post('/user/pdc/send-transaction',this.dataForm)
                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Success!")
                        Object.assign(this.$data,this.$options.data())
                        this.getPdrafList()
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
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
