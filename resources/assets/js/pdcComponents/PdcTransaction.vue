<template>
   <div>
       <div class="uk-container uk-container-large">
           <h3 class="uk-margin-top">Transaction Pdc</h3>
           <hr class="uk-divider-small">
           <transaction-afrocash :theUser="userLocalisation"></transaction-afrocash>
       </div>
   </div>
</template>

<script>
import TransactionAfrocash from '../userComponents/TransactionAfrocashComponent.vue'
    export default {
        components : {
            'transaction-afrocash' : TransactionAfrocash
        },
        mounted() {
          UIkit.offcanvas($("#side-nav")).hide();  
        },
        computed : {
            typeUser() {
                return this.$store.state.typeUser
            },
            userLocalisation() {
                return this.$store.state.userLocalisation
            }
        }
    }
</script>
