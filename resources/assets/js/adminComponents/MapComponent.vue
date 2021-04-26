<template>
    <div>
        <div id="map" class="map"></div>
    </div>
</template>
<script>
import { Loader } from '@googlemaps/js-api-loader'
    export default {
        created() {
            this.onInit()
        },
        mounted() {},
        methods : {
            onInit : async function () {
                try
                {
                    var map
                    const loader = new Loader({
                        apiKey : "AIzaSyBsuNR-F2eQdPFVh1N-tAtLpmFGxU91b00",
                        version : 'weekly',
                        libraries : ['places']
                    })

                    loader.load().then( () => {
                        map = new google.maps.Map(document.getElementById('map') , {
                            center : {lat : 9.64299737505792, lng : -13.579342659744581},
                            zoom : 8,
                        })

                        const marker = new google.maps.Marker({
                            position : {lat : 9.723544017097593 , lng :  -13.41386109461232},
                            map : map
                        })
                    })

                    let response = await axios.get('/maps')
                    if(response)
                    {
                        console.log(response.data)
                    }
                }
                catch(error)
                {
                    alert(error)
                }
            }
        }
    }
</script>
<style>
 /* Set the size of the div element that contains the map */
 #map {
   height: 500px;
   /* The height is 400 pixels */
   width: 100%;
   /* The width is the width of the web page */
 }

</style>
