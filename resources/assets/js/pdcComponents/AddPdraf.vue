<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="dots"></loading>
        <div class="uk-container uk-container-large">
            <h3 class="uk-margin-top">Ajoutez un PDRAF</h3>
            <hr class="uk-divider-small">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-3@m">
                    <div v-for="e in errors" :key="e" class="uk-alert-danger" uk-alert>
                        <a href="#" class="uk-alert-close" uk-close></a>
                        <p>
                            {{e}}
                        </p>
                    </div>
                    <form @submit.prevent="sendForm()">
                        <div class="uk-width-1-1@m">
                            <label for="">Email *</label>
                            <input v-model="dataForm.email" type="email" class="uk-input uk-border-rounded" placeholder="Adresse Email (ex : xyz@gmail.com)">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Telephone *</label>
                            <input v-model="dataForm.telephone" type="text" class="uk-input uk-border-rounded" placeholder="Numero de Telephone (ex : 666 000 000)">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Agence *</label>
                            <input v-model="dataForm.agence" type="text" class="uk-input uk-border-rounded" placeholder="nom de l'agence (ex : Boutique de Diallo)">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Adresse *</label>
                            <input v-model="dataForm.adresse" type="text" class="uk-input uk-border-rounded" placeholder="Adresse (ex : Manquepas Kaloum )">
                        </div>
                        <div class="uk-width-1-1@m">
                            <label for="">Confirmez le mot de passe *</label>
                            <input v-model="dataForm.password_confirmation" type="password" class="uk-input uk-border-rounded" placeholder="Entrez votre mot de passe pour confirmer">
                        </div>
                        <div class="uk-margin-top">
                            <button class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
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
    data() {
        return {
            isLoading : false,
            fullPage : true,
            dataForm : {
                _token : "",
                email : "",
                telephone : "",
                adresse : "",
                agence : "",
                password_confirmation : "",
            },
            errors : []
        }
    },
    methods : {
        sendForm : async function () {
            try {
                this.isLoading = true
                this.dataForm._token = this.myToken
                let response = await axios.post('/user/pdc/send-pdraf-add-request',this.dataForm)
                if(response && response.data == 'done') {
                    this.isLoading = false
                    alert("Success!")
                    Object.assign(this.$data,this.$options.data())
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
        }
    },
    computed : {
        myToken() {
            return this.$store.state.myToken
        }
    }
}
</script>