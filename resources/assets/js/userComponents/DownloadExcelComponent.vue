<template>
    <div>
        <download-excel
            class="uk-button uk-button-small uk-padding-remove uk-border-rounded uk-button-primary download-btn"
            uk-tooltip="Telechargez les donnees au format excel"
            :data="dataToExport"
            :fields="dataFields"
            worksheet="Mon classeur"
            :name="fileName+'.xls'">
                <span uk-icon="download"></span>
        </download-excel>
    </div>
</template>
<script>
import JsonExcel from 'vue-json-excel'

    export default {
        components : {
            downloadExcel : JsonExcel
        },
        props : {
            dataToExport : Array,
            dataFields : Object,
            fileName : String
        },
        mounted() {},
        data() {
            return {
                dataSetting : {
                    json_meta : [
                        [
                            {
                                key : 'charset',
                                value : 'utf-8'
                            }
                        ]
                    ]
                }
            }
        }
    }
</script>
<style lang="css">
.download-btn {
    position : fixed !important;
    right : 1% !important;
    bottom : 30% !important;
    background : rgba(5, 5, 7, 0.3) !important;
}

.download-btn:hover{
    background : rgba(77,74,252,1) !important;
}
</style>