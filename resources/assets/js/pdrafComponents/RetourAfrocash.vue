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
           <h3 class="uk-margin-top">Retour Afrocash</h3>
           <hr class="uk-divider-small">
           <div class="uk-grid-small uk-grid-divider" uk-grid>
               <div class="uk-width-1-3@m">
                   <div class="uk-alert-danger uk-border-rounded" uk-alert v-for="(err,index) in errors" :key="index">
                       <a class="uk-alert-close" uk-close></a>
                       <p>{{err}}</p>
                   </div>
                   <form @submit.prevent="sendRetourAfrocash()">
                       <div class="uk-width-1-1@m">
                           <label for=""><span uk-icon="icon : user"></span> Point de Controle (PDC)</label>
                           <span class="uk-input uk-border-rounded">{{pdcUser.localisation}}</span>
                       </div>
                       <div class="uk-width-1-1@m">
                           <label for=""><span uk-icon="icon : credit-card"></span> Montant</label>
                           <input v-model="dataForm.montant" type="number" class="uk-input uk-border-rounded" placeholder="Entrez le montant de la transaction">
                       </div>
                       <div class="uk-width-1-1@m">
                           <label for=""><span uk-icon="icon : lock"></span> Confirmez le mot de passe</label>
                           <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez le mot de passe pour confirmation">
                       </div>
                       <div class="uk-margin-small-top">
                           <button type="submit" class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
                       </div>
                   </form>
               </div>
                <!-- SOLDE AFROCASH -->
                <div class="uk-width-1-3@m">
                    <div class="uk-card uk-card-default uk-border-rounded" style="box-shadow : none !important ; border : solid 1px #ddd !important;">
                        <div class="uk-card-header">
                            <h3 class="uk-card-title">SOLDE AFROCASH (GNF)</h3>
                        </div>
                        <div class="uk-card-body uk-text-center">
                            <span class=" uk-card-title">{{userAccount.solde | numFormat}}</span>
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
            this.getInfos()
        },
        data() {
            return {
                pdcUser : {},
                userAccount : {},
                isLoading : false,
                fullPage : true,
                dataForm : {
                    _token : "",
                    pdc_id : "",
                    montant : 0,
                    password_confirmation : ""
                },
                errors : []
            }
        },
        methods : {
            getInfos : async function () {
                try {
                    this.isLoading = true
                    var response = await axios.get('/user/pdraf/retour-afrocash-infos')

                    if(response) {
                        this.pdcUser = response.data
                    }
                    response = await axios.get('/user/pdraf/get-solde')
                    if(response) {
                        this.userAccount = response.data
                    }

                    this.isLoading = false
                } catch(error) {
                    this.isLoading = false
                    alert(error)
                }
            },
            sendRetourAfrocash : async function () {
                try {
                    this.isLoading = true
                    this.dataForm._token = this.myToken
                    this.dataForm.pdc_id = this.pdcUser.username

                    let response = await axios.post('/user/pdraf/send-retour-afrocash',this.dataForm)
                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Success !")
                        Object.assign(this.$data,this.$options.data())
                        this.getInfos()
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
            },
            typeUser() {
                return this.$store.state.typeUser
            },
            userLocalisation() {
                return this.$store.state.userLocalisation
            }
        }
    }
</script>
