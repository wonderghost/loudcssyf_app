<template>
  <div class="">

  <div class="uk-navbar-container uk-box-shadow-small" id="entete" uk-sticky uk-navbar>
    <div class="uk-navbar-left">
        <button class="uk-navbar-item uk-button" uk-toggle="target:#side-nav" uk-icon="icon:menu"></button>
        <!-- <a href="" class="uk-navbar-item uk-logo uk-visible@m">LAYE DISTRIBUTION</a> -->
        <a href="/" class="uk-navbar-item uk-logo uk-visible@m">
          <img src="/img/layedist.png" width="100" alt="">
        </a>
        <a href="" class="uk-navbar-item uk-logo uk-hidden@m">
          <img src="/img/layedist.png" width="70" alt="">
        </a>
    </div>
    <div class="uk-navbar-center uk-visible@m">
      <span uk-tooltip="Tableau de bord" class="uk-margin-right">
        <router-link to="/dashboard"><i class="material-icons">dashboard</i></router-link>
      </span>
      <span v-if="typeUser == 'admin' || typeUser == 'commercial'" uk-tooltip="Performances" class="uk-margin-right">
        <router-link to="/performances"><i class="material-icons">timeline</i></router-link>
      </span>
      <span v-if="typeUser == 'admin' || typeUser == 'commercial'" uk-tooltip="Objectifs" class="uk-margin-right">
        <router-link to="/objectifs/visu"><i class="material-icons">track_changes</i></router-link>
      </span>
      <span v-if="typeUser == 'v_da' || typeUser == 'v_standart'" uk-tooltip="Objectifs" class="uk-margin-right">
        <router-link to="/objectifs-user"><i class="material-icons">track_changes</i></router-link>
      </span>
      
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
      <span uk-tooltip="Alertes" v-if="typeUser == 'v_da' || typeUser == 'v_standart' || typeUser == 'admin' || typeUser == 'commercial'" class="uk-button uk-button-small border-button">
        <router-link to="/alertes/abonnement"><i class="material-icons">alarm</i></router-link>
      </span>
      <template v-if="typeUser == 'admin' || typeUser == 'commercial'" id="">
        <span uk-tooltip="Promo">
          <router-link to="/promo"><span uk-icon="icon : tag"></span>  PROMO</router-link>
        </span>
      </template>
    <template v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'commercial'" id="">
      <router-link to="/pay-comission" class="uk-button uk-button-small border-button button-pay-comission"><i class="material-icons">monetization_on</i></router-link>
      <span class="" uk-tooltip="Reabonnement Afrocash">
        <router-link to="/all-ventes-pdraf"><span uk-icon="grid"></span></router-link>
      </span>
    </template>
      <a class="uk-button uk-button-small border-button" uk-tooltip="Recherche" uk-toggle="target : #modal-search-serial"><i class="material-icons">search</i></a>
      <!-- <a v-if="typeUser == 'v_da'" class="uk-button uk-button-small border-button" href="#modal-remboursement" uk-toggle uk-tooltip="Paiement Remboursement"><i class="material-icons">payment</i></a> -->
      <router-link v-if="typeUser == 'v_da'" to="/promo"><i class="material-icons">payment</i></router-link>
      <a class="uk-button uk-button-small border-button" uk-toggle href="#modal-deblocage" uk-tooltip="Deblocage Cga"><i class="material-icons">lock_open</i><span v-if="typeUser !=='v_da' && typeUser !== 'v_standart'">{{deblocageCount}}</span> </a>
      <a v-if="typeUser == 'admin'" class="uk-button uk-button-small border-button" uk-toggle href="#modal-reactivation-materiel" uk-tooltip="Reactivation Materiels"><i class="material-icons">settings_backup_restore</i><span v-if="typeUser !=='v_da' && typeUser !== 'v_standart'">{{reactivationCount}}</span> </a>
    </div>
    <div class="uk-navbar-right uk-visible@m">
      <a class="uk-button"><span uk-icon="icon : user ;"></span> {{userLocalisation}} </a>
      <form action="/logout" method="post">
        <input type="hidden" name="_token" :value="myToken">
        <button class="uk-button uk-button-small uk-button-link uk-margin-right border-button" type="submit" uk-tooltip="Deconnexion"><i class="material-icons">power_settings_new</i></button>
    </form>
    </div>
    <div class="uk-hidden@m uk-text-center bottom-content uk-box-shadow-small">
      <span>
        <router-link to="/dashboard" class="uk-margin-small"><i class="material-icons">dashboard</i></router-link>
      </span>
      <span v-if="typeUser == 'admin' || typeUser == 'commercial'" class="">
        <router-link to="/performances" class="uk-margin-small"><i class="material-icons">timeline</i></router-link>
      </span>
      <span v-if="typeUser == 'admin' || typeUser == 'commercial'" class="">
        <router-link to="/objectifs/visu" class="uk-margin-small"><i class="material-icons">track_changes</i></router-link>
      </span>
      <span v-if="typeUser == 'v_da' || typeUser == 'v_standart'" class="">
        <router-link to="/objectifs-user" class="uk-margin-small"><i class="material-icons">track_changes</i></router-link>
      </span>
      <a class="uk-button uk-button-small border-button" href="#all-notification" uk-toggle uk-tooltip="Notifications"><i class="material-icons">notifications</i><span class="">{{unreadNotifications.length}}</span> </a>
      <span>
        <router-link to="/promo" class="uk-margin-small"><i class="material-icons">card_giftcard</i></router-link>
      </span>
      <template v-if="typeUser == 'admin' || typeUser == 'gcga' || typeUser == 'commercial'" id="">
        <router-link to="/pay-comission" class="uk-button uk-button-small border-button button-pay-comission"><i class="material-icons">monetization_on</i></router-link>
        <span class="" uk-tooltip="Reabonnement Afrocash">
          <router-link to="/all-ventes-pdraf"><i class="material-icons">apps</i></router-link>
        </span>
      </template>
      <a class="uk-button uk-button-small border-button" uk-tooltip="Recherche" uk-toggle="target : #modal-search-serial"><i class="material-icons">search</i></a>
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
                    <!-- <li><a href="/admin/add-user"><span uk-icon="icon:arrow-right"></span> Nouveau</a></li> -->
                    <li>
                      <router-link to="/user/add"><span uk-icon="icon : link"></span> Nouveau</router-link>
                    </li>

                    <li>
                      <router-link to="/user/list"><span uk-icon="link"></span> Tous les Utilisateur</router-link>
                    </li>
                    <li>
                      <router-link to="/pdraf/list"><span uk-icon="link"></span> Creation Pdraf</router-link>
                    </li> 
                    <li>
                      <router-link to="/reseaux/afrocash"><span uk-icon="link"></span> Reseaux Afrocash</router-link>
                    </li>
                    <!-- <li><a href="/admin/pdraf/list"><span uk-icon="icon : arrow-right"></span> Creation Pdraf</a></li> -->
                </ul>
            </li>
            <li class="uk-parent" >
                <a href="#"><span uk-icon="icon:credit-card;ratio:0.9"></span> Compte Credit</a>
                <ul class="uk-nav-sub">
                    <!-- <li><a href="/admin/add-account-credit"><span uk-icon="icon:arrow-right"></span> Comptes</a></li> -->
                    <li>
                      <router-link to="/account"><span uk-icon="link"></span> Compte Credit</router-link>
                    </li>
                </ul>
            </li>
            <li class="uk-parent">
              <a href="#"><span uk-icon="icon: credit-card;"></span> Afrocash</a>
              <ul class="uk-nav-sub">
                <!-- <li><a href="/admin/afrocash"><span uk-icon="icon :arrow-right;"></span> Operations</a>	</li> -->
                <li>
                  <router-link to="/afrocash/operation"><span uk-icon="link"></span> Operation</router-link>
                </li>
                <!-- <li><a href="/admin/afrocash/all-transactions"><span uk-icon="icon : arrow-right;"></span> Toutes les transactions</a>	</li> -->
                <li>
                  <router-link to="/afrocash/all-transaction"><span uk-icon="link"></span> Toutes les transactions</router-link>
                </li>
                <!-- <li><a href="/admin/recouvrement/operations"><span uk-icon="icon : arrow-right"></span> Recouvrements</a></li> -->
                <li>
                  <router-link to="/afrocash/recouvrement"><span uk-icon="link"></span> Recouvrements</router-link>
                </li>
              </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Materiels</a>
                <ul class="uk-nav-sub">
                    <!-- <li><a href="/admin/add-depot"><span uk-icon="icon:arrow-right"></span> Entrepot</a></li> -->
                    <li>
                      <router-link to="/material/entrepot"><span uk-icon="link"></span> Entrepot</router-link>
                    </li>
                    <li>
                      <router-link to="/material/all-material"><span uk-icon="link"></span> Tous les materiels</router-link>
                    </li>
                    <li>
                      <router-link to="/material/affectation"><span uk-icon="link"></span> Affectation Materiel</router-link>
                    </li>
                    <!-- <li><a href="/admin/depot-central"><span uk-icon="icon:arrow-right"></span> Entrepot</a></li> -->
                    <!-- <li><a href="/admin/list-material"><span uk-icon="icon:arrow-right"></span> Tous les materiels</a></li> -->
                    <!-- <li><a href="/admin/history-depot"><span uk-icon="icon:arrow-right"></span> Historique </a></li> -->
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                <ul class="uk-nav-sub">
                    <!-- <li><a href="/admin/inventory"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li> -->
                    <li>
                      <router-link to="/inventory"><span uk-icon="link"></span> Inventaire</router-link>
                    </li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                <ul class="uk-nav-sub">
                    <li>
                      <router-link to="/commandes"><span uk-icon="link"></span> Toutes les Commandes</router-link>
                    </li>
                    <li>
                      <router-link to="/setting/command/"><span uk-icon="link"></span> Parametres Commande</router-link>
                    </li>
                </ul>
            </li>
            <li class="uk-parent">
              <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Rapport Ventes</a>
              <ul class="uk-nav-sub">
                  <!-- <li><a href="/admin/add-rapport"><span uk-icon="icon:arrow-right"></span> Ajouter</a></li> -->
                  <li>
                    <router-link to="/rapport/add"><span uk-icon="link"></span> Ajouter</router-link>
                  </li>
                  <li>
                    <router-link to="/rapport/list"><span uk-icon="link"></span> Toutes les ventes</router-link>
                  </li>
                  <!-- <li><a href="/admin/all-rapport"><span uk-icon="icon:arrow-right"></span> Toutes les ventes</a></li> -->
              </ul>
          </li>
          </template>
          <template v-if="typeUser == 'admin'">
            <li class="uk-parent">
            <a href="#"> <span uk-icon="icon : settings ; ratio : .9"></span> Parametres</a>
            <ul class="uk-nav-sub">
              <li>
                <router-link to="/setting/formule"><span uk-icon="link"></span> Formule</router-link>
              </li>
              <li>
                <router-link to="/setting/profile"><span uk-icon="link"></span> Profile</router-link>
              </li>
              <li><a href="#" uk-toggle="target : #rapport-setting-modal"><span uk-icon="icon : minus"></span> Reglage Rapport de vente</a></li>
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
                    <li>
                      <router-link to="/rapport/list"><span uk-icon="link"></span> Rapport de ventes</router-link>
                    </li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/inventory"><span uk-icon="link"></span> Inventaire</router-link>
                  </li>
                    <!-- <li><a href="/user/my-inventory"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li> -->
                    <!-- <li><a href="/user/my-history-ravitaillement"><span uk-icon="icon:arrow-right"></span> Historique de ravitaillement</a></li> -->
                </ul>
            </li>

            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:users;ratio:0.9"></span> Carnet d'adresse</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/client/add"><span uk-icon="link"></span> Nouveau</router-link>
                  </li>
                  <li>
                    <router-link to="/client/list"><span uk-icon="link"></span> Repertoire</router-link>
                  </li>
                </ul>
            </li>
            <!-- AFROCASH -->
            <li class="uk-parent">
              <a href="#"><span uk-icon="icon : credit-card"></span> Afrocash</a>
              <ul class="uk-nav-sub">
                <li>
                  <router-link to="/afrocash/user-operation"><span uk-icon="link"></span> Operation</router-link>
                </li>
                <li>
                  <router-link to="/afrocash/transactions"><span uk-icon="link"></span>Toutes les Transactions</router-link>
                </li>
              </ul>
            </li>
            <!-- // -->
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/command/new"><span uk-icon="link"></span> Nouvelle Commande</router-link>
                  </li>
                  <li>
                    <router-link to="/command/list"><span uk-icon="link"></span>Toutes les commandes</router-link>
                  </li>
                    <!-- <li><a href="/user/new-command"><span uk-icon="icon:arrow-right"></span> Nouvelle Commande</a></li> -->
                    <!-- <li><a href="/user/list-command"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li> -->
                </ul>
            </li>
          </template>
          <!-- GESTIONNAIRE CGA -->
          <template v-if="typeUser == 'gcga'" id="">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:credit-card;ratio:0.9"></span> Credit</a>
                <ul class="uk-nav-sub">
                    <li>
                      <router-link to="/account"><span uk-icon="link"></span> Compte Credit</router-link>
                    </li>
                </ul>
            </li>
            <li class="uk-parent">
              <a href="#" ><span uk-icon="icon:credit-card"></span> Afrocash</a>
              <ul class="uk-nav-sub">
                <li>
                  <router-link to="/afrocash/all-transaction"><span uk-icon="link"></span> Toutes les transactions</router-link>
                </li>
              </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                <ul class="uk-nav-sub">
                    <li>
                      <router-link to="/commande-credit/all"><span uk-icon="link"></span> Toutes les Commandes</router-link>
                    </li>
                </ul>
            </li>
          </template>
          <!-- // -->
          <template id="" v-if="typeUser == 'logistique'">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Depots</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/depot/ravitailler"><span uk-icon="link"></span> Ravitailler un depot</router-link>
                  </li>
                  <li>
                    <router-link to="/material/all-material"><span uk-icon="link"></span> Tous les materiels</router-link>
                  </li>
                    <!-- <li><a href="/user/history-depot"><span uk-icon="icon:arrow-right"></span> Historique </a></li> -->
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/inventory"><span uk-icon="link"></span> Inventaire</router-link>
                  </li>
                    <!-- <li><a href="#"><span uk-icon="icon:arrow-right"></span> Historique de ravitaillement</a></li> -->
                </ul>
            </li>
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/command/list"><span uk-icon="link"></span> Toutes les Commandes</router-link>
                  </li>
                    <!-- <li><a href="/user/commandes"><span uk-icon="icon:thumbnails"></span> Toutes les Commandes</a></li> -->
                </ul>
            </li>
          </template>
          <template id="" v-if="typeUser == 'controleur'">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Rapport Ventes</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/rapport/add"><span uk-icon="link"></span> Ajouter</router-link>
                  </li>
                  <li>
                    <router-link to="/rapport/list"><span uk-icon="link"></span> Toutes les ventes</router-link>
                  </li>
                </ul>
            </li>
          </template>
          <template id="" v-if="typeUser == 'gdepot'">
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Materiels</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/material/all-material"><span uk-icon="link"></span> Stock</router-link>
                  </li>
                  <li>
                    <router-link to="/livraison/all"><span uk-icon="link"></span> Livraison</router-link>
                  </li>
                </ul>
            </li>
          </template>
          <template id="" v-if="typeUser == 'coursier'">
            <!-- LE COURSIER -->
            <li class="uk-parent">
                <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Operations</a>
                <ul class="uk-nav-sub">
                  <li>
                    <router-link to="/afrocash/recouvrement"><span uk-icon="link"></span> Recouvrement</router-link>
                  </li>
                </ul>
            </li>
            <!-- // -->
          </template>
          <!-- PDC -->
          <template v-if="typeUser == 'pdc'">
            <li class="uk-parent">
              <a href="#"><span uk-icon="icon : credit-card"></span> Afrocash</a>
              <ul class="uk-nav-sub">
                <li>
                  <router-link to="/operation"><span uk-icon="icon : link"></span> Operation</router-link>
                </li>
                <li>
                  <router-link  to="/transactions"><span uk-icon="icon : link"></span> Toutes les transactions</router-link>
                </li>
                <li>
                  <router-link  to="/inventaire-pdraf"><span uk-icon="icon : link"></span> Inventaire PDRAF</router-link>
                </li>
              </ul>
            </li>
            <li class="uk-parent">
              <a href="#"><span uk-icon="icon : cog"></span> Materiel</a>
              <ul class="uk-nav-sub">
                <li>
                  <router-link to="/pdc/command/new"><span uk-icon="icon : link"></span> Commandes</router-link>
                </li>
                <li>
                  <router-link to="/pdc/material/inventory"><span uk-icon="icon : link"></span> Inventaire Stock</router-link>
                </li>
              </ul>
            </li>
            
            <li class="uk-parent">
              <a href="#"><span uk-icon="users"></span> Utilisateurs</a>
              <ul class="uk-nav-sub">
                <li>
                    <router-link to="/add-pdraf"><span uk-icon="icon : link"></span> Ajoutez un PDRAF</router-link>
                </li>
                <li>
                    <router-link to="/all-users"><span uk-icon="icon : list"></span> Tous les Utilisateurs</router-link>
                </li>
                <li>
                    <router-link to="/all-make-pdraf"><span uk-icon="icon : list"></span> Creation Pdraf</router-link>
                </li>
              </ul>
            </li>
            <li class="uk-parent">
              <a href="#"><span uk-icon="cart"></span> Ventes</a>
              <ul class="uk-nav-sub">
                <li class="">
                  <router-link to="/all-ventes"><span uk-icon="icon : link"></span> Toutes les ventes</router-link>
                </li>
              </ul>
            </li>
          </template>
          <!-- PDRAF -->
          <template v-if="typeUser == 'pdraf'">
            <li class="uk-parent">
              <a href="#"><span uk-icon="credit-card"></span> Afrocash</a>
              <ul class="uk-nav-sub">
                <li class="">
                  <router-link  to="/transfert-courant"><span uk-icon="icon : link"></span>Transfert Courant</router-link>
                </li>
                <li>
                  <router-link  to="/retour-afrocash"><span uk-icon="icon : link"></span> Retour Afrocash</router-link>
                </li>
                <li>
                  <router-link  to="/all-transaction-pdraf"><span uk-icon="icon : link"></span>Toutes les transactions</router-link>
                </li>
              </ul>
            </li>
            <li class="uk-parent">
              <a href="#"><span uk-icon="cog"></span> Materiel</a>
              <ul class="uk-nav-sub">
                 <li>
                  <router-link to="/pdraf/command/new"><span uk-icon="icon : link"></span> Commande Materiel</router-link>
                </li>
                <li>
                  <router-link to="/pdraf/materiel/inventory"><span uk-icon="icon : link"></span> Inventaire Stock</router-link>
                </li>
              </ul>
            </li>
            <li class="uk-parent">
              <a href="#"><span uk-icon="cart"></span> Ventes</a>
              <ul class="uk-nav-sub">
                <li>
                  <router-link to="/recrutement-afrocash"><span uk-icon="icon : link"></span> Recrutement</router-link>
                </li>
                <li class="">
                  <router-link to="/reabonnement-afrocash"><span uk-icon="icon : link"></span> Reabonnement</router-link>
                </li>
                <li>
                    <router-link to="/all-ventes-pdraf"><span uk-icon="icon : link"></span> Toutes les ventes</router-link>
                </li>
              </ul>
            </li>
          </template>
          <!-- // -->
          <template v-if="typeUser !== 'admin'" id="">
            <li class="uk-parent">
              <a href="#"> <span uk-icon="icon : settings ; ratio : .9"></span> Parametres</a>
              <ul class="uk-nav-sub">
                <li>
                  <router-link to="/setting/profile"><span uk-icon="link"></span> Profile</router-link>
                </li>
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

          // var channel = Echo.channel('notification');
          // channel.listen('notify', function(data) {
          //   alert(JSON.stringify(data));
          // });
        },
        props : {

        }
        ,
        data () {
          return {
            notifications : [],
            alerts : [],
          }
        },
        methods : {
          getAllNotifications : async function () {
            try {
              let response = await axios.get('/user/notification/getlist')
              // let alertResponse = await axios.get('/admin/alert-abonnement/count')
              if(response) {
                // this.alerts = alertResponse.data
                this.notifications = response.data
              }
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
          },
          reactivationCount() {
            return this.$store.state.reactivationCount
          }
        }
    }
</script>
