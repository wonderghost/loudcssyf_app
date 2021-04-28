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
        data : () => {
            return {
                users : []
            }
        },
        methods : {
            onInit : async function () {
                try
                {
                    let response = await axios.get('/maps')
                    if(response)
                    {
                        this.users = response.data

                        var map
                        const loader = new Loader({
                            apiKey : "AIzaSyBsuNR-F2eQdPFVh1N-tAtLpmFGxU91b00",
                            version : 'weekly',
                            libraries : ['places']
                        })

                        loader.load().then( () => {
                            map = new google.maps.Map(document.getElementById('map') , {
                                center : {lat : 9.64299737505792, lng : -13.579342659744581},
                                zoom : 10,
                            })

                            const infoWindow = new google.maps.InfoWindow()

                            this.users.forEach(element => {

                                const marker = new google.maps.Marker({
                                    position : {lat : element.lat , lng :  element.long},
                                    map : map,
                                    icon : "/img/afrocash.png",
                                    title : element.localisation,
                                    label : {text : element.localisation.substr(0,50,'...') , color : "blue"},
                                    animation : google.maps.Animation.DROP
                                })

                                const content = "<div>"+element.localisation+"</div>"+
                                    "<div>Telephone : "+element.phone+"</div>"

                                marker.addListener('click', () => {
                                    infoWindow.close()
                                    infoWindow.setContent(content)
                                    infoWindow.open(marker.getMap(),marker)
                                })

                            });



                        })
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
   height: 650px;
   /* The height is 400 pixels */
   width: 100%;
   /* The width is the width of the web page */
 }

</style>
