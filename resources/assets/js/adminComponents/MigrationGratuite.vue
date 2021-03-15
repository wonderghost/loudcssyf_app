<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>


        <form @submit.prevent="sendRapport('migration')" class="uk-width-1-2@m">
            <div class="uk-margin-small">
                <label for="">Quantite Materiel</label>
                <input type="number" v-model="form.quantite_materiel" min="1" class="uk-input uk-margin-small uk-border-rounded">
            </div>
            <!-- SERIAL NUMBERS -->
            <div v-for="input in parseInt(form.quantite_materiel)" :key="input" class="uk-margin-small">
                <input type="text" class="uk-input uk-border-rounded" v-model="form.serial_number[input-1]" required :placeholder="'Serial Number '+input">
            </div>
            <button type="submit" class="uk-button uk-button-small uk-button-primary uk-border-rounded">Envoyez</button>
        </form>
    </div>
</template>
<script>
    export default {
        mounted() {},
        data : () => {
            return {
                isLoading : false,
                fullPage : true,
                errors : [],
                form : {
                    _token : "",
                    serail_number : [],
                    vendeur : ""
                }
            }
        },
        methods : {

        },
        computed : {
            myToken() {
                return this.$store.state.myToken
            }
        }
    }
</script>
