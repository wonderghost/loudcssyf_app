<template>
  <div class="">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"></loading>

        <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
          <template id="" v-if="typeUser == 'v_standart'">
            <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small " href="#"><span uk-icon="icon : arrow-down"></span> Depots</a></li>
          </template>
            <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small " href="#"><span uk-icon="icon : shrink"></span> Transfert Courant</a></li>
            <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small " href="#"><span uk-icon="icon : arrow-up"></span> Retrait</a></li>
        </ul>
        <!-- Erreor block -->
        <template v-if="errors.length" v-for="error in errors">
        <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small uk-width-1-3@m" uk-alert>
          <a href="#" class="uk-alert-close" uk-close></a>
          <p>{{error}}</p>
        </div>
      </template>

        <ul class="uk-switcher uk-margin">
          <template v-if="typeUser == 'v_standart'" id="">
            <li>
              <!-- DEPOTS -->
              <h3>Effectuez un depot</h3>
              <form @submit.prevent="afrocashDepot()" class="uk-width-1-3@m">
                <div class="uk-margin-small">
                  <label for=""><span uk-icon="icon : users"></span> Vendeur</label>
                  <select class="uk-select uk-border-rounded" v-model="AfrocashData.numero_compte_courant">
                    <option value="">--Selectionnez le vendeur--</option>
                    <option :value="a.numero_compte" v-for="a in accounts">{{ a.vendeurs }}</option>
                  </select>
                </div>
                <div class="uk-margin-small">
                  <label for=""><span uk-icon="icon : credit-card"></span> Montant</label>
                  <input type="number" min="100000" class="uk-border-rounded uk-input" v-model="AfrocashData.montant" placeholder="Entrez le montant">
                </div>
                <div class="uk-margin-small">
                  <label for=""><span uk-icon="icon : lock"></span> Mot de passe</label>
                  <input type="password" v-model="AfrocashData.password" class="uk-input uk-border-rounded" placeholder="Entrez votre de mot de passe">
                </div>
                <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded" name="button">Envoyez <span uk-icon="icon : check"></span> </button>
              </form>
              <!-- // -->
            </li>
          </template>

            <li>
              <h3>Effectuez une transaction</h3>
              <!-- TRANSACTION COURANT -->

              <form @submit.prevent="afrocashTransfertCourant()" class="uk-width-1-3@m">
                <div class="uk-margin-small">
                  <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
                  <select class="uk-select uk-border-rounded" v-model="AfrocashCourant.vendeurs">
                    <option value="">-- Destinataire --</option>
                    <option :value="a.id_vendeurs" v-for="a in accounts">{{ a.vendeurs }}</option>
                  </select>
                </div>
                <div class="uk-margin-small">
                  <label for=""> <span uk-icon="icon : credit-card"></span> Montant</label>
                  <input type="number" min="10000" required class="uk-input uk-border-rounded" v-model="AfrocashCourant.montant"  placeholder="Entrez le montant">
                </div>
                <div class="uk-margin-small">
                  <label><span uk-icon="icon : lock"></span> Confirmez le mot de passe</label>
                  <input type="password" class="uk-input uk-border-rounded" v-model="AfrocashCourant.password" placeholder="Entrez votre mot de passe">
                </div>
                <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez <span uk-icon="icon : check"></span> </button>
              </form>
              <!-- // -->
            </li>
            <li>
              <h3>Effectuez un retrait</h3>
            </li>
        </ul>


  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

  export default {
    components : {
      Loading
    },
    created () {

    },
    mounted() {
      //
      this.accountList()
    },
    data () {
      return {
        isLoading : false,
        fullPage : true,
        AfrocashData : {
          _token : "",
          numero_compte_courant : "",
          montant : 0,
          password : "",
          type_operation : ""
        },
        AfrocashCourant : {
          _token : "",
          vendeurs : "",
          montant : 0,
          password : "",
          type_operation : "transfert_courant"
        },
        accounts : [],
        errors : []
      }
    },
    methods : {
      accountList : async function () {
        try {
          let response = await axios.get('/user/afrocash/get-account-list')
          this.accounts = response.data
        } catch (e) {
            alert(e)
        }
      },
      afrocashDepot : async function () {
        this.isLoading = true
        this.AfrocashData._token = this.myToken
        this.AfrocashData.type_operation = 'depot'
        try {
          let response = await axios.post('/user/afrocash/transaction',this.AfrocashData)
          if(response.data == 'done') {
            UIkit.modal.alert("<div class='uk-alert-success' uk-alert><span uk-icon='icon : check'></span> Transaction effectue avec success !</div>")
              .then(function () {
                location.reload()
              })
          }
        } catch (error) {
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
      afrocashTransfertCourant : async function () {
        this.isLoading = true
        this.AfrocashCourant._token = this.myToken

        try {
          let response = await axios.post('/user/afrocash/transaction',this.AfrocashCourant)
          if(response.data == 'done') {
            this.isLoading = false
            UIkit.modal.alert("<div class='uk-alert-success uk-border-rounded' uk-alert><span uk-icon='icon : check'></span> Transaction effectue avec success!</div>")
              .then(function () {
                location.reload()
              })
          }
        }
        catch (error) {
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
      typeUser () {
        return this.$store.state.typeUser
      },
      myToken () {
        return this.$store.state.myToken
      }
    }
  }
</script>
