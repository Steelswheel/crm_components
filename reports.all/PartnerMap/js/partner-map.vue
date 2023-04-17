<template>
    <div class="map-partner" >

        <el-dialog
            v-if="!isLoading"
            :visible.sync="showAuthorizes"
            :modal-append-to-body="false"
            center
            width = '700px'
            :title="`КООПЕРАТИВНЫЙ УЧАСТОК ${showAuthorizesKey? authorizes[showAuthorizesKey].plot: ''}`"
            >

            <div v-if="showAuthorizesKey !== null" >
                <div>Уполномоченный: <b>{{authorizes[showAuthorizesKey].name}}</b></div>
                <div>Секретарь: <b>{{authorizes[showAuthorizesKey].secretary}}</b></div>
                <div>Адрес: <b>{{authorizes[showAuthorizesKey].address}}</b></div>




                <div class="mt-4">

                    <el-tabs v-model="tabsSelect">
                        <el-tab-pane
                            :label="`Регоны ${authorizes[showAuthorizesKey].regions.length}`"
                            name="regions">
                            <div v-for="code in authorizes[showAuthorizesKey].regions" :key="code">
                                <b>{{regionCode[code]}}</b>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane
                            :label="`Пайщики ${countGroup(authorizes[showAuthorizesKey].regions,countMap['shareholders'])}`"
                            name="pay">
                            <div v-for="code in authorizes[showAuthorizesKey].regions" :key="code" class="mb-2">
                                <div class="">
                                    <b >{{regionCode[code]}}</b>
                                    {{shareholders.filter(i => i.code === code).length}}
                                </div>
                                <div  v-for="item in shareholders.filter(i => i.code === code)" :key="item.ID">

                                    {{item.UF_FIO}}
                                </div>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane
                            :label="`Партнеры ${countGroup(authorizes[showAuthorizesKey].regions,countMap['partners'])}`"
                            name="partners">
                            <div v-for="code in authorizes[showAuthorizesKey].regions" :key="code" class="mb-2">
                                <div class="">
                                    <b>{{regionCode[code]}}</b>
                                    {{partners.filter(i => i.code === code).length}}
                                </div>
                                <div  v-for="partner in partners.filter(i => i.code === code)" :key="partner.id">
                                    <a :href="`/b/edp/?deal_id=${partner.id}/`">{{partner.fio}}</a>
                                </div>
                            </div>
                        </el-tab-pane>

                    </el-tabs>

                </div>
            </div>


        </el-dialog>

        <div style="position:sticky; background: #ffffff8f;" ref="positionSticky">
            <div v-if="!isLoading">
                <div class="authorizesList">
                    <div class="" v-for="(item, key) in authorizes" :key="key">
                        <div @click="openAuthorizes(key)" class="authorizesItem" :style="{'border-color':item.color}">
                            <div class="d-flex">
                                <div class="authorizesItem__bg" :style="{'background-color':item.color}">

                                </div>
                                <div class="authorizesItem__text">
                                    <div class="text-center1 font-weight-bold1 text-primary">КООПЕРАТИВНЫЙ УЧАСТОК #{{item.plot}}</div>

                                    <div class="authorizesItem__line">{{item.name}} <small class="text-secondary">({{item.secretary}})</small></div>

                                    <div>
                                        <span class="mr-2">Рерионов: {{item.regions.length}}</span>
                                        <span class="mr-2">Пайщики: <b>{{ countGroup(item.regions,countMap['shareholders']) }}</b></span>
                                        <span class="mr-2">Партнеры: {{ countGroup(item.regions,countMap['partners']) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="d-flex justify-content-between">
                    <div class="d-flex" >
                        <updateRegister class="mr-2"/>
                        <div class="mr-2">  Пайщики: {{shareholders.length}}, партнеры: {{partners.length}} </div>
                        <a href="#" @click.prevent="dwn1" class="mr-2">Скачать</a>
                        <a href="#" @click.prevent="dwn2">Скачать exel</a>
                    </div>
                    <div ref="regionHover"></div>
                    <div>
                        <el-radio-group v-model="mapView">
                            <el-radio label="shareholders">Пайщики</el-radio>
                            <el-radio label="partners">Партнеры</el-radio>
                        </el-radio-group>
                    </div>
                </div>
            </div>

        </div>


        <rf-map
            @regionHover="onRegionHover"
            @regionClick="onRegionClick"
            :text="countMap[mapView]"
            :color="color"
        />

    </div>
</template>

<script>
import { saveAs } from 'file-saver'
import { Document, Packer, Paragraph, HeadingLevel } from 'docx'
import { BX_POST } from '@app/API';
import { fixPositionSticky } from '@app/helper';
import { RadioGroup, Radio, Dialog, Tabs, TabPane} from 'element-ui'
import rfMap from '@app/components/rf-map'
import updateRegister from './updateRegister'

export default {
    name: "mapPartner",
    props: {
    },
    components: {
        updateRegister,
        rfMap,
        'el-radio-group': RadioGroup,
        'el-radio': Radio,
        'el-dialog': Dialog,
        'el-tabs': Tabs,
        'el-tab-pane': TabPane,

    },
    data() {
        return {
            tabsSelect: 'regions',
            showAuthorizes: false,
            showAuthorizesKey: null,
            isLoading: true,
            partners: null,
            regionCode: null,
            shareholders: null,
            authorizes: null,

            mapView: 'shareholders',

            text: {},
            color: {},
            countMap: {
                'shareholders': {},
                'partners': {},
            }
        }
    },


    mounted() {
        this.$nextTick(() => {
            fixPositionSticky(this.$refs.positionSticky,'');
        })
        this.load()
    },
    methods: {
        onRegionHover(code){
            if(this.$refs.regionHover){
                this.$refs.regionHover.innerHTML = code ? this.regionCode[code] : ''
            }
        },
        onRegionClick(code){
            console.log(code);
        },
        load(){

            BX_POST('vaganov:reports.all', 'PartnerMapLoad')
                .then(r => {
                    this.partners = r.partners
                    this.regionCode = r.regionCode
                    this.shareholders = r.shareholders
                    this.authorizes = r.authorizes

                    this.countMap = {
                        shareholders: this.countCode(this.shareholders),
                        partners: this.countCode(this.partners),
                    }
                    this.setColor(this.authorizes)

                }).finally(() => {
                    this.isLoading = false
                })
        },
        openAuthorizes(key){
            this.showAuthorizes = true
            this.showAuthorizesKey = key
            this.tabsSelect = 'regions'
        },
        setColor(data){
            let color = {}
            data.forEach(i => {
                i.regions.forEach(code => {
                    this.$set(color,code,i.color)
                })
            })
            this.color = color
        },
        countCode(data){
            let countData = {}
            for(let code in this.regionCode){
                let codeFilter = data.filter(i => i.code === code)
                if(codeFilter.length){
                    countData[code] = codeFilter.length
                }
            }
            return countData;
        },
        countGroup(codes,data){
            let acm = 0
            if(codes){
                codes.forEach(code => {
                    if(data[code]){
                        acm +=data[code]
                    }
                })
            }
            return acm
        },
        saveDocumentToFile(doc, fileName) {
            // Create a mime type that will associate the new file with Microsoft Word
            const mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            // Create a Blob object from Packer containing the Document instance and the mimeType
            Packer.toBlob(doc).then((blob) => {
                const docblob = blob.slice(0, blob.size, mimeType)
                // Save the file using saveAs from the file-saver package
                saveAs(docblob, fileName)
            })
        },
        dwn1(){

            let children = []

            console.log(this.authorizes);
            this.authorizes.forEach((item, key) => {

                children.push(new Paragraph({ text: `КООПЕРАТИВНЫЙ УЧАСТОК ${key + 1}`, heading: HeadingLevel.HEADING_1}))
                children.push(new Paragraph({ text: `Уполномоченный: ${item.name}`}))
                children.push(new Paragraph({ text: `Секретарь: ${item.secretary}`}))
                children.push(new Paragraph({ text: `Адрес: ${item.address}`}))
                children.push(new Paragraph({ text: `Регионы:`}))

                item.regions.forEach(code => {
                    children.push(new Paragraph({ text: this.regionCode[code]}))
                })
            })


            let doc = new Document({
                sections: [
                    {
                        children,
                    },
                ],
            })
            this.saveDocumentToFile(doc, 'Пайщики.docx')
        },
        dwn2(){
            BX_POST('vaganov:reports.all', 'PartnerMapRegister')
                .then(r => {
                    location.href = r
                }).finally(() => {
                this.isLoading = false
            })
        }


    }
}
</script>

<style scoped>

>>> path {
    fill: #e9e9e9;
    stroke: #343434;
    stroke-width: 0.5;
}

.authorizesItem{
    margin: 1px;
    border: solid 1px #0A3A68;
    background: white;
}

.authorizesItem__bg{
    width: 30px;
    flex-shrink: 0;
}
.authorizesItem__text{
    line-height: 16px;
    padding: 3px;
    width: calc(100% - 30px);
}
.authorizesItem__line{
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.authorizesList{
    display: flex;
    flex-wrap: wrap;
    align-content: flex-start;
    height: 100%;

}
.authorizesList > div{
    width: 25%
}


</style>