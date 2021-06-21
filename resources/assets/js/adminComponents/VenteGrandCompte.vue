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
            <li><span>Reseaux Afrocash</span></li>
            <li><span>Vente(s) Grand Compte</span></li>
        </ul>

        <h3>Vente(s) Grand Compte</h3>
        <hr class="uk-divider-small">

        <table class="uk-table uk-table-small uk-table-middle uk-table-striped uk-table-hover uk-table-divider uk-table-responsive">
            <thead>
                <tr>
                    <th v-for="(h,index) in headers" :key="index">{{ h }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(v,index) in ventes" :key="index">
                    <td>{{ v.date }}</td>
                    <td>{{ v.nom_complet }}</td>
                    <td>{{ v.entreprise }}</td>
                    <td>{{ v.montant | numFormat }}</td>
                    <td>{{ v.materiel }}</td>
                    <td>{{ v.formule }}</td>
                    <td>{{ v.duree }}</td>
                    <td>
                        <template v-if="v.status == 'wait'">
                            <span class="uk-label uk-label-primary">
                                <span uk-icon="more"></span>
                            </span>
                        </template>
                        <template v-else-if="v.status == 'confirmed'">
                            <span class="uk-label uk-label-success">
                                <span uk-icon="check"></span>
                            </span>
                        </template>
                        <template v-else>
                            <span class="uk-label uk-label-danger">
                                <span uk-icon="close"></span>
                            </span>
                        </template>
                    </td>
                    <td>
                        <template v-if="v.status == 'wait'">
                            <button class="uk-border-rounded uk-button-primary" @click="onConfirm(v)">
                                <span uk-icon="check"></span>
                            </button>
                            <button class="uk-border-rounded uk-button-danger" @click="onRemove(v)">
                                <span uk-icon="trash"></span>
                            </button>
                        </template>
                        
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        components : {
            Loading
        },
        beforeMount()
        {
            this.onInit()
        },
        mounted() {},
        data : () => {
            return {
                headers : ['Date','Nom Complet','Entreprise','Montant','Materiel','Formule','Duree','Status','-'],
                isLoading : false,
                fullPage : true,
                ventes : [],
                errors : []
            }
        },
        methods : {
            onInit : async function ()
            {
                try
                {
                    this.isLoading = true
                    let response = await axios.get('/vente-grand-compte')
                    if(response.status == 200)
                    {
                        this.ventes = response.data
                        this.isLoading = false
                    }
                }
                catch(error)
                {
                    this.isLoading = false
                    alert(error)
                }
            },
            onConfirm : async function (vente)
            {
                try
                {
                    var confirmation = confirm("Etes vous sur de vouloir effectuer cette action ?")
                    if(!confirmation)
                    {
                        return 0
                    }
                    this.isLoading = true
                    let response = await axios.post('/vente-grand-compte',{
                        _token : this.myToken,
                        'id'   :   vente.id
                    })
                    if(response.status == 200)
                    {
                        this.isLoading = false
                        alert('Success.')
                        this.onInit()
                    }
                }
                catch(error)
                {
                    this.isLoading = false
                    this.errors = []
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
            onRemove : async function (vente)
            {
                try
                {
                    let confirmation = confirm("Etes vous sur de vouloir supprimer cette vente ?")
                    if(!confirmation)
                    {
                        return 0
                    }
                    this.isLoading = true
                    let response = await axios.delete('/vente-grand-compte/'+vente.id)
                    if(response.status == 200)
                    {
                        this.isLoading = false
                        alert("Sucess.")
                        this.onInit()
                    }
                }
                catch(error)
                {
                    this.isLoading = false
                    this.errors = []
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
            myToken()
            {
                return this.$store.state.myToken
            }
        }
    }
</script>
