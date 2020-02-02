<template>
  <div class="">
    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Comptes</a></li>
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Soldes Vendeurs</a></li>
		</ul>

    <ul class="uk-switcher uk-margin">
      <li>
        <div class="uk-child-width-1-2@m uk-grid-divider" uk-grid>
          <div class="">

            <template v-for="(value , name) in soldes">
  						<div class="uk-grid-small uk-text-lead" uk-grid>
  								<div class="uk-width-expand uk-text-capitalize" uk-leader>{{name}}(GNF)</div>
  								<div>{{value | numFormat}}</div>
  						</div>
          </template>
          </div>
          <div class="">
            <h4>Crediter les comptes</h4>
            <template v-if="errors.length" v-for="error in errors">
            <div class="uk-alert-danger uk-border-rounded uk-box-shadow-hover-small" uk-alert>
              <a href="#" class="uk-alert-close" uk-close></a>
              <p>{{error}}</p>
            </div>
          </template>
          <div v-if="requestState" class="uk-alert-success uk-border-rounded uk-box-shadow-hover-small" uk-alert>
            <a href="#" class="uk-alert-close" uk-close></a>
            <p>Success</p>
          </div>
            <form @submit="crediterAccount($event)">
						<label>
              <input type="radio" v-model="typeCredit" class="uk-radio" name="compte" value="cga"> CGA
						</label>
						<label>
              <input type="radio" v-model="typeCredit" class="uk-radio" name="compte" value="rex"> REX
						</label>
            <input type="number" name="montant" v-model="montantCredit" class="uk-input uk-border-rounded uk-box-shadow-hover-small uk-margin-small" placeholder="Montant">
						<button type="submit" class="uk-button uk-button-small uk-box-shadow-small uk-text-capitalize uk-button-primary uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
          </form>
          </div>
        </div>
      </li>
      <li>

      </li>
    </ul>
  </div>
</template>

<script>
    export default {
        mounted() {
          this.getSolde()
        },
        data () {
          return {
            soldes : {
              afrocash : 0,
              cga : 0,
              rex : 0,
              total : 0
            },
            montantCredit : 0,
            typeCredit : "",
            creditAccountUrl : "/admin/add-account-credit",
            errors : [],
            requestState : false
          }
        }
        ,
        methods : {
          getSolde : async function () {
            try {
              let response = await axios.get('/admin/get-global-solde')
              response.data.forEach(element => {
                if(element.designation == 'cga') {
                  this.soldes.cga = element.solde
                } else if(element.designation == 'afrocash') {
                  this.soldes.afrocash = element.solde
                } else {
                  this.soldes.rex = element.solde
                }
                this.soldes.total+=element.solde
              })
            } catch (error) {
              console.log(error)
            }
          },
          crediterAccount : async function (event) {
            event.preventDefault()

            try {
              let response = await axios.post(this.creditAccountUrl,{
                compte : this.typeCredit,
                montant : this.montantCredit
              })
              if(response.data == 'done') {
                $("#loader").hide()
                this.requestState = true
                this.montantCredit = 0
                this.getSolde()
              }
            } catch (error) {
              $("#loader").hide()
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
