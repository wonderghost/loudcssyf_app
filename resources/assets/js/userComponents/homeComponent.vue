<template>
  <div class="">

  <div class="uk-navbar-container uk-box-shadow-small" id="entete" uk-sticky uk-navbar>
    <div class="uk-navbar-left">
        <button class="uk-navbar-item uk-button" uk-toggle="target:#side-nav" uk-icon="icon:menu"></button>
        <a href="" class="uk-navbar-item uk-logo">LAYE DISTRIBUTION</a>
    </div>
    <div class="uk-navbar-center uk-visible@m">
      <a class="uk-button uk-button-small border-button" href="/" uk-tooltip="Tableau de bord"><i class="material-icons">home</i></a>
      <a class="uk-button uk-button-small border-button" href="" uk-tooltip="Notifications"><i class="material-icons">notifications</i></a>
      <a class="uk-button uk-button-small border-button" href="" uk-tooltip="Conversations"><i class="material-icons">message</i></a>
      <a class="uk-button uk-button-small border-button" href="" uk-tooltip="Alertes"><i class="material-icons">alarm</i></a>
      <template v-if="typeUser == 'admin'" id="">
        <a class="uk-button uk-button-small border-button button-pay-comission" uk-toggle href="#modal-commission" uk-tooltip="Paiement Commission"><i class="material-icons">monetization_on</i></a>
      	<a class="uk-button uk-button-small uk-button-primary uk-box-shadow-hover-small uk-margin-left uk-border-rounded uk-box-shadow-hover-small" href="#modal-promo" uk-toggle><span uk-icon="icon : tag"></span> Promo</a>
    </template>

    </div>
    <div class="uk-navbar-right uk-visible@m">
      <a href="#" class="uk-button"><span uk-icon="icon : user ;"></span> {{username}} </a>
      <form action="/logout" method="post">
        <input type="hidden" name="_token" :value="myToken">
        <button class="uk-button uk-button-small uk-button-link uk-margin-right border-button" type="submit" uk-tooltip="Deconnexion"><i class="material-icons">power_settings_new</i></button>
    </form>
    </div>
  </div>

<div class="">
  <div id="side-nav"  uk-offcanvas="mode:push;bg-close:true;">
    <div class="uk-offcanvas-bar side-nav">
        <ul class="uk-nav uk-nav-default uk-nav-parent-icon" uk-nav>
          <li class="uk-nav-header">
            <h5>LAYE DIST / CANAL+ AFROCASH</h5>
          </li>
          <template v-if="typeUser == 'admin'" id="">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:user;ratio:0.9"></span> Utilisateurs</a>
                <ul class="uk-nav-sub">
                    <li><a href="/admin/add-user"><span uk-icon="icon:arrow-right"></span> Nouveau</a></li>
                    <li><a href="/admin/list-users"><span uk-icon="icon:arrow-right"></span> Tous les utilisateurs</a></li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:credit-card;ratio:0.9"></span> Compte Credit</a>
                <ul class="uk-nav-sub">
                    <li><a href="/admin/add-account-credit"><span uk-icon="icon:arrow-right"></span> Comptes</a></li>
                </ul>
            </li>
            <li class="uk-parent">
              <a href="#"><span uk-icon="icon: credit-card;"></span> Afrocash</a>
              <ul class="uk-nav-sub">
                <li><a href="/admin/afrocash"><span uk-icon="icon :arrow-right;"></span> Operations</a>	</li>
                <li><a href="/admin/afrocash/all-transactions"><span uk-icon="icon : arrow-right;"></span> Toutes les transactions</a>	</li>
              </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Materiels</a>
                <ul class="uk-nav-sub">
                    <li><a href="/admin/add-depot"><span uk-icon="icon:arrow-right"></span> Nouveau Materiel</a></li>
                    <li><a href="/admin/depot-central"><span uk-icon="icon:arrow-right"></span> Entrepot</a></li>
                    <li><a href="/admin/list-material"><span uk-icon="icon:arrow-right"></span> Tous les materiels</a></li>
                    <li><a href="/admin/history-depot"><span uk-icon="icon:arrow-right"></span> Historique </a></li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                <ul class="uk-nav-sub">
                    <li><a href="/admin/inventory"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li>
                    <li><a href="#"><span uk-icon="icon:arrow-right"></span> Historique de ravitaillement</a></li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                <ul class="uk-nav-sub">
                    <li><a href="/admin/commandes"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
                </ul>
            </li>
            <li class="uk-parent">
              <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Rapport Ventes</a>
              <ul class="uk-nav-sub">
                  <li><a href="/admin/add-rapport"><span uk-icon="icon:arrow-right"></span> Ajouter</a></li>
                  <li><a href="/admin/all-rapport"><span uk-icon="icon:arrow-right"></span> Toutes les ventes</a></li>
              </ul>
          </li>
          <li class="uk-parent">
            <a href="#"> <span uk-icon="icon : settings ; ratio : .9"></span> Parametres</a>
            <ul class="uk-nav-sub">
              <li><a href="/admin/formule"><span uk-icon="icon:check"></span> Formule</a></li>
              <li><a href="/admin/settings"><span uk-icon="icon:user"></span> Profile</a></li>
            </ul>
          </li>
          </template>
        </ul>
      </div>
    </div>
</div>
</div>
</template>

<script>
    export default {
        mounted() {

        },
        props : {

        }
        ,
        data () {
          return {

          }
        },
        methods : {

        },
        computed : {
          typeUser() {
            return this.$store.state.typeUser
          },
          myToken () {
            return this.$store.state.myToken
          },
          userLocalisation() {
            return this.$store.state.userLocalisation
          },
          username () {
            return this.$store.state.userName
          }
        }
    }
</script>
