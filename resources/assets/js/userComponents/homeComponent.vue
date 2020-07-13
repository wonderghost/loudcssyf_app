<template>
  <div class="">

  <div class="uk-navbar-container uk-box-shadow-small" id="entete" uk-sticky uk-navbar>
    <div class="uk-navbar-left">
        <button class="uk-navbar-item uk-button" uk-toggle="target:#side-nav" uk-icon="icon:menu"></button>
        <!-- <a href="" class="uk-navbar-item uk-logo uk-visible@m">LAYE DISTRIBUTION</a> -->
        <a href="" class="uk-navbar-item uk-logo uk-visible@m">
          <img src="/img/layedist.png" width="100" alt="">
        </a>
        <a href="" class="uk-navbar-item uk-logo uk-hidden@m">
          <img src="/img/layedist.png" width="70" alt="">
        </a>
    </div>
    <div class="uk-navbar-center uk-visible@m">
      <a class="uk-button uk-button-small border-button" href="/" uk-tooltip="Tableau de bord"><i class="material-icons">home</i></a>
      <div class="uk-inline">
        <a class="uk-button uk-button-small border-button" uk-tooltip="Notifications"><i class="material-icons">notifications</i><span class="">{{unreadNotifications.length}}</span> </a>
        <div class="" uk-drop="mode: click ; animation: uk-animation-slide-top-small;">
          <div class="uk-card-default uk-box-shadow-small notification-container uk-overflow-auto" style="background : #fefefe !important;border : solid 1px #ddd !important; ">
            <ul class="uk-list uk-list-divider">
              <li v-for="n in unreadNotifications.slice(0,5)">
                <span class="">
                  <span class="uk-text-bold">{{n.titre}}</span>
                  <p class="uk-margin-remove">{{n.description}}</p>
                </span>
                  <div class="uk-text-right uk-margin-right">
                    <a @click="readNotification(n.id)">vu</a>
                  </div>
              </li>
            </ul>
            <a class="uk-button uk-button-link uk-text-capitalize" href="#all-notification" uk-toggle>Tout voir</a>
          </div>
        </div>
      </div>
      <a class="uk-button uk-button-small border-button" uk-tooltip="Conversations"><i class="material-icons">message</i></a>
      <a class="uk-button uk-button-small border-button" uk-tooltip="Alertes" uk-toggle="target : #modal-alert-abonnement"><span>{{alertCount}}</span> <i class="material-icons">alarm</i> <span style="color : red !important">{{alertInactifCount}}</span></a>
      <!-- <a class="uk-button uk-button-small border-button" uk-tooltip="Alertes"><i class="material-icons" style="color : red !important;">alarm</i></a> -->
      <template v-if="typeUser == 'admin' || typeUser == 'commercial'" id="">
      	<a class="uk-button uk-button-small uk-button-primary uk-box-shadow-hover-small uk-margin-left uk-border-rounded uk-box-shadow-hover-small" href="#modal-promo" uk-toggle><span uk-icon="icon : tag"></span>  PROMO</a>
    </template>
    <template v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'commercial'" id="">
      <a class="uk-button uk-button-small border-button button-pay-comission" uk-toggle href="#modal-commission" uk-tooltip="Paiement Commission"><i class="material-icons">monetization_on</i></a>
    </template>
      <a class="uk-button uk-button-small border-button" uk-tooltip="Recherche" uk-toggle="target : #modal-search-serial"><i class="material-icons">search</i></a>
      <a class="uk-button uk-button-small border-button" href="#modal-remboursement" uk-toggle uk-tooltip="Paiement Remboursement"><i class="material-icons">payment</i></a>
      <a class="uk-button uk-button-small border-button" uk-toggle href="#modal-deblocage" uk-tooltip="Deblocage Cga"><i class="material-icons">lock_open</i><span v-if="typeUser !=='v_da' && typeUser !== 'v_standart'">{{deblocageCount}}</span> </a>
    </div>
    <div class="uk-navbar-right uk-visible@m">
      <a class="uk-button"><span uk-icon="icon : user ;"></span> {{userLocalisation}} </a>
      <form action="/logout" method="post">
        <input type="hidden" name="_token" :value="myToken">
        <button class="uk-button uk-button-small uk-button-link uk-margin-right border-button" type="submit" uk-tooltip="Deconnexion"><i class="material-icons">power_settings_new</i></button>
    </form>
    </div>
    <div class="uk-hidden@m uk-navbar-right">
      <a class="uk-button uk-button-small border-button" href="/" uk-tooltip="Tableau de bord"><i class="material-icons">home</i></a>
      <a class="uk-button uk-button-small border-button" uk-tooltip="Recherche" uk-toggle="target : #modal-search-serial"><i class="material-icons">search</i></a>
      <a v-if="typeUser == 'admin' || typeUser == 'gcga'" class="uk-button uk-button-small border-button" uk-tooltip="Paiement Comission" uk-toggle="target : #modal-commission"><i class="material-icons">monetization_on</i></a>
      <a v-if="typeUser == 'v_da' || typeUser == 'v_standart'" class="uk-button uk-button-small border-button" href="#modal-remboursement" uk-toggle uk-tooltip="Paiement Remboursement"><i class="material-icons">payment</i></a>
      <a class="uk-button uk-button-small border-button" href="#all-notification" uk-toggle uk-tooltip="Notifications"><i class="material-icons">notifications</i><span class="">{{unreadNotifications.length}}</span> </a>
      <a class="uk-button uk-button-small border-button" uk-tooltip="Alertes" uk-toggle="target : #modal-alert-abonnement"><span>{{alertCount}}</span> <i class="material-icons">alarm</i> <span style="color : red !important">{{alertInactifCount}}</span></a>
    </div>
  </div>

  <!-- TOUTES LES NOTIFICATIOS -->
  <div id="all-notification" uk-modal="esc-close : false ; bg-close : false">
      <div class="uk-modal-dialog uk-modal-body">
          <h4 class="uk-modal-title"><i class="material-icons">notifications</i> Toutes les notifications</h4>
          <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-left-medium">
              <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Non Lues</a></li>
              <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Lues</a></li>
          </ul>

          <ul class="uk-switcher uk-margin uk-overflow-auto uk-height-medium">
              <li>
                <ul class="uk-list uk-list-divider">
                  <li v-for="n in unreadNotifications.slice(0,50)">
                    <span class="uk-text-bold">{{n.titre}}</span>
                    <p class="uk-margin-remove">{{n.description}}</p>
                  </li>
                </ul>
              </li>
              <li>
                <ul class="uk-list uk-list-divider">
                  <li v-for="n in readNotifications.slice(0,50)">
                    <span class="uk-text-bold">{{n.titre}}</span>
                    <p class="uk-margin-remove">{{n.description}}</p>
                  </li>
                </ul>
              </li>
          </ul>
          <p class="uk-text-right">
             <button class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-box-shadow-small uk-modal-close" type="button">Fermer</button>
          </p>
      </div>
  </div>
  <!-- // -->

<div class="">
  <div id="side-nav"  uk-offcanvas="mode:push;bg-close:true;">
    <div class="uk-offcanvas-bar side-nav">
        <ul class="uk-nav uk-nav-default uk-nav-parent-icon" uk-nav>
          <li class="uk-nav-header">
            <h5>LAYE DIST / CANAL+ AFROCASH</h5>
          </li>
          <template v-if="typeUser == 'admin' || typeUser == 'commercial'" id="">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:user;ratio:0.9"></span> Utilisateurs</a>
                <ul class="uk-nav-sub">
                    <li><a href="/admin/add-user"><span uk-icon="icon:arrow-right"></span> Nouveau</a></li>
                    <li><a href="/admin/list-users"><span uk-icon="icon:arrow-right"></span> Tous les utilisateurs</a></li>
                </ul>
            </li>
            <li class="uk-parent" >
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
                <li><a href="/admin/recouvrement/operations"><span uk-icon="icon : arrow-right"></span> Recouvrements</a></li>
              </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Materiels</a>
                <ul class="uk-nav-sub">
                    <li><a href="/admin/add-depot"><span uk-icon="icon:arrow-right"></span> Entrepot</a></li>
                    <!-- <li><a href="/admin/depot-central"><span uk-icon="icon:arrow-right"></span> Entrepot</a></li> -->
                    <li><a href="/admin/list-material"><span uk-icon="icon:arrow-right"></span> Tous les materiels</a></li>
                    <!-- <li><a href="/admin/history-depot"><span uk-icon="icon:arrow-right"></span> Historique </a></li> -->
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                <ul class="uk-nav-sub">
                    <li><a href="/admin/inventory"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li>
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
          </template>
          <template v-if="typeUser == 'admin'">
            <li class="uk-parent">
            <a href="#"> <span uk-icon="icon : settings ; ratio : .9"></span> Parametres</a>
            <ul class="uk-nav-sub">
              <li><a href="/admin/formule"><span uk-icon="icon:check"></span> Formule</a></li>
              <li><a href="/admin/settings"><span uk-icon="icon:user"></span> Profile</a></li>
              <li>
                  <form action="/logout" method="post">
                    <input type="hidden" name="_token" :value="myToken">
                    <button style="border : none;" class="uk-button uk-button-small uk-button-default uk-text-capitalize" type="submit">
                      <span uk-icon="icon:sign-out"></span> Deconnexion
                    </button>
                </form>
                </li>
            </ul>
          </li>
          </template>
          <template v-if="typeUser == 'v_da' || typeUser == 'v_standart'" id="">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Ventes</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/rapport-ventes"><span uk-icon="icon:plus"></span> Rapport de ventes</a></li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/my-inventory"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li>
                    <!-- <li><a href="/user/my-history-ravitaillement"><span uk-icon="icon:arrow-right"></span> Historique de ravitaillement</a></li> -->
                </ul>
            </li>

            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:users;ratio:0.9"></span> Carnet d'adresse</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/add-client"><span uk-icon="icon:arrow-right"></span> Nouveau</a></li>
                    <li><a href="/user/list-client"><span uk-icon="icon:arrow-right"></span> Repertoire</a></li>
                </ul>
            </li>
            <!-- AFROCASH -->
            <li class="uk-parent">
              <a href="#"><span uk-icon="icon : credit-card"></span> Afrocash</a>
              <ul class="uk-nav-sub">
                <li><a href="/user/afrocash"> <span uk-icon="icon : arrow-right"></span>	Operations</a> </li>
                <li><a href="/user/afrocash/all-transactions"> <span uk-icon="icon : arrow-right"></span>	Toutes les Transactions</a> </li>
              </ul>
            </li>
            <!-- // -->
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/new-command"><span uk-icon="icon:arrow-right"></span> Nouvelle Commande</a></li>
                    <li><a href="/user/list-command"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
                </ul>
            </li>
          </template>
          <!-- GESTIONNAIRE CGA -->
          <template v-if="typeUser == 'gcga'" id="">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:credit-card;ratio:0.9"></span> Credit</a>
                <ul class="uk-nav-sub">

                    <li><a href="/user/cga-credit"><span uk-icon="icon:arrow-right"></span> Comptes</a></li>
                    <!-- // -->
                </ul>
            </li>
            <li class="uk-parent">
              <a href="#" ><span uk-icon="icon:credit-card"></span> Afrocash</a>
              <ul class="uk-nav-sub">
                <li><a href="/user/afrocash/transactions"><span uk-icon="icon : arrow-right"></span>  Toutes les Transactions</a> </li>
              </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/credit-cga/commandes"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
                </ul>
            </li>
          </template>
          <!-- // -->
          <template id="" v-if="typeUser == 'logistique'">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Depots</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/ravitailler-depot"><span uk-icon="icon:arrow-right"></span> Ravitailler un depot</a></li>
                    <li><a href="/user/list-material"><span uk-icon="icon:arrow-right"></span> Tous les materiels</a></li>
                    <li><a href="/user/history-depot"><span uk-icon="icon:arrow-right"></span> Historique </a></li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/inventory"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li>
                    <li><a href="#"><span uk-icon="icon:arrow-right"></span> Historique de ravitaillement</a></li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/commandes"><span uk-icon="icon:thumbnails"></span> Toutes les Commandes</a></li>
                </ul>
            </li>
          </template>
          <template id="" v-if="typeUser == 'controleur'">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Rapport Ventes</a>
                <ul class="uk-nav-sub">
                    <li><a href="/user/add-rapport"><span uk-icon="icon:arrow-right"></span> Ajouter</a></li>
                    <li><a href="/user/all-rapport"><span uk-icon="icon:arrow-right"></span> Toutes les ventes</a></li>
                </ul>
            </li>
          </template>
          <template id="" v-if="typeUser == 'gdepot'">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Materiels</a>
                <ul class="uk-nav-sub">
                    <!-- LOGISTIC ONLY -->
                    <li><a href="/user/inventaire-depot"><span uk-icon="icon:arrow-right"></span> Stock</a></li>
                    <li><a href="/user/livraison"><span uk-icon="icon:arrow-right"></span> Commandes</a></li>
                </ul>
            </li>
          </template>
          <template id="" v-if="typeUser == 'coursier'">
            <!-- LE COURSIER -->
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Operations</a>
                <ul class="uk-nav-sub">
                  <li><a href="/user/recouvrement"><span uk-icon="icon:arrow-right"></span> Recouvrement</a></li>
                </ul>
            </li>
            <!-- // -->
          </template>

          <template v-if="typeUser !== 'admin'" id="">
            <li class="uk-parent">
              <a href="#"> <span uk-icon="icon : settings ; ratio : .9"></span> Parametres</a>
              <ul class="uk-nav-sub">
                <li><a href="/user/settings"><span uk-icon="icon:user"></span> Profile</a></li>
                <li>
                  <form action="/logout" method="post">
                    <input type="hidden" name="_token" :value="myToken">
                    <button style="border : none;" class="uk-button uk-button-small uk-button-default uk-text-capitalize" type="submit">
                      <span uk-icon="icon:sign-out"></span> Deconnexion
                    </button>
                </form>
                </li>
              </ul>
            </li>
          </template>
        </ul>
      </div>
    </div>
</div>
</div>
</template>

<script type="application/javascript">
    export default {
        mounted() {
          this.getAllNotifications()

          var channel = Echo.channel('notification');
          channel.listen('notify', function(data) {
            alert(JSON.stringify(data));
          });
        },
        props : {

        }
        ,
        data () {
          return {
            notifications : []
          }
        },
        methods : {
          getAllNotifications : async function () {
            try {
              let response = await axios.get('/user/notification/getlist')
              this.notifications = response.data
            } catch (e) {
                alert(e)
            }
          },
          readNotification : async function (id) {
            try {
                let response = await axios.post('/user/notification/mark-as-read',{
                  _token : this.myToken,
                  id_notify : id
                })
                if(response.data == 'done') {
                  this.getAllNotifications()
                }
            } catch(error) {
                alert(error)
            }
          }
        },
        computed : {
          alertInactifCount() {
            return this.$store.state.alertInactifCount
          },
          alertCount() {
            return this.$store.state.alertCount
          },
          deblocageCount() {
            return this.$store.state.deblocageCount
          },
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
          },
          unreadNotifications() {
            return this.notifications.filter((n) => {
              return n.status == "unread"
            })
          },
          readNotifications() {
            return this.notifications.filter((n) => {
              return n.status == "read"
            })
          }
        }
    }
</script>
