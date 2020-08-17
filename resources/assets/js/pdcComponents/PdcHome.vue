<template>
    <div>
        <div class="uk-margin-left uk-margin-top">
            <a class="uk-button uk-button-small uk-border-rounded"><span uk-icon="icon : credit-card"></span> Afrocash</a>
            <div class="uk-box-shadow-small uk-border-rounded" uk-dropdown>
                <ul class="uk-nav uk-dropdown-nav">
                    <li class="">
                        <router-link class="uk-button uk-button-small uk-text-left" to="/operation"><span uk-icon="icon : link"></span> Operation Afrocash</router-link>
                    </li>
                    <li>
                        <router-link class="uk-button uk-button-small uk-text-left" to="/transactions"><span uk-icon="icon : link"></span> Toutes les transactions</router-link>
                    </li>
                    <li>
                        <router-link class="uk-button uk-button-small uk-text-left" to="/inventaire-pdraf"><span uk-icon="icon : link"></span> Inventaire PDRAF</router-link>
                    </li>
                </ul>
            </div>
            
            <a class="uk-button uk-button-small uk-border-rounded"><span uk-icon="icon : users"></span> Utilisateurs</a>
            <div class="uk-box-shadow-small uk-border-rounded" uk-dropdown>
                <ul class="uk-nav uk-dropdown-nav">
                    <li class="">
                        <router-link class="uk-text-left uk-button uk-button-small" to="/add-pdraf"><span uk-icon="icon : link"></span> Ajoutez un PDRAF</router-link>
                    </li>
                    <li>
                        <router-link class="uk-text-left uk-button uk-button-small" to="/all-users"><span uk-icon="icon : list"></span> Tous les Utilisateurs</router-link>
                    </li>
                    <li>
                        <router-link class="uk-text-left uk-button uk-button-small" to="/all-make-pdraf"><span uk-icon="icon : list"></span> Creation Pdraf</router-link>
                    </li>
                </ul>
            </div>            

            <a class="uk-button uk-button-small uk-border-rounded"><span uk-icon="icon : cart"></span> Ventes</a>
            <div class="uk-box-shadow-small uk-border-rounded" uk-dropdown>
                <ul class="uk-nav uk-dropdown-nav">
                    <li class="">
                        <router-link class="uk-text-left uk-button uk-button-small" to="/all-ventes"><span uk-icon="icon : link"></span> Toutes les ventes</router-link>
                    </li>
                </ul>
            </div>

            <router-view></router-view>
        </div>
    </div>
</template>
<script>

export default {
    mounted() {
        
    }
}
</script>
