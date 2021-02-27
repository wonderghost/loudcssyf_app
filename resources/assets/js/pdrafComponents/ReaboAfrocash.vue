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
            <li v-if="$route.path == '/reabonnement-afrocash'"><span>Reabonnement Afrocash</span></li>
            <li v-else><span>Recrutement Afrocash</span></li>
        </ul>

        <h3 v-if="$route.path == '/reabonnement-afrocash'" class="uk-margin-top">Reabonnement Afrocash</h3>
        <h3 v-else class="uk-margin-top">Recrutement Afrocash</h3>
        <hr class="uk-divider-small">

        <nav class="" uk-navbar>
            <div class="uk-navbar-left">
                <ul class="uk-navbar-nav">
                    <li class=""><router-link to="/upgrade-afrocash">Upgrade Afrocash</router-link></li>
                    <li><router-link to="/reactivation-materiel">Reactivation Materiel</router-link></li>
                    <li v-if="$route.path == '/recrutement-afrocash'"><router-link to="/reabonnement-afrocash">Reabonnement Afrocash</router-link></li>
                    <li v-else><router-link to="/recrutement-afrocash">Recrutement Afrocash</router-link></li>
                </ul>

            </div>
        </nav>

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-2-3@m">
                <div v-for="(err,index) in errors" :key="index" class="uk-alert-danger uk-margin-small uk-width-1-1@m uk-border-rounded" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p class="uk-text-center">{{err}}</p>
                </div>
                <div class="uk-width-1-1@m uk-alert-info uk-border-rounded uk-box-shadow-hover-small" uk-alert>

                    <p>
                        (*) Champs obligatoires
                    </p>
                </div>
                <form @submit.prevent="sendReaboAfrocash()" class="uk-grid-small" uk-grid>
                    <template v-if="$route.path == '/recrutement-afrocash'">
                        <div class="uk-width-1-1@m">
                            <h3>Infos Client</h3>
                        </div>
                        <div class="uk-width-1-6@m">
                            <label for="">Titre*</label>
                            <select v-model="dataForm.titre" class="uk-select uk-border-rounded">
                                <option value="Mr">Mr</option>
                                <option value="Mme">Mme</option>
                                <option value="Mlle">Mlle</option>
                            </select>
                        </div>
                        <div class="uk-width-1-6@m">
                            <label for="">Pays</label>
                            <span class="uk-input uk-border-rounded">122</span>
                        </div>
                        <div class="uk-width-1-3@m">
                            <label for="">Nom*</label>
                            <input v-model="dataForm.nom" type="text" class="uk-input uk-border-rounded" placeholder="Nom du client">
                        </div>
                        <div class="uk-width-1-3@m">
                            <label for="">Prenom*</label>
                            <input v-model="dataForm.prenom" type="text" class="uk-input uk-border-rounded" placeholder="Prenom du client">
                        </div>
                        <div class="uk-width-1-3@m">
                            <label for="">Ville/Quartier*</label>
                            <input v-model="dataForm.ville" type="text" class="uk-input uk-border-rounded" placeholder="Ville ou Quartier du client (ex : Conakry)">
                        </div>
                        <div class="uk-width-1-3@m">
                            <label for="">Adresse Postale</label>
                            <input v-model="dataForm.adresse_postal" type="text" class="uk-input uk-border-rounded" placeholder="Adresse Postale du client (ex : BP 1433)">
                        </div>
                        <div class="uk-width-1-3@m">
                            <label for="">Email</label>
                            <input v-model="dataForm.email" type="text" class="uk-input uk-border-rounded" placeholder="Adresse Email du client (ex : xyz@gmail.com)">
                        </div>
                        
                        <!-- INFOS TECHNICIENS -->
                        <div class="uk-width-1-3@m">
                            <label for="">Technicien *</label>
                            <select v-model="dataForm.technicien" class="uk-select uk-border-rounded">
                                <option value="">-- Choisissez le Technicien --</option>
                                <option :value="tech.username" v-for="(tech,index) in technicienList" :key="index">{{ tech.nom }} {{ tech.prenom}} {{ tech.phone }} {{tech.localisation}} ({{ tech.service_plus_id}})</option>
                            </select>
                        </div>
                        <div class="uk-width-1-1@m">
                            <hr class="uk-divider-small">
                        </div>
                    </template>

                    <div class="uk-width-1-1@m">
                        <h3>Infos Materiel</h3>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Numero Materiel* (<span>{{dataForm.serial_number.length}}</span>)</label>
                        <input v-model="dataForm.serial_number" type="text" class="uk-input uk-border-rounded" placeholder="Numero du materiel">
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Formule*</label>
                        <select @change="calculMontantTtc()" v-model="dataForm.formule" class="uk-select uk-border-rounded">
                            <option value="">-- Selectionnez une formule --</option>
                            <option :data-prix="f.prix" :value="f.nom" v-for="(f,index) in formuleList" :key="index">{{ f.title }}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Duree*</label>
                        <select @change="calculMontantTtc()" v-model="dataForm.duree" class="uk-select uk-border-rounded">
                            <option :value="d" v-for="(d,index) in duree" :key="index">{{d}}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Option</label>
                        <select @change="calculMontantTtc()" v-model="dataForm.options" class="uk-select uk-border-rounded" multiple>
                            <option value="">-- Choix des options --</option>
                            <option :data-prix="o.prix" :value="o.nom" v-for="(o,index) in optionsList" :key="index">{{ o.nom }}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Telephone Client*</label>
                        <input v-model="dataForm.telephone_client" type="text" class="uk-input uk-border-rounded" placeholder="Numero de telephone du client">
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Montant Ttc</label>
                        <span class="uk-input uk-border-rounded">{{dataForm.montant_ttc | numFormat }}</span>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Comission</label>
                        <span class="uk-input uk-border-rounded">{{dataForm.comission | numFormat }}</span>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Confirmez votre mot de passe*</label>
                        <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez le mot de passe">
                    </div>
                    <div class="uk-width-1-1@m">
                        <button type="submit" class="uk-button uk-button-primary uk-width-1-1@s uk-width-1-4@m uk-width-1-6@l uk-button-small uk-border-rounded">Envoyez</button>
                    </div>
                </form>
            </div>
            <div class="uk-width-1-3@m">
                <div class="uk-card uk-card-default uk-border-rounded" style="box-shadow : none !important ; border : solid 1px #ddd !important;" uk-sticky="offset : 100">
                    <div class="uk-card-header">
                        <h3 class="uk-card-title">SOLDE AFROCASH (GNF)</h3>
                    </div>
                    <div class="uk-card-body uk-text-center">
                        <span class=" uk-card-title">{{userAccount.solde | numFormat}}</span>
                    </div>
                </div>  
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
            requestUrl : "",
            pourcentComission : 0,
            isLoading : false,
            fullPage : true,
            dataForm : {
                _token : "",
                serial_number : "",
                formule : "",
                duree : 1,
                options : [],
                telephone_client : "",
                montant_ttc : 0,
                comission : 0,
                password_confirmation : "",
                titre : "Mr",
                nom : "",
                prenom : "",
                ville : "",
                adresse_postal : "",
                email : "",
                technicien : ""
                
            },
            duree : [1,2,3,6,9,12,24],
            formuleList : [],
            optionsList : [],
            actifOption : "",
            errors : [],
            userAccount : {},
            technicienList : []
        }
    },
    watch : {
        '$route' : 'getInfos'
    },
    methods : {
        sendReaboAfrocash : async function () {
            try {
                this.isLoading = true
                this.dataForm._token = this.myToken
                let response = await axios.post(this.requestUrl,this.dataForm)
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
        },
        calculMontantTtc : function () {
            try {
                var ttc = 0
                var comission = 0
                if(this.actifFormuleInfo[0]) {
                    ttc = (this.actifFormuleInfo[0].prix + this.totalOption()) * this.dataForm.duree
                    comission = Math.round((ttc/1.18) * (this.pourcentComission/100))
                }
                
                this.dataForm.montant_ttc = ttc
                this.dataForm.comission = comission
                

            } catch(error) {
                alert(error)
            }
        },
        totalOption : function () {
            try {
                var total = 0

                if(this.dataForm.options.length > 0) {

                    this.dataForm.options.forEach((o) => {
                        if(o != "") {

                            this.actifOption = o
                            total += this.actifOptionInfos[0].prix
                        }
                    })
                    
                    return total
                }

                return 0


            } catch(error) {
                alert(error)
            }
        },         
        getInfos : async function () {
            try {
                Object.assign(this.$data,this.$options.data())
                this.isLoading = true
                var response = await axios.get('/user/formule/list')
                
                if(response) {
                    this.formuleList = response.data.formules
                    this.optionsList = response.data.options
                }

                response = await axios.get('/user/pdraf/get-solde')
                if(response) {
                    this.userAccount = response.data
                    this.isLoading = false
                }

                // recuperer la liste de tous les techniciens
                response = await axios.get('/user/technicien-list')

                if(response) {
                    this.technicienList = response.data
                    this.isLoading = false
                }

                if(this.$route.path == '/reabonnement-afrocash') {
                    this.requestUrl = "/user/pdraf/send-reabo-afrocash"
                    this.pourcentComission = 5.5
                }
                else if(this.$route.path == '/recrutement-afrocash') {
                    this.requestUrl = "/user/pdraf/send-recrutement-afrocash"
                    this.pourcentComission = 3
                }

            } catch(error) {
                alert(error)
            }
        }  
    },
    computed : {
        actifOptionInfos () {
            return this.optionsList.filter((o) => {
                return o.nom == this.actifOption
            })
        },
        actifFormuleInfo() {
            return this.formuleList.filter((f) => {
                return f.nom == this.dataForm.formule
            })
        },
        myToken() {
            return this.$store.state.myToken
        }
    }
}
</script>
