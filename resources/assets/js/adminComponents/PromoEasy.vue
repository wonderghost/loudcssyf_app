<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <h3 class="">Promo Easy</h3>
        <hr class="uk-divider-small">
        
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
    export default {
        components : {
            Loading
        },
        mounted() {},
        data : () => {
            return {
                isLoading : false,
                fullPage : true
            }
        },
        methods : {
            
        },
        computed : {
            myToken()
            {
                return this.$store.state.myToken
            }
        }
    }
</script>
