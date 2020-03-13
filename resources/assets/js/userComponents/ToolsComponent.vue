<template>
    <div>
        <!-- MODAL PLUS -->
        <div id="modal-plus" class="uk-modal-container" uk-modal="esc-close : false ; bg-close : false">
            <div class="uk-modal-dialog">
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">Plus</h2>
                </div>
                <div class="uk-modal-body uk-height-medium" uk-overflow>
                    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation : uk-animation-slide-bottom">
                        <li><a class="uk-button uk-button-small uk-border-rounded" href="#"><span uk-icon="icon : unlock"></span> Deblocage Compte Cga</a></li>
                        <li><a class="uk-button uk-button-small uk-border-rounded" href="#"><span uk-icon="icon : history"></span> Annulation de Saisie</a></li>
                    </ul>

                    <ul class="uk-switcher uk-margin">
                        <li>
                            <!-- DEBLOCAGE COMPTE -->
                            <div class="uk-alert-info uk-width-1-2@m uk-border-rounded uk-box-shadow-small" uk-alert>
                                <p><i class="material-icons uk-float-left">info</i> Envoyez une demand de deblocage de compte Cga</p>
                            </div>
                        </li>
                        <li>    
                            <!-- ERREUR DE SAISIE -->
                            <div class="uk-alert-info uk-width-1-2@m" uk-alert>
                                <p><i class="material-icons uk-float-left uk-border-rounded uk-box-shadow-small">info</i>Envoyez une demande d'annulation de Saisie</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="uk-modal-footer">
                    <button class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-modal-close">Fermer</button>
                </div>
            </div>
        </div>        
        <!-- // -->
        <!-- RIGHT ACCESSOIRES -->
        <radial-menu
        :itemSize="20"
        :radius="85"
        :angle-restriction="100">
        <radial-menu-item 
          v-for="(item, index) in items" 
          :key="index">
          <button :uk-toggle="'target: #'+modals[index]" class="more-tools uk-padding-remove uk-button-primary uk-button-small" :uk-tooltip="tooltips[index]">
              <i class="material-icons">{{item}}</i>
          </button>
        </radial-menu-item>
      </radial-menu>
        <!-- // -->
    </div>
</template>
<script>
import { Facebook } from 'vue-socialmedia-share';
import { WhatsApp } from 'vue-socialmedia-share';
import { RadialMenu,  RadialMenuItem } from 'vue-radial-menu'


export default {
    components : {
        Facebook,
        WhatsApp,
        RadialMenu,
        RadialMenuItem
    },
    mounted() {
        
    },
    data(){
        return {
            items: ['chat','feedback','share','more'],
            tooltips : ['Chat','Boit a suggestion','Reseaux Sociaux','Plus'],
            modals : ['','','','modal-plus']
        }
    }
}
</script>
