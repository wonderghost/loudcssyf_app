<template>
    <div>
        <div class="uk-margin-left uk-margin-top">
            <a class="uk-button uk-button-small uk-border-rounded"><span uk-icon="icon : credit-card"></span> Afrocash</a>
            <div class="uk-box-shadow-small uk-border-rounded" uk-dropdown>
                <ul class="uk-nav uk-dropdown-nav">
                    <li class="">
                        <router-link class="uk-button uk-button-small uk-text-left" to="/transfert-courant"><span uk-icon="icon : link"></span>Transfert Courant</router-link>
                    </li>
                    <li>
                        <router-link class="uk-button uk-button-small uk-text-left" to="/retour-afrocash"><span uk-icon="icon : link"></span> Retour Afrocash</router-link>
                    </li>
                    <li>
                        <router-link class="uk-button uk-button-small uk-text-left" to="/all-transaction-pdraf"><span uk-icon="icon : link"></span>Toutes les transactions</router-link>
                    </li>
                    <li>
                        <router-link to="/pdraf/command/new"><span uk-icon="icon : link"></span> Commande Materiel</router-link>
                    </li>
                </ul>
            </div>
            
            <a class="uk-button uk-button-small uk-border-rounded"><span uk-icon="icon : users"></span> Ventes</a>
            <div class="uk-box-shadow-small uk-border-rounded" uk-dropdown>
                <ul class="uk-nav uk-dropdown-nav">
                    <li class="">
                        <router-link class="uk-text-left uk-button uk-button-small" to="/reabonnement-afrocash"><span uk-icon="icon : link"></span> Reabonnement</router-link>
                    </li>
                    <li>
                        <router-link class="uk-text-left uk-button uk-button-small" to="/all-ventes-pdraf"><span uk-icon="icon : link"></span> Toutes les ventes</router-link>
                    </li>
                </ul>
            </div>            

            <router-view></router-view>
        </div>
    </div>
</template>

<script>
export default {

}
</script>
