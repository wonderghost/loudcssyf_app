<template>
    <div>
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <div class="uk-container uk-container-large">
            <h3 class="uk-margin-top">Tous les Pdraf</h3>
            <hr class="uk-divider-small">
            <users-list :list="listUsers"></users-list>
        </div>
    </div>
</template>
<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import UserList from '../userComponents/UserListComponent.vue'

export default {
    components : {
        Loading,
        'users-list' : UserList
    },
    mounted() {
        UIkit.offcanvas($("#side-nav")).hide();
        this.getList()
    },
    data() {
        return {
            listUsers : [],
            isLoading : false,
            fullPage : false
        }
    },
    methods : {
        getList : async function () {
            try {
                this.isLoading = true
                let response = await axios.get('/user/get-pdraf-list')
                if(response) {
                    this.isLoading = false
                    // this.listUsers = response.data
                    response.data.forEach(element => {
                        this.listUsers.push(element.user)
                    });
                }
            } catch(error) {
                alert(error)
            }
        }
    }
}
</script>
