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
            <li><router-link to="/reabonnement-afrocash">Reabonnement Afrocash</router-link></li>
            <li><span>Upgrade Afrocash</span></li>
        </ul>

        <h3 class="uk-margin-top">Upgrade Afrocash</h3>
        <hr class="uk-divider-small">

        <div v-for="(err,index) in errors" :key="index" class="uk-alert-danger uk-width-1-2@m uk-border-rounded" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{err}}</p>
        </div>
        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-2-3@m">
                <div class="uk-width-1-1@m uk-alert-info uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                    <p>
                        (*) Champs obligatoires
                    </p>
                </div>
                <form @submit.prevent="sendUpgradeAfrocash()" class="uk-grid-small" uk-grid>
                    <div class="uk-width-1-3@m">
                        <div class="uk-width-1-1@m">
                            <label for="">Numero Materiel* ({{dataForm.serial_number.length}})</label>
                            <input @keyup="checkUpgrade()" @blur="checkUpgrade()" type="text" placeholder="Numero du Materiel" class="uk-input uk-border-rounded" v-model="dataForm.serial_number">
                        </div>
                        <div class="uk-1-1@m">
                            <label for="">Telephone Client</label>
                            <input v-model="dataForm.telephone_client" type="text" class="uk-input uk-border-rounded" placeholder="Telephone Client">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Confirmez le mot de passe</label>
                            <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Confirmez votre mot de passe">
                        </div>
                        
                    </div>
                    <div class="uk-width-2-3@m uk-grid-small" uk-grid>
                        <div class="uk-width-1-2@m">
                            <template v-if="Object.keys(upgradeData).length === 0">
                                <div class="uk-width-1-1@m">
                                    <label for="">Ancienne Formule</label>
                                    <select @change="calculMontantTtc()" v-model="dataForm.old.formule" class="uk-select uk-border-rounded">
                                        <option value="">-- Selectionnez une formule --</option>
                                        <option :data-prix="f.prix" :value="f.nom" v-for="(f,index) in formuleList" :key="index">{{ f.nom }}</option>
                                    </select>
                                </div>
                            </template>
                            <template v-else>
                                <div class="uk-width-1-1@m">
                                    <label for="">Ancienne Formule</label>
                                    <span class="uk-input uk-border-rounded">{{upgradeData.formule_name}}</span>
                                </div>
                            </template>
                        </div>
                        <div class="uk-width-1-2@m">
                            <div class="uk-width-1-1@m">
                                <label for="">Formule</label>
                                <select @change="calculMontantTtc()" v-model="dataForm.new.formule" class="uk-select uk-border-rounded">
                                    <option value="">-- Selectionnez une formule --</option>
                                    <option :data-prix="f.prix" :value="f.nom" v-for="(f,index) in formuleList" :key="index">{{ f.nom }}</option>
                                </select>
                            </div>
                            <div class="uk-width-1-1@m">
                                <label for="">Option</label>
                                <select @change="calculMontantTtc()" v-model="dataForm.new.option" class="uk-select uk-border-rounded" multiple>
                                    <option value="">-- Choix des options --</option>
                                    <option :data-prix="o.prix" :value="o.nom" v-for="(o,index) in optionsList" :key="index">{{ o.nom }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <template v-if="Object.keys(upgradeData).length === 0">
                        <div class="uk-width-1-6@m">
                            <label for="">Duree</label>
                            <select @change="calculMontantTtc()" v-model="dataForm.duree" class="uk-select uk-border-rounded">
                                <option :value="d" v-for="(d,index) in duree" :key="index">{{d}}</option>
                            </select>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Debut</label>
                            <input v-model="dataForm.debut" type="date" class="uk-input uk-border-rounded" placeholder="Debut Abonnement">
                        </div>
                    </template>
                    <template v-else>
                        <div class="uk-width-1-4@m">
                            <label for="">Duree</label>
                            <span class="uk-input uk-border-rounded">{{upgradeData.duree}}</span>
                        </div>
                        <div class="uk-width-1-4@m">
                            <label for="">Debut</label>
                            <span class="uk-input uk-border-rounded">{{upgradeData.debut.slice(0,11)}}</span>
                        </div>
                    </template>
                    <div class="uk-width-1-4@m">
                        <label for="">Montant ttc (GNF)</label>
                        <span class="uk-input uk-border-rounded">{{dataForm.montant_ttc | numFormat}}</span>
                    </div>
                    <div class="uk-width-1-4@m">
                        <label for="">Comission (GNF)</label>
                        <span class="uk-input uk-border-rounded">{{dataForm.comission | numFormat}}</span>
                    </div>
                    <div class="uk-width-1-1@m">
                        <button class="uk-button-small uk-button-primary uk-button uk-border-rounded">Envoyez</button>
                    </div>

                </form>
            </div>
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
            this.getInfos()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                formuleList : [],
                optionsList : [],
                userAccount : {},
                duree : [1,2,3,6,9,12,24],
                dataForm : {
                    _token : "",
                    serial_number : "",
                    old : {
                        formule : "",
                    },
                    new : {
                        formule : "",
                        option : [""],
                    },
                    montant_ttc : 0,
                    comission : 0,
                    duree : 1,
                    debut : "",
                    telephone_client : "",
                    password_confirmation : ""
                },
                currentOption : "",
                upgradeData : {},
                errors : []
            }
        },
        methods : {
            calculMontantTtc : function () {
                try {
                    if(this.dataForm.old.formule && this.dataForm.new.formule) {
                        
                        this.dataForm.montant_ttc = (parseFloat(this.newFormulePrix) - parseFloat(this.oldFormulePrix) + parseFloat(this.allOptionPrix)) * parseInt(this.dataForm.duree)
                        this.dataForm.comission = Math.round((this.dataForm.montant_ttc/1.18) * (5.5/100))
    
                        if(this.dataForm.montant_ttc < 0) {
                            this.dataForm.new.formule = ""
                            this.dataForm.montant_ttc = 0
                            this.dataForm.comission = 0
                            
                            throw "Modification Impossible !"
                        }
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            checkUpgrade : async function () {
                try {
                    let response = await axios.post('/user/rapport/check-upgrade',{
                        _token : this.myToken,
                        serial_number : this.dataForm.serial_number
                    })

                    if(response && response.data != 'fail') {
                        this.upgradeData = response.data
                        this.dataForm.old.formule = response.data.formule_name
                        this.dataForm.duree = response.data.duree
                        this.dataForm.debut = response.data.debut
                    }
                    else {
                        this.upgradeData = {}
                    }
                }
                catch(error) {
                    
                }
            },
            sendUpgradeAfrocash : async function () {
                try {
                    this.isLoading = true
                    this.dataForm._token = this.myToken
                    let response = await axios.post('/user/pdraf/send-upgrade-afrocash',this.dataForm)
                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Success !")
                        Object.assign(this.$data,this.$options.data())
                        this.getInfos()
                    }
                }
                catch(error) {
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
            getInfos : async function () {
                try {
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
                } catch(error) {
                    alert(error)
                }
            }
        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            },
            optionPrix() {
                return this.optionsList.filter((o)  =>  {
                    return o.nom == this.currentOption
                })
            },
            oldFormulePrix() {
                if(this.dataForm.old.formule) {
                    return this.formuleList.filter((f)  =>  {
                        return f.nom == this.dataForm.old.formule
                    })[0].prix
                }
                return 0
            },
            newFormulePrix() {
                if(this.dataForm.new.formule) {
                    return this.formuleList.filter((f)  =>  {
                        return f.nom == this.dataForm.new.formule
                    })[0].prix
                }
                return 0
            },
            allOptionPrix() {
                let som = 0
                this.dataForm.new.option.forEach(op => { 
                    this.currentOption = op
                    if(this.optionPrix[0]) {
                        som += this.optionPrix[0].prix
                    }
                })
                this.currentOption = ""
                return som
            }
        }
    }
</script>
