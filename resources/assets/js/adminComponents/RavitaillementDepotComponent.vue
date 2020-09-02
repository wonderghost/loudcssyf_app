<template>
  <div class="uk-container uk-container-large">
    <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#fff"
        background-color="#083050"></loading>

        <h3>Ravitaillement Depot</h3>
        <hr class="uk-divider-small">

        <!-- Erreor block -->
          <template v-if="errors.length" v-for="error in errors">
          <div class="uk-alert-danger uk-border-rounded uk-align-center uk-box-shadow-hover-small uk-width-1-3@m" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>{{error}}</p>
          </div>
        </template>

        <form @submit.prevent="sendRavitaillementDepot()" class="uk-grid-small uk-grid-divider" uk-grid>
        <!-- SELECT MATERIAL -->
          <div class="uk-width-1-4@m">

            <div class="uk-width-1-1@m uk-margin-small">
              <label for="">Material</label>
              <select class="uk-select uk-border-rounded" @change="makeSerial($event)" v-model="formData.produit">
                <option value="">--Materiel--</option>
                <option :value="m.reference" :id="m.with_serial" v-for="m in materiels"> {{m.libelle}} </option>
              </select>
            </div>

            <div class="uk-width-1-1@m uk-margin-small">
              <label for="">Depot</label>
              <select class="uk-select uk-border-rounded" v-model="formData.depot">
                <option value="">-- Choisissez un depot --</option>
                <option :value="d.localisation" v-for="d in depots"> {{d.localisation}} </option>
              </select>
            </div>
            <div class="uk-width-1-1@m uk-margin-small">
              <label>Quantite</label>
              <input type="number" min="1" required class="uk-input uk-border-rounded" v-model="formData.quantite">
            </div>
            <div class="uk-width-1-1@m">
              <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
            </div>

          </div>
          <div class="uk-width-3-4@m">
            <template v-if="serial" id="">
              <div class="uk-alert-info uk-border-rounded" uk-alert>
                <!-- <a href="#" class="uk-alert-close" uk-close></a> -->
                <p> <span uk-icon="icon : info"></span> Remplissez les champs vides !</p>
              </div>
              <div class="uk-grid uk-grid-small" uk-grid>
                <div v-for="i in parseInt(formData.quantite)" class="uk-width-1-5@m">
                  <label for="">Serial Number {{i}}</label>
                  <input type="text" class="uk-input uk-border-rounded" v-model="formData.serials[i-1]" placeholder="Entrez le Numero de Serie">
                </div>
              </div>
            </template>
          </div>
      </form>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

  export default {
    created() {
      this.isLoading = true
    },
    mounted() {
      UIkit.offcanvas($("#side-nav")).hide();
      this.getInfos()
    },
    components : {
      Loading
    },
    data() {
      return {
        isLoading : false,
        fullPage : true,
        depots : [],
        materiels : [],
        formData : {
          _token : "",
          quantite : 1,
          produit : "",
          depot : "",
          serials : []
        },
        errors : [],
        serial : false
      }
    },
    methods : {
      makeSerial : function (event) {
        var terminal
        this.materiels.forEach( (value) => {
          if(value.with_serial == 1) {
            terminal = value
          }
        })
        if(event.target.value == terminal.reference) {
          this.serial = true
        }
        else {
          this.serial = false
        }
      },
      getInfos : async function () {
        try {
          let response = await axios.get('/logistique/ravitaillement/list-depot')
          this.depots = response.data
          response = await axios.get('/user/logistique/get-materiel')
          this.materiels = response.data
          this.isLoading = false
        } catch (e) {
            alert(e)
        }
      },
      sendRavitaillementDepot : async function () {
        this.isLoading = true
        try {
          this.formData._token = this.myToken
          let response = await axios.post('/user/ravitailler-depot',this.formData)
          if(response.data == 'done') {
            alert("Success !")
            Object.assign(this.$data,this.$options.data())
          }
          this.isLoading = false
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
      }
    },
    computed : {
      myToken() {
        return this.$store.state.myToken
      }
    }
  }
</script>
