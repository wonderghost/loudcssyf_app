<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <div id="rapport-setting-modal" class="uk-modal-container" uk-modal="esc-close : false ; bg-close : false">
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h3 class="">Reglage Rapport de vente</h3>
                </div>
                <div class="uk-modal-body">
                    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
                        <li><a class="uk-button uk-button-small uk-button-primary uk-border-rounded" href="#">Commission</a></li>
                        <li>
                            <a href="#" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Reabo Afrocash</a>
                        </li>
                        <li>
                            <a href="#" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Interval & Formules</a>
                        </li>
                    </ul>

                    <ul class="uk-switcher uk-margin">
                        <li>
                            <!-- paramtre pourcentage des comissions -->
                            <div class="uk-grid-small" uk-grid>
                                <form @submit.prevent="sendParameters()" class="uk-width-1-2@m">
                                    <div class="uk-width-1-2@m">
                                        <label for="">Pourcentage en Recrutement</label>
                                        <input v-model="dataForm.pourcent_recrut" type="text" class="uk-input uk-border-rounded" placeholder="Pourcentage Recrutement (ex : 5.5)">
                                    </div>
                                    <div class="uk-width-1-2@m">
                                        <label for="">Pourcentage en Reabonnement</label>
                                        <input v-model="dataForm.pourcent_reabo" type="text" class="uk-input uk-border-rounded" placeholder="Pourcentage Reabonnement (ex : 5.5)">
                                    </div>
                                    <div class="uk-width-1-2@m" >
                                        <label for="">Confirmez le mot de passe</label>
                                        <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe">
                                    </div>
                                    <div class="uk-margin-small">
                                        <button type="submit" class="uk-button uk-button-small uk-border-rounded uk-button-primary">Envoyez</button>
                                    </div>
                                </form>
                            </div>

                        </li>
                        <li>
                            <div uk-grid class="uk-grid-small">
                                <form @submit.prevent="sendReaboSetting()" class="uk-width-1-2@m">
                                    <div class="uk-width-1-2@m uk-margin-small">
                                        <label for=""><span uk-icon="users"></span>  Utilisateur</label>
                                        <select v-model="reaboSetting.username" class="uk-select uk-border-rounded">
                                            <option value="">-- Utilisateur --</option>
                                            <option :value="u.username" v-for="(u,index) in userStandart" :key="index">{{u.localisation}}</option>
                                        </select>
                                    </div>
                                    <div class="uk-width-1-2@m uk-margin-small">
                                        <label for="">Confirmez le mot de passe</label>
                                        <input v-model="reaboSetting.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Confirmez votre mot de passe">
                                    </div>
                                    <div>
                                        <button type="submit" class="uk-button uk-button-primary uk-border-rounded uk-button-small">Envoyez</button>
                                    </div>
                                </form>
                            </div>
                        </li>
                        <li>
                            <!-- INTERVAL ET FORMULE -->
                            <table class="uk-table uk-table-small uk-table-divier uk-table-striped uk-table-hover">
                                <thead>
                                    <tr>
                                        <th>interval first</th>
                                        <th>interval last</th>
                                        <th>Formules</th>
                                        <th>-</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(inter,index) in intervalList" :key="index">
                                        <td>{{ inter.interval_first }}</td>
                                        <td>{{ inter.interval_last }}</td>
                                        <td>
                                            <span v-if="inter.formule">
                                                <span v-for="(f,index) in inter.formule" :key="index">
                                                    
                                                </span>
                                            </span>
                                        </td>
                                        <td>
                                            <button uk-tooltip="Ajouter une formule" class="uk-button-primary uk-border-rounded uk-padding-remove"><i class="material-icons">note_add</i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- // -->
                        </li>
                    </ul>                    
                    
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-danger uk-button-small uk-border-rounded uk-modal-close" type="button">Fermer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
export default {
    components : {
        Loading
    },
    mounted() {
        this.getParametersInfos()
    },
    data() {
        return {
            isLoading : false,
            fullPage : true,
            dataForm : {
                _token : "",
                pourcent_recrut : 0,
                pourcent_reabo : 0,
                password_confirmation : ""
            },
            reaboSetting : {
                _token : "",
                username : "",
                password_confirmation : ""
            },
            percentInfos : {},
            userList : [],
            intervalList : []
        }
    },
    methods : {
        sendReaboSetting : async function () {
            try {
                this.isLoading = true
                this.reaboSetting._token = this.myToken
                let response = await axios.post('/admin/pdraf/set-vendeur-for-reabo',this.reaboSetting)
                if(response && response.data == 'done') {
                    this.isLoading = false
                    alert("Success!")
                    this.getParametersInfos()
                    location.reload()
                }
            } catch(error) {    
                if(error.response.data.errors) {
                    let errorTab = error.response.data.errors
                    for (var prop in errorTab) {
                    // this.errors.push(errorTab[prop][0])
                    alert(errorTab[prop][0])
                    }
                } else {
                    // this.errors.push(error.response.data)
                    alert(error.response.data)
                }
            }
        },
        getParametersInfos : async function () {
            try {
                var response = await axios.get('/admin/get-rapport-parameters')
                if(response.data) {
                    this.percentInfos = response.data
                    this.dataForm.pourcent_recrut = this.percentInfos.pourcentage_recrutement
                    this.dataForm.pourcent_reabo = this.percentInfos.pourcentage_reabonnement

                    this.$store.commit('setInfosComissions',response.data)
                }

                response = await axios.get('/admin/all-vendeurs')
                if(response && response.data) {
                    this.userList = response.data
                }

                response = await axios.get('/admin/get-interval-infos')
                if(response) {
                    this.intervalList = response.data
                }
            } catch(error){
                alert(error)
            }
        },
        sendParameters : async function () {
            try {
                this.isLoading = true
                this.dataForm._token = this.myToken
                let response = await axios.post('/admin/set-rapport-parameters',this.dataForm)
                if(response && response.data == 'done') {
                    this.isLoading = false
                    alert('Operation Success !')
                    this.getParametersInfos()
                    location.reload()
                }
            } catch(error) {
                this.isLoading = false
                if(error.response.data.errors) {
                    let errorTab = error.response.data.errors
                    for (var prop in errorTab) {
                    // this.errors.push(errorTab[prop][0])
                    alert(errorTab[prop][0])
                    }
                } else {
                    // this.errors.push(error.response.data)
                    alert(error.response.data)
                }
            }
        }
    },
    computed : {
        myToken() {
            return this.$store.state.myToken
        },
        userStandart() {
            return this.userList.filter((u) => {
                return u.type == 'v_standart'
            })
        }
    }
}
</script>
