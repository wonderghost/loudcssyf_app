<template>
<div class="">
  <loading :active.sync="isLoading"
      :can-cancel="true"
      :is-full-page="fullPage"></loading>

      <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-bottom">
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit CGA</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Credit REX</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">AFROCASH SEMI GROSSISTE</a></li>
      </ul>

      <ul class="uk-switcher uk-margin">

          <!-- ENVOI DE COMMANDE MATERIEL -->
          <li>
            <!-- COMMANDE MATERIEL -->
            <form class="" action="index.html" method="post">

            <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
              <div>
                  <label uk-tooltip="">Kit Complet</label>
              </div>
              <div id="">
                <label>Qte <input type="text" class="uk-input" name="" value=""> </label>
              </div>
              <div>
                <label>Prix TTC (GNF)<input type="text" class="uk-input" name="" value=""></label>
              </div>
              <div>
                <label>HT (GNF)<input type="text" class="uk-input" name="" value=""></label>
              </div>
              <div>
                <label>TVA (18%) (GNF)<input type="text" class="uk-input" name="" value=""></label>
              </div>
              <div>
                <label>Montant TTC (GNF)<input type="text" class="uk-input" name="" value=""></label>
              </div>
              <!-- SUBVENTION -->
              <div></div>
              <div><label>Subvention</label></div>
              <div>
              <input type="text" class="uk-input" name="" value="">
              </div>
              <div>
              <input type="text" class="uk-input" name="" value="">
              </div>
              <div>
                <input type="text" class="uk-input" name="" value="">
              </div>
              <div>
                <input type="text" class="uk-input" name="" value="">
              </div>
              <!-- MARGE -->
              <div></div>
              <div><label>Marge</label></div>
              <div>
                <input type="text" class="uk-input" name="" value="">
              </div>
              <div>
                <input type="text" class="uk-input" name="" value="">
              </div>
              <div>
                <input type="text" class="uk-input" name="" value="">
              </div>
              <div>
                <input type="text" class="uk-input" name="" value="">
              </div>
            </div>

            <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
              <div>
                  <label uk-tooltip="">Migration</label>
              </div>
              <div id="">
                <label>Qte<input type="text" class="uk-input" name="" value=""></label>
              </div>
            </div>
            <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
              <div>
                  <label uk-tooltip="">Paraboles dûes</label>
              </div>
              <div id="">
                <label>Qte<input type="text" class="uk-input" name="" value=""></label>
              </div>
            </div>

            <hr class="uk-divider-small">
            <div class="uk-grid-collapse" uk-grid>
              <div class="uk-width-5-6@m">
                <span>TOTAL TTC-MATERIEL (GNF)</span>
              </div>
              <div class="uk-width-1-6@m">
                <input type="text" class="uk-input" name="" value="">
              </div>
            </div>
            <div class="uk-grid-collapse" uk-grid>
              <div class="uk-width-5-6@m">
                <span>TOTAL SUBVENTION (GNF)</span>
              </div>
              <div class="uk-width-1-6@m">
                <input type="text" class="uk-input" name="" value="">
              </div>
            </div>
            <div class="uk-grid-collapse uk-text-lead" uk-grid>
              <div class="uk-width-5-6@m">
                <span>TOTAL A PAYER (GNF)</span>
              </div>
              <div class="uk-width-1-6@m">
                <input type="text" class="uk-input" name="" value="">
              </div>
            </div>
            <hr class="uk-divider-small">
            <button type="submit" class="uk-button uk-button-small uk-button-primary uk-box-shadow-small uk-border-rounded uk-box-shadow-small">valider<span uk-icon="icon:check;ratio:.8"></span></button>
          </form>
          </li>
          <!-- // -->
        <li></li>
        <li></li>
        <li></li>
      </ul>

</div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

    export default {
      components : {
        Loading
      },
        mounted() {

        },
        data () {
          return {
            isLoading : false,
            fullPage : true
          }
        }

    }
</script>
