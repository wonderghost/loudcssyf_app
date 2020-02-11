<template>
  <div class="">
    <div id="modal-commission" class="uk-modal-container" uk-modal="esc-close : false ; bg-close : false;">
  	    <div class=" uk-modal-dialog">
  	        <div class="uk-modal-header">
  	            <h3 class="uk-modal-title"> <i class="material-icons">monetization_on</i> Paiement Commission</h3>
  	        </div>
  	        <div class="uk-modal-body uk-overflow-auto uk-height-medium">
  						<table class="uk-table uk-table-small uk-table-hover uk-table-striped uk-table-divider uk-table-responsive">
  							<thead>
  								<tr>
  									<th>Du</th>
  									<th>Au</th>
  									<th>Total</th>
  									<th>Status</th>
  									<th>Vendeurs</th>
                    <th>-</th>
  								</tr>
  							</thead>
  							<tbody>
                  <tr v-for="pay in payComissionList" :key="pay.id">
                    <td>{{pay.du}}</td>
                    <td>{{pay.au}}</td>
                    <td>{{pay.total}}</td>
                    <td>{{pay.status}}</td>
                    <td>{{pay.vendeurs}}</td>
                    <template id="" v-if="pay.status == 'unvalidated'">
                      <td> <button type="button" uk-toggle="target : #validate-payment-comission" class="uk-button uk-button-small uk-button-primary uk-text-capitalize uk-box-shadow-small uk-border-rounded" name="button">Validez</button> </td>
                      <td> <button type="button" class="uk-button uk-button-small uk-button-danger uk-text-capitalize uk-box-shadow-small uk-border-rounded" name="button">Annulez</button> </td>
                    </template>
                  </tr>
                </tbody>
  						</table>
  	        </div>
  	        <div class="uk-modal-footer uk-text-right">
  	            <button class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-danger uk-modal-close" type="button">Fermer</button>
  	        </div>
  	    </div>
  	</div>

    <div id="validate-payment-comission" uk-modal="esc-close : false ; bg-close : false">
  	    <div class="uk-modal-dialog">

  	        <div class="uk-modal-header">
  	            <h4>Vous confirmez la validation pour le paiement des commissions a hauteur de : </h4>
  	        </div>
  	        <div class="uk-modal-body">

  					</div>
  	        <div class="uk-modal-footer uk-text-right">
  						<button uk-toggle="target : #modal-commission" class="uk-button uk-button-default uk-border-rounded uk-box-shadow-small uk-button-small" type="button"> <span uk-icon="icon : arrow-left"></span> Retour</button>
  						<button class="uk-button uk-button-danger uk-modal-close uk-border-rounded uk-box-shadow-small uk-button-small" type="button">Fermer</button>
  					</div>

  	    </div>
  	</div>
  </div>
</template>
<script>
  export default {
    created () {

    },
      mounted() {
        this.getPayComissionList()
      },
      data () {
        return {

        }
      },
      methods : {
        getPayComissionList : async function () {
          try {
            let response = await axios.get('/user/pay-comissions/all')
            if(response.data) {
              this.$store.commit('setPayComissionList',response.data)
            }
          } catch (e) {
              alert(e)
          }
        }
      },
      computed : {
        payComissionList () {
          return this.$store.state.payComissionList
        }
      }
  }
</script>
