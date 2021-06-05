<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>


        <div class="uk-container uk-container-large">
           <h3 class="uk-margin-top">Retour Materiel</h3>
           <hr class="uk-divider-small">
            
            <div class="uk-width-1-2@m">
                <template v-if="errors">
                    <div v-for="(err,index) in errors" :key="index" class="uk-width-1-1@m uk-alert-danger" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        <p>{{ err }}</p>
                    </div>
                </template>
                <div class="uk-row">
                    <form @submit.prevent="retourMaterielRequest()" class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="users"></span> Distributeur</label>
                            <select v-model="form.distributeur" class="uk-select uk-border-rounded">
                                <option value="">-- Selectionnez un Distributeur --</option>
                                <option v-for="(d,index) in distributeurs" :key="index" :value="d.username">{{ d.localisation }}</option>
                            </select>
                        </div>
                        <!-- <div class="uk-width-1-1@m">
                            <label for=""><span uk-icon="folder"></span> Depot</label>
                            <select v-model="form.depot" class="uk-select uk-border-rounded">
                                <option value="">-- Selectionnez un depot --</option>
                                <option v-for="(d,index) in depots" :key="index" :value="d.localisation">{{ d.localisation }}</option>
                            </select>
                        </div> -->
                        <div class="uk-width-1-1@m">
                            <label for="">Quantite</label>
                            <input v-model="form.quantite" max="10" type="number" class="uk-input uk-border-rounded">
                        </div>
                        <template v-if="form.quantite > 0">
                            <div class="uk-width-1-2@m" v-for="i in parseInt(form.quantite)" :key="i">
                                <label for="">Materiel-{{i}}</label>
                                <input v-model="form.materiels[i-1]" type="text" class="uk-input uk-border-rounded" :placeholder="'Materiel-' + i">
                            </div>
                        </template>
                        <div class="uk-width-1-1@m">
                            <button type="submit" class="uk-button uk-button-primary uk-button-small uk-border-rounded">Envoyez</button>
                        </div>
                    </form>
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
        beforeMount() {
            this.onInit()
        },
        mounted() {},
        data : () => {
            return {
                isLoading : false,
                fullPage : true,
                form : {
                    _token : "",
                    distributeur : "",
                    // depot : "",
                    quantite : 1,
                    materiels : []
                },
                distributeurs : [],
                depots : [],
                errors : []
            }
        },
        methods : {
            onInit : async function () {
                try {
                    Object.assign(this.$data,this.$options.data())
                    let response = await axios.get('/admin/all-vendeurs')
                    if(response)
                    {
                        this.distributeurs = response.data
                    }

                    // response = await axios.get('/admin/inventory/depot')

                    // if(response)
                    // {
                    //     this.depots = response.data
                    // }
                }
                catch(error)
                {
                    alert(error)
                }
            },
            retourMaterielRequest : async function() {
                try
                {
                    this.isLoading = true
                    this.errors = []
                    this.form._token = this.myToken
                    let response = await axios.post('/material/retour-materiel',this.form)
                    if(response && response.data == 'done')
                    {
                        alert("Success.")
                        this.isLoading = false
                        this.onInit()
                    }
                }
                catch(error)
                {
                    this.isLoading = false
                    if(error.response.data.errors) {
                        let errorTab = error.response.data.errors
                        for (var prop in errorTab) {
                            this.errors.push(errorTab[prop][0])
                        }
                    } 
                    else {
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
