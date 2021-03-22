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
            <li><span>Vente</span></li>
            <li><span>Nouveau Reabonnement</span></li>
        </ul>

        <h3 class="uk-margin-top">Nouveau Reabonnement</h3>
        <hr class="uk-divider-small">

        <div class="uk-grid-small" uk-grid>
            <div class="uk-width-2-3@m">
                <div class="uk-alert-danger" uk-alert v-for="(err,index) in errors" :key="index">
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>{{ err }}</p>
                </div>
                <form @submit.prevent="sendReabonnementRequest()" class="uk-grid-small" uk-grid>
                    
                    <div class="uk-width-1-1@m">
                        <h3>Infos Client</h3>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Nom*</label>
                        <input v-model="form.nom" type="text" class="uk-input uk-border-rounded" placeholder="Nom du client">
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Prenom*</label>
                        <input v-model="form.prenom" type="text" class="uk-input uk-border-rounded" placeholder="Prenom du client">
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Telephone Client*</label>
                        <input v-model="form.telephone" type="text" class="uk-input uk-border-rounded" placeholder="Numero de telephone du client">
                    </div>
                    <div class="uk-width-1-1@m">
                        <hr class="uk-divider-small">
                    </div>
                    <div class="uk-width-1-1@m">
                        <h3>Infos Materiel</h3>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Numero Materiel*({{form.materiel.length}})</label>
                        <input v-model="form.materiel" type="text" class="uk-input uk-border-rounded" placeholder="Numero du materiel">
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Formule*</label>
                        <select v-model="form.formule" class="uk-select uk-border-rounded">
                            <option value="">-- Selectionnez une formule --</option>
                            <option :data-prix="f.prix" :value="f.nom" v-for="(f,index) in formules" :key="index">{{ f.title }}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Duree*</label>
                        <select  v-model="form.duree" class="uk-select uk-border-rounded">
                            <option :value="d" v-for="(d,index) in duree" :key="index">{{d}}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-3@m">
                        <label for="">Option</label>
                        <select v-model="form.options" class="uk-select uk-border-rounded" multiple>
                            <option value="">-- Choix des options --</option>
                            <option :data-prix="o.prix" :value="o.nom" v-for="(o,index) in options" :key="index">{{ o.nom }}</option>
                        </select>
                    </div>
                    <div class="uk-width-1-6@m">
                        <label for="">Montant Ttc</label>
                        <span class="uk-input uk-border-rounded">{{ form.montant | numFormat }}</span>
                    </div>
                    
                    <div class="uk-width-1-3@m">
                        <label for="">Confirmez votre mot de passe*</label>
                        <input v-model="form.password" type="password" class="uk-input uk-border-rounded" placeholder="Entrez le mot de passe">
                    </div>
                    <div class="uk-width-1-1@m">
                        <button type="submit" class="uk-button uk-button-primary uk-width-1-1@s uk-width-1-4@m uk-width-1-6@l uk-button-small uk-border-rounded">Envoyez</button>
                    </div>
                </form>
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
            this.onInit()
        },
        data : () => {
            return {
                isLoading : false,
                fullPage : true,
                formules : [],
                options : [],
                duree : [1,2,3,6,9,12,24],
                form : {
                    _token : "",
                    nom : "",
                    prenom : "",
                    materiel : "",
                    formule : "",
                    options : [],
                    duree : 1,
                    telephone : "",
                    montant : 0,
                    password : ""
                },
                errors : [],
                actifOption : ""
            }
        },
        watch : {
            'form.formule' : 'setMontant',
            'form.duree' : 'setMontant',
            'form.options' : 'setMontant'
        },
        methods : {
            onInit : async function () {
                try {
                    Object.assign(this.$data,this.$options.data())

                    let response = await axios.get('/user/formule/list')
                    if(response) {
                        this.formules = response.data.formules
                        this.options = response.data.options
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            sendReabonnementRequest : async function () {
                try {
                    this.isLoading = true
                    this.errors = []
                    this.form._token = this.myToken
                    let response = await axios.post('/vente/reabonnement',this.form)
                    if(response && response.data == 'done') {
                        alert('Success.')
                        this.onInit()
                        this.isLoading = false
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
            setMontant : function () {
                try {
                    var ttc = 0
                    if(this.actifFormule) {
                        ttc = (this.actifFormule.prix + this.totalOption()) * this.form.duree
                    }
                    
                    this.form.montant = ttc
                }
                catch(error) {
                    alert(error)
                }
            },
            totalOption : function () {
            try {
                var total = 0

                if(this.form.options.length > 0) {

                    this.form.options.forEach((o) => {
                        if(o != "") {

                            this.actifOption = o
                            total += this.actifOptionInfos.prix
                        }
                    })
                    
                    return total
                }

                return 0


            } catch(error) {
                alert(error)
            }
        }
        },
        computed : {
            actifFormule() {
                let data = this.formules.filter((f) => {
                    return f.nom == this.form.formule
                })

                if(data) {
                    return data[0]
                }
                return false
            },
            actifOptionInfos() {
                let data = this.options.filter((o) => {
                    return o.nom == this.actifOption
                })

                if(data) {
                    return data[0]
                }
                return false
            },
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
