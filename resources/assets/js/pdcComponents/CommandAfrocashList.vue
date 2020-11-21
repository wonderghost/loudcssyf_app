<template>
    <div class="uk-container uk-container-large">
        <loading :active.sync="isLoading"
        :can-cancel="false"
        :is-full-page="fullPage"
        loader="bars"
        :opacity="1"
        color="#1e87f0"
        background-color="#fff"></loading>

        <ul class="uk-breadcrumb">
            <li><router-link uk-tooltip="Tableau de bord" to="/dashboard"><span uk-icon="home"></span></router-link></li>
            <li><span>Reseaux Afrocash</span></li>
            <li><span>Toutes les commandes</span></li>
        </ul>

        <h3 class="uk-margin-top">Toutes les commandes</h3>
        <hr class="uk-divider-small">

        <nav class="" uk-navbar>
          <div class="uk-navbar-left">
              <ul class="uk-navbar-nav">
                  <li class=""><router-link to="/pdc/command/new">Nouvelle Commande</router-link></li>
              </ul>
          </div>
        </nav>
    </div>
</template>

<script>
    export default {
        mounted() {}
    }
</script>
