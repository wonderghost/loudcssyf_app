<template>
   <div>
       <div class="uk-container uk-container-large">
           <h3 class="uk-margin-top">Historique des transactions</h3>
           <hr class="uk-divider-small">
           <transaction-afrocash :theUser="userLocalisation"></transaction-afrocash>
       </div>
   </div>
</template>

<script>
    export default {
        mounted() {
            
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
