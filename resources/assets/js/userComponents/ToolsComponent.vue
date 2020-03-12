<template>
    <div>
        <!-- RIGHT ACCESSOIRES -->
        <div class="more-tools uk-padding-remove">
            <div>
                <a class="uk-button" uk-tooltip="Outils"><i class="material-icons">more</i> </a>    
            </div>
            <div>
                <a class="uk-button" uk-tooltip="Boite a suggestion"><i class="material-icons">feedback</i></a>
            </div>
            <div>
                <a class="uk-button" uk-tooltip="Reseaux Sociaux"><i class="material-icons">share</i></a>
            </div>
            <div>
                <a class="uk-button" uk-tooltip="Chat"><i class="material-icons">chat</i></a>
            </div>
        </div>
        <!-- // -->
    </div>
</template>
<script>
import { Facebook } from 'vue-socialmedia-share';
import { WhatsApp } from 'vue-socialmedia-share';
export default {
    components : {
        Facebook,
        WhatsApp
    },
    mounted() {
        
    },
    data(){
        return {

        }
    }
}
</script>
