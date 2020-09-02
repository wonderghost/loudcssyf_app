<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>


        <h3>Operation Afrocash</h3>
        <hr class="uk-divider-small">
        <ul uk-tab>
            <li><a href="#">Apport</a></li>
            <li><a href="#">Depenses</a></li>
        </ul>

        <ul class="uk-switcher uk-margin">
            <li>
                <!-- APPORT -->
                <button v-if="typeUser == 'admin'" type="button" uk-toggle="target: #modal-apport" class="uk-width-1-4@m uk-button uk-button-primary uk-button-small  uk-border-rounded uk-box-shadow-small" name="button"><span uk-icon="icon : plus"></span> Effectuez un apport</button>
          <!-- AUGMENTATION CAPITAL / APPORT -->
                <div id="modal-apport" uk-modal>
                    <div class="uk-modal-dialog">
                        <div class="uk-modal-header">
                            <h4 class="uk-modal-title">Effectuez un apport</h4>
                        </div>
                        <div class="uk-modal-body">
                            <!-- Erreor block -->
                            <template v-if="errors.length" v-for="error in errors">
                            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                                <a href="#" class="uk-alert-close" uk-close></a>
                                <p>{{error}}</p>
                            </div>
                            </template>
                            <div class="uk-alert-info uk-border-rounded uk-box-shadow-small" uk-alert>
                                <a href="#" class="uk-alert-close" uk-close></a>
                                <p>Remplissez les champs vides!</p>
                            </div>
                            <form @submit.prevent="makeApportAfrocashCentral()">
                                <div class="uk-margin-small">
                                    <label for="">Montant</label>
                                    <input v-model="formData.montant" type="text" class="uk-input uk-border-rounded">
                                </div>
                                <div class="uk-margin-small">
                                    <label for="">Description</label>
                                    <textarea v-model="formData.description" class="uk-textarea uk-border-rounded" id="" cols="10" rows="10"></textarea>
                                </div>
                                <button type="submit" class="uk-text-capitalize uk-button uk-button-primary uk-border-rounded uk-button-small">Envoyez</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- // -->
                <hr class="uk-divider-small">
                <h3>Historique des apports</h3>
                <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Motif</th>
                        <th>Description</th>
                        <th>Solde anterieur</th>
                        <th>Nouveau solde</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in histoApport.slice(start,end)">
                            <td>{{a.created_at}}</td>
                            <td>{{a.montant | numFormat}}</td>
                            <td>{{a.motif}}</td>
                            <td>{{a.description}}</td>
                            <td>{{a.solde_anterieur | numFormat}}</td>
                            <td>{{a.nouveau_solde | numFormat}}</td>
                        </tr>
                    </tbody>
                </table>
                <ul class="uk-pagination uk-flex uk-flex-center" uk-margin>
                    <li> <span> Page active : {{currentPage}} </span> </li>
                    <li> <button @click="previousPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> <span uk-pagination-previous></span> Precedent </button> </li>
                    <li> <button @click="nextPage()" type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" name="button"> Suivant <span uk-pagination-next></span>  </button> </li>
                </ul>
            </li>
            <li>
                <!-- DEPENSES -->
                <button v-if="typeUser == 'admin'" type="button" uk-toggle="target: #modal-depenses" class="uk-width-1-4@m uk-button uk-button-primary uk-button-small  uk-border-rounded uk-box-shadow-small" name="button"><span uk-icon="icon : minus"></span> Ajouter une depense</button>
                <!-- // -->
                <div id="modal-depenses" uk-modal>
                    <div class="uk-modal-dialog">
                        <div class="uk-modal-header">
                            <h4 class="uk-modal-title">Ajoutez une depense</h4>
                        </div>
                        <div class="uk-modal-body">
                        <!-- Erreor block -->
                        <template v-if="errors.length" v-for="error in errors">
                        <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
                            <a href="#" class="uk-alert-close" uk-close></a>
                            <p>{{error}}</p>
                        </div>
                        </template>
                        <div class="uk-alert-info uk-border-rounded uk-box-shadow-small" uk-alert>
                            <a href="#" class="uk-alert-close" uk-close></a>
                            <p>Remplissez les champs vides!</p>
                        </div>
                        <form @submit.prevent="makeDepenseAfrocashCentral()">
                            <div class="uk-margin-small">
                                <label for="">Motif</label>
                                <select name="" class="uk-select uk-border-rounded" v-model="depenseForm.motif">
                                    <option value="paiement_salaire">Paiement Salaire</option>
                                    <option value="loyers">Loyers</option>
                                    <option value="connection_internet">Connection Internet</option>
                                    <option value="carburant">Carburant</option>
                                    <option value="credit_appel">Credit Appel</option>
                                    <option value="commission">Commission</option>
                                    <option value="autres">Autres</option>
                                </select>
                            </div>
                            <div class="uk-margin-small">
                                <label for="">Montant</label>
                                <input type="number" v-model="depenseForm.montant" class="uk-input uk-border-rounded">
                            </div>
                            <div class="uk-margin-small">
                                <label for="">Description</label>
                                <textarea class="uk-textarea uk-border-rounded" v-model="depenseForm.description" id="" cols="30" rows="10"></textarea>
                            </div>
                            <button type="submit" class="uk-button uk-border-rounded uk-button-primary uk-button-small">Envoyez</button>
                        </form>
                    </div>
                    </div>
                </div>
                <hr class="uk-divider-small">
                <h3>Historique des depenses</h3>
                <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Motif</th>
                        <th>Description</th>
                        <th>Solde Anterieur</th>
                        <th>Nouveau Solde</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in histoDepense">
                            <td>{{d.created_at}}</td>
                            <td>{{d.montant | numFormat}}</td>
                            <td>{{d.motif}}</td>
                            <td>{{d.description}}</td>
                            <td>{{d.solde_anterieur | numFormat}}</td>
                            <td>{{d.nouveau_solde | numFormat}}</td>
                        </tr>
                    </tbody>
                </table>
            </li>
        </ul>
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
            UIkit.offcanvas($("#side-nav")).hide();    
            this.historiqueApport()
            this.historiqueDepense()
        },
        data() {
            return {
                formData : {
                    _token : "",
                    montant : 0,
                    motif : "",
                    description : ""
                },
                depenseForm : {
                    _token : "",
                    montant : "",
                    motif : "",
                    description : ""
                },
                histoApport : [],
                histoDepense : [],
                currentPage: 1,
                start : 0,
                end  : 10,
                fullPage : true,
                isLoading : false,
                errors : []
            }
        },
        methods : {
            makeDepenseAfrocashCentral : async function() {
                try {
                    this.isLoading = true
                    
                    this.depenseForm._token = this.myToken
                    let response = await axios.post('/admin/afrocash/depenses',this.depenseForm)
                    if(response.data == 'done') {
                        alert("Success !")
                        Object.assign(this.$data,this.$options.data())
                        this.historiqueDepense()
                        
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
            makeApportAfrocashCentral : async function () {
                try {
                    this.isLoading = true
                    
                    this.formData._token = this.myToken
                    let response = await axios.post('/admin/afrocash/apport',this.formData)
                    
                    if(response.data == 'done') {

                        alert("Success !")
                        Object.assign(this.$data,this.$options.data())
                        this.historiqueApport()
                        
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
            historiqueApport : async function () {
                try {
                    this.isLoading = true
                    let response = await axios.get('/admin/afrocash/historique-apports')
                    this.histoApport = response.data
                    this.isLoading = false
                } catch(error) {
                    alert(error)
                }
            },
            historiqueDepense : async function() {
                try {
                    this.isLoading = true
                    let response = await axios.get('/admin/afrocash/historique-depenses');
                    this.histoDepense = response.data
                    this.isLoading = false
                } catch(error) {
                    alert(error)
                }
            },
            nextPage : function () {
            if(this.histoApport.length > this.end) {
              let ecart = this.end - this.start
              this.start = this.end
              this.end += ecart
              this.currentPage++
            }
          },
          previousPage : function () {
            if(this.start > 0) {
              let ecart = this.end - this.start
              this.start -= ecart
              this.end -= ecart
              this.currentPage--
            }
          }
        }, 
        computed : {
            typeUser() {
                return this.$store.state.typeUser
            },
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
