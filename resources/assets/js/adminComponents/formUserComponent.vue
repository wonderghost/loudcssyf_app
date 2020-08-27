<template>
    <div class="">
        <loading :active.sync="isLoading"
      :can-cancel="false"
      :is-full-page="fullPage"
      loader="dots"></loading>

        <template v-if="type == 'edit'">
            <div class="uk-alert-danger uk-width-1-3@m  uk-border-rounded" v-for="(error,index) in errors" :key="index" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p>
                    {{error}}
                </p>
            </div>
            <form @submit.prevent="editUserRequest()">
                <div class="uk-grid-small uk-child-width-1-2@m" uk-grid>
                    <div class="">
                        <!-- champs obligatoires -->
                        <div class="">
                            <label for="">Email *</label>
                            <input v-model="data.email" type="email" name="" :class="inputClass" placeholder="ex : xyz@gmail.com">
                        </div>
                        <div class="">
                            <label for="">Telephone *</label>
                            <input v-model="data.phone"  type="text" name="" :class="inputClass" placeholder="ex : 666 000 000">
                        </div>
                        <div class="">
                            <label>Agence *</label>
                            <input v-model="data.localisation" type="text" name="" :class="inputClass" placeholder="Entrez le nom de l'agence">
                        </div>
                        <div class="">
                            <label for="">Niveau d'access *</label>
                            <span class="uk-input uk-border-rounded">{{typeAccess[data.type]}}</span>
                        </div>
                        <div>
                            <label for="">Confirmez votre mot de passe</label>
                            <input v-model="data.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe">
                        </div>
                    </div>
                    <div class="" v-if="requireState">
                        <!-- champs facultatif -->
                        <div class="">
                            <label for="">NumDist</label>
                            <input v-model="data.numdist" type="text" name="" value="" :class="inputClass" placeholder="Numero Distributeur">
                        </div>
                        <div class="">
                            <label for="">Societe <span v-if="data.access == 'pdc'">*</span></label>
                            <input v-model="data.societe" type="text" name="" :class="inputClass" placeholder="Nom de l'entreprise">
                        </div>
                        <div class="">
                            <label for="">Rccm</label>
                            <input v-model="data.rccm" type="text" name="" :class="inputClass" placeholder="Numero Registre du commerce">
                        </div>
                        <div class="">
                            <label for="">Ville</label>
                            <input v-model="data.ville" type="text" name="" :class="inputClass" placeholder="ex : Conakry">
                        </div>
                        <div class="">
                            <label for="">Adresse</label>
                            <input v-model="data.adresse" type="text" name="" :class="inputClass" placeholder="Quartier">
                        </div>
                    </div>
                </div>
                <div class="uk-margin-small">
                    <button type="submit" name="button" class="uk-button uk-text-capitalize uk-text-small uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-sall">
                    validez
                    <span uk-icon="icon : check"></span>
                    </button>
                </div>
            </form>
        </template>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
        
       props : {
           data : Object,
           type : String,
       },
       components : {
           Loading
       },
       data() {
           return {
               inputClass : "uk-input uk-border-rounded",
               typeAccess : {
                    v_da : 'Distributeur Agree',
                    v_standart : 'Vendeur standart',
                    logistique : "Responsable Logistique",
                    controleur : 'Controleur',
                    gcga : 'Gestionnaire Cga',
                    grex : 'Gestionnaire Rex',
                    coursier : "Coursier",
                    gdepot : 'Gestionnaire depot',
                    commercial : 'Responsable commercial',
                    technicien : 'Technicien',
                    pdc : 'Point de Contact',
                    pdraf : 'Point de Retrait Afrocash',
                    graf : 'Gestionnaire Reseaux Afrocash'
                },
                requireState : true,
                isLoading : false,
                fullPage : true,
                errors  : []
           }
       },
       methods : {
           editUserRequest : async function () {
                try {
                    this.isLoading = true
                    this.data._token = this.myToken
                    let response = await axios.post('/admin/users/edit-request',this.data)
                    if(response && response.data == 'done') {
                        this.isLoading = false
                        alert("Success!")
                        Object.assign(this.$data,this.$options.data())
                    }
                } catch(error) {
                    this.isLoading = false
                    alert("Error!")
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
       }
    }
</script>
