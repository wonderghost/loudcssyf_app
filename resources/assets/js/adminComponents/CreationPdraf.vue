<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>

        <!-- MODAL CONFIRMATION -->
        <div id="modal-confirmation-create-pdraf" uk-modal>
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h5>Confirmez la Creation de : <span class="uk-text-bold"> {{actifDemand.agence}}</span>, pour :<span class="uk-text-bold" v-if="actifDemand.pdc"> {{actifDemand.pdc.localisation}}</span></h5>
                </div>
                <div class="uk-modal-body">
                    <div v-for="(error,index) in errors" class="uk-alert-danger" uk-alert :key="index">
                        <a class="uk-alert-close" uk-close></a>
                        <p>{{error}}</p>
                    </div>
                    <ul class="uk-list uk-list-divider">
                        <li>
                            <span uk-icon="icon : mail"></span>
                            <span>Email : </span>
                            <span class="uk-text-bold">{{actifDemand.email}}</span>
                        </li>
                        <li>
                            <span uk-icon="phone"></span>
                            <span>Telephone : </span>
                            <span class="uk-text-bold">{{actifDemand.telephone}}</span>
                        </li>
                        <li>
                            <span uk-icon="location"></span>
                            <span>Agence : </span>
                            <span class="uk-text-bold">{{actifDemand.agence}}</span>
                        </li>
                        <li>
                            <span uk-icon="world"></span>
                            <span>Adresse : </span>
                            <span class="uk-text-bold">{{actifDemand.adresse}}</span>
                        </li>
                        <li>
                            <span uk-icon="link"></span>
                            <span>Pdc : </span>
                            <span class="uk-text-bold" v-if="actifDemand.pdc">{{actifDemand.pdc.localisation}}</span>
                        </li>
                    </ul>
                    <form @submit.prevent="addPdrafUserByConfirm()">
                        <div class="">
                            <label for=""><span uk-icon="icon : lock"></span>  Confirmez votre mot de passe</label>
                            <input v-model="password_confirmation" type="password" autofocus class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe">
                        </div>
                        <div class="uk-margin-small">
                            <button class="uk-button-primary uk-button-small uk-border-rounded uk-button">Envoyez</button>
                        </div>
                    </form>
                </div>
                <div class="uk-modal-footer">
                    <p class="uk-text-right">
                        <button class="uk-button uk-button-danger uk-border-rounded uk-button-small uk-modal-close" type="button">Fermer</button>
                    </p>
                </div>
            </div>
        </div>
        <!-- // -->


        <table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Telephone</th>
                    <th>Agence</th>
                    <th>Adresse</th>
                    <th>Pdc</th>
                    <th>Status</th>
                    <th>-</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(l,index) in listCreation" :key="index">
                    <td>{{l.email}}</td>
                    <td>{{l.telephone}}</td>
                    <td>{{l.agence}}</td>
                    <td>{{l.adresse}}</td>
                    <td>{{l.pdc.localisation}}</td>
                    <td v-if="!l.confirmed_at && !l.remove_at"><span class="uk-alert-warning">en attente</span></td>
                    <td v-if="l.confirmed_at && !l.remove_at"><span class="uk-alert-success">confirmer</span></td>
                    <td v-if="!l.confirmed_at && l.remove_at"><span class="uk-alert-danger">annuler</span></td>
                    <td>
                        <template v-if="!l.confirmed_at && !l.remove_at">
                            <button @click="actifDemand = l" uk-toggle="target : #modal-confirmation-create-pdraf" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-button-primary">confirm</button>
                            <button class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-button-danger">annuler</button>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        components : {
            Loading
        } ,
        mounted () {
            this.getList()
        },
        data() {
            return {

                isLoading : false,
                fullPage : true,
                listCreation : [],
                actifDemand : {},
                password_confirmation : "",
                errors : []
            }
        },
        methods : {
            addPdrafUserByConfirm : async function () {
                try {
                    this.isLoading = true
                    UIkit.modal($("#modal-confirmation-create-pdraf")).hide()

                    let response = await axios.post('/admin/pdraf/add',{
                        by_confirm_id : this.actifDemand.id,
                        _token : this.myToken,
                        email : this.actifDemand.email,
                        phone : this.actifDemand.telephone,
                        agence : this.actifDemand.agence,
                        access : this.actifDemand.access,
                        password_confirmation : this.password_confirmation,
                        tag : 'by_confirm',
                        pdc : this.actifDemand.pdc.username,
                        access : 'pdraf'
                    })

                    
                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Success !")
                        UIkit.modal($("#modal-confirmation-create-pdraf")).hide()
                        Object.assign(this.$data,this.$options.data())
                        this.getList()
                    }
                } catch(error) {
                    this.isLoading = false
                    UIkit.modal($("#modal-confirmation-create-pdraf")).show()
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
            getList : async function () {
                try {
                    let response = await axios.get('/admin/pdraf/get-list')
                    if(response) {
                        this.listCreation = response.data
                    }
                } catch(error) {
                    alert(error)
                    console.log(error)
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
