<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <template v-if="viewModal">
            <!-- VIEW MODAL -->
            <div id="modal-reactivation-materiel" class="uk-modal-container" uk-modal>
                <div class="uk-modal-dialog">
                    <div class="uk-modal-header">
                        <h2 class="uk-modal-title">Reactivation Materiels</h2>
                    </div>
                    <div class="uk-modal-body">
                        <!-- paginate component -->
                        <div class="uk-align-right uk-width-1-3@m uk-margin-top">
                            <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
                            <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
                            <button @click="getData()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
                            <template v-if="lastUrl">
                                <button @click="paginateFunction(lastUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Precedent">
                                <span uk-icon="chevron-left"></span>
                                </button>
                            </template>
                            <template v-if="nextUrl">
                                <button @click="paginateFunction(nextUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize u-t uk-text-small" uk-tooltip="Suivant">
                                <span uk-icon="chevron-right"></span>
                                </button>
                            </template>
                        </div>
                        <!-- // -->
                        <table class="uk-table uk-table-small uk-table-responsive uk-table-divider uk-table-striped uk-table-hover">
                            <thead>
                                <tr>
                                    <th>Numero Materiel</th>
                                    <th>Pdraf</th>
                                    <th>Status</th>
                                    <th>-</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(h,index) in historiqueList" :key="index">
                                    <td>{{h.materiel}}</td>
                                    <td>{{h.pdraf}}</td>
                                    <template v-if="!h.confirm_at && !h.remove_at">
                                        <td><span class="uk-alert-primary">en cours</span></td>
                                        <td>
                                            <button @click="changeStatus('confirm',h.id)" class="uk-button-primary uk-padding-remove uk-border-rounded">
                                                <i class="material-icons">done</i>
                                            </button>
                                            <button @click="changeStatus('delete',h.id)" class="uk-button-danger uk-padding-remove uk-border-rounded">
                                                <i class="material-icons">delete</i>
                                            </button>
                                        </td>
                                    </template>
                                    <template v-else-if="h.confirm_at">
                                        <td><span class="uk-alert-success">effectu&eacute;</span></td>
                                        <td></td>
                                    </template>
                                    <template v-else>
                                        <td><span class="uk-alert-danger">impossible</span></td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="uk-modal-footer uk-text-right">
                        <button class="uk-button uk-button-danger uk-button-small uk-border-rounded uk-modal-close" type="button">Fermer</button>
                    </div>
                </div>
            </div>
            <!-- // -->
        </template>
        <template v-else>
            <!-- NORMAL VIEW -->
            <!-- paginate component -->
            <div class="uk-align-right uk-width-1-3@m uk-margin-top">
                <span class="">{{firstItem}} - {{firstItem + perPage}} sur {{total}}</span>
                <a v-if="currentPage > 1" @click="paginateFunction(firstPage)" uk-tooltip="aller a la premiere page" class="uk-button-default uk-border-rounded uk-button-small uk-text-small"><span>1</span></a>
                <button @click="getData()" class="uk-button-small uk-button uk-border-rounded uk-text-small" uk-tooltip="actualiser"><span uk-icon="refresh"></span></button>
                <template v-if="lastUrl">
                    <button @click="paginateFunction(lastUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize uk-text-small" uk-tooltip="Precedent">
                    <span uk-icon="chevron-left"></span>
                    </button>
                </template>
                <template v-if="nextUrl">
                    <button @click="paginateFunction(nextUrl)" class="uk-button uk-button-small uk-border-rounded uk-text-capitalize u-t uk-text-small" uk-tooltip="Suivant">
                    <span uk-icon="chevron-right"></span>
                    </button>
                </template>
            </div>
            <!-- // -->
            <table class="uk-table uk-table-small uk-table-responsive uk-table-divider uk-table-striped uk-table-hover">
                            <thead>
                                <tr>
                                    <th>Numero Materiel</th>
                                    <th>Pdraf</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(h,index) in historiqueList" :key="index">
                                    <td>{{h.materiel}}</td>
                                    <td>{{h.pdraf}}</td>
                                    <template v-if="!h.confirm_at && !h.remove_at">
                                        <td><span class="uk-alert-primary">en cours</span></td>
                                    </template>
                                    <template v-else-if="h.confirm_at">
                                        <td><span class="uk-alert-success">effectu&eacute;</span></td>
                                    </template>
                                    <template v-else>
                                        <td><span class="uk-alert-danger">impossible</span></td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
            <!-- // -->
        </template>

    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        props : {
            viewModal : Boolean
        },
        components : {
            Loading
        },
        mounted() {
            this.getData()
        },
        data() {
            return {
                isLoading : false,
                fullPage : true,
                    // paginate
                nextUrl : "",
                lastUrl : "",
                perPage : "",
                currentPage : 1,
                firstPage : "",
                firstItem : 1,
                total : 0,
        // #####
                historiqueList : []
            }
        },
        methods : {
            paginateFunction : async function (url) {
                try {
                
                    let response = await axios.get(url)
                    if(response && response.data) {
                        
                        this.historiqueList = response.data.all

                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.currentPage = response.data.current_page
                        this.firstPage = response.data.first_page
                        this.firstItem = response.data.first_item,
                        this.total = response.data.total
                    }
                }
                catch(error) {
                    alert("Erreur!")
                    console.log(error)
                }
            },
            getData : async function () {
                try {
                    let response = await axios.get('/user/pdraf/get-reactivation-materiel')
                    if(response) {
                        this.historiqueList = response.data.all

                        this.nextUrl = response.data.next_url
                        this.lastUrl = response.data.last_url
                        this.currentPage = response.data.current_page
                        this.firstPage = response.data.first_page
                        this.firstItem = response.data.first_item,
                        this.total = response.data.total

                        this.$store.commit('setReactivationCount',response.data.count)
                    }
                }
                catch(error) {
                    alert(error)
                }
            },
            changeStatus : async function (theState,theId) {
                try {
                    var conf = confirm("Vous etes sur de vouloir effectue cette action ?")

                    if(!conf) {
                        return 0
                    }

                    let response = await axios.post('/admin/pdraf/change-status-reactivation-materiel',{
                        _token : this.mytoken,
                        state : theState,
                        id : theId
                    })
                    if(response && response.data == 'done') {
                        alert("Success !")
                        this.getData()
                    }
                }
                catch(error) {
                    console.log(error)
                }
            }
        },
        computed : {}
    }
</script>
<style lang="css">
    button {
        cursor  : pointer !important;
    }
</style>