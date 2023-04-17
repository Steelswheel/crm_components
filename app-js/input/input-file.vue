<template>
    <div class="input-file">



        <div :class="`crop-${alias}`">


        </div>




        <!--  Список удаленных файлов      -->
        <div v-if="isShowDeleted">

            <div v-if="inValue.filter(i => i.isImage && !i.l && i.del).length > 0" class="mb-2 small ">
                <a :href="`/b/img/?ids=${inValue.filter(i => i.isImage && !i.l && i.del).map(i => i.ID)}`" target="_blank" class="mr-3 text-danger">Открыть фото</a>
                <a :href="`/b/img/?ids=${inValue.filter(i => i.isImage && !i.l && i.del).map(i => i.ID)}&pdf`" target="_blank" class="mr-3 text-danger">Открыть фото в PDF</a>
            </div>

            <div v-for="(file, key) in inValue.filter(i => !i.l && i.del)" :key="'1'+key">
                <div>
                    <a target="_blank" :href="file.get_src" class="text-line-through text-danger">{{file.FILE_NAME}}</a>
                    <span class="ml-1">
                        <span v-if="isUpdate" @click="cancelRemove(file.ID)" class="add-dotted">Отмена</span>
                    </span>
                </div>
            </div>
        </div>

        <div v-if="inValue.filter(i => i.isImage && !i.l && !i.del).length > 0" class="mb-2 small">
            <a :href="`/b/img/?ids=${inValue.filter(i => i.isImage && !i.l && !i.del).map(i => i.ID)}`" target="_blank" class="mr-3">Открыть фото</a>
            <a :href="`/b/img/?ids=${inValue.filter(i => i.isImage && !i.l && !i.del).map(i => i.ID)}&pdf`" target="_blank" class="mr-3">Открыть фото в PDF</a>
        </div>
        <div>


            <!-- Модалка с фото -->
            <b-modal
                ref="modal-photo"
                :title="label"
                :hide-header="true"
                :hide-footer="true"
                :no-fade="true"
                :modal-class="addFieldValue ? 'modalTwoWindow' : ''"

            >

                <div @click="closeModal" class="my-close-model">
                    <i class="el-icon-close text-danger"></i>
                </div>

                <div class="d-flex justify-content-center">

                    <div v-if="addFieldValue" style=" height: calc(100vh - 34px); width: 600px; margin-right: 50px">

                        <vasSlider :images="addFieldValue.filter(i => i.isImage && !i.l && !i.del)"/>

                    </div>

                    <div style="height: calc(100vh - 34px); width: 850px">

                        <vasSlider :images="inValue.filter(i => i.isImage && !i.l && !i.del)" :openImg="showPhoto"/>

                    </div>
                </div>

            </b-modal>



            <!--<div class="bx-file__fileName">
                {{file.FILE_NAME}}
            </div>-->

            <div class="">
                <!-- Список файлов, не картинки     -->
                <a  v-for="(file, key) in inValue.filter(i => !i.l && !i.isImage && !i.del)"
                    :key="'1'+key"
                    :href="file.get_src"
                    target="_blank"
                    :class="`bx-file bx-file--${file.ext}`"
                >
                    <div
                        v-if="isUpdate"
                        @click.prevent="remove(file.ID)"
                        href="#"
                        class="bx-file__control"><b-icon icon="x"/>
                    </div>

                </a>

                <!-- список фото с миниатюрами   -->
                <div
                    v-for="(fileImg, key) in inValue.filter(i => i.isImage && !i.l && !i.del)"
                    class="bx-file bx-file--img cursor-pointer"
                    @click.stop="open(key)"
                    :style="`background-image: url('${fileImg.img}')`"
                    :key="fileImg.ID"
                >
                    <div
                        v-if="isUpdate"
                        @click.prevent.stop="remove(fileImg.ID)"
                        href="#"
                        class="bx-file__control"><b-icon icon="x"/>
                    </div>
                </div>
            </div>

            <!--  Модалка с Crop  -->
            <b-modal
                ref="modal-crop"
                title="CROP"
                :hide-footer="true"
                :no-fade="true"
            >
                <div id="modal-crop-wrapper"></div>
                <el-button @click="cropServer">Обрезать</el-button>
            </b-modal>

            <!-- Загруженные КАРТИНКИ -->
            <div class="inputFile__uploadImg">
                <div v-for="(file, key) in inValue.filter(i => i.l && i.isImage)" :key="cropUpd+key" class="inputFile__uploadImg__item" >


                    <div class="inputFile__uploadImg__item__img">
                        <img class="img-fluid" :src="file.url" alt="" :style="`transform: rotate(${90*file.turn}deg)`">
                    </div>

                    <div class="d-flex justify-content-between">

                        <div>
                            <span v-if="!file.deleted && !file.loading" @click.prevent="removeLoad(file.ID)" class="cursor-pointer">
                                <small> <b-icon icon="trash-fill" class="text-secondary"/></small>
                            </span>

                        </div>
                        <div>
                            <span @click="turnRight(file.ID)" class="cursor-pointer">
                                 <b-icon icon="arrow-clockwise"/>
                            </span>
                            <span @click="turnLeft(file.ID)" class="cursor-pointer">
                                 <b-icon icon="arrow-counterclockwise"/>
                            </span>
                            <span @click="cropModal(file.ID)">
                                <b-icon icon="crop"/>
                            </span>
                        </div>



                    </div>




                    <el-progress v-if="file.loading" :percentage="file.progress"></el-progress>

                </div>

            </div>




            <!-- Загруженные файлы -->
            <div v-for="(file, key) in inValue.filter(i => i.l && !i.isImage)" :key="'0'+key">
                <div>
                    <span :class="{'text-line-through': file.deleted}">{{file.name}} </span>
                    <span class="ml-1">
                        <span v-if="file.error">ERROR</span>
                        <span v-if="!file.deleted && !file.loading" @click.prevent="removeLoad(file.ID)"><small> <b-icon icon="trash-fill" class="text-secondary"/></small>
                        </span>
                    </span>

                    <el-progress v-if="file.loading" :percentage="file.progress"></el-progress>

                </div>
            </div>
        </div>



        <el-button  v-if="isUpdate" @click="openInputFile" class="mr-1" type="default" plain  size="mini">{{btnName}}</el-button>

        <span
            @click="isShowDeleted = !isShowDeleted"
            v-if="fileDeleted.length > 0"
            class="add-dotted "
            :class="{'text-primary':isShowDeleted}"
        ><b-icon icon="trash-fill"/>{{fileDeleted.length}}</span>

        <input v-show="false" @change="onLoadFiles" type="file" multiple ref="fileUpload">
    </div>

</template>

<script>

import Crop from 'tinycrop';
import { Button, Progress } from 'element-ui'
import { BX_POST } from '@app/API'
import { cloneDeep, isEqual, isEmpty } from 'lodash'
import vasSlider from '@app/components/vas-slider';

export default {
    inheritAttrs: false,
    name: "input-file",
    components: {
        'el-button': Button,
        'el-progress': Progress,

        vasSlider
    },
    props: {
        btnName:{
            type: String,
            default: 'Добавить файл'
        },
        value: {
            type: Array,
            default: () => ([])
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        alias: String,
        attribute: {},
        label: String,
        test: {},
        file: Array,
        img: Array,
        dealId: {},
        addField: String,

    },
    data() {
        console.log();
        return {
            inValue: cloneDeep(this.value),
            loadFiles: undefined,
            showPhoto: 1,
            files: [],
            objectFile: {
                tmp: '',
                error: false,
                loading: true,
                deleted: false,
                progress: 0,
                crop: {},
                turn: 0,
                isImage: false,
            },
            isShowDeleted: false,
            sizeBlock:  0,
            cropData: {},
            cropUrl: '',
            cropId: null,
            cropUpd: 0,

        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input',this.inValue)
            }
        },
        value(){
            if (!isEqual(this.value,this.inValue)){
                this.inValue = cloneDeep(this.value)
            }
        },
        fileDeleted(){
            if (this.fileDeleted.length === 0){
                this.isShowDeleted = false
            }
        }
    },

    mounted(){

        if (localStorage.getItem('field' + this.alias)){
            this.onSizeBlock()
        }


    },
    computed: {
        isUpdate(){
            return this.isEdit
        },
        fileDeleted(){
            return this.inValue.filter(i => i.del)
        },
        addFieldValue:{
            get: function () {
                if(this.addField){
                    return this.$store.getters['form/GET_VALUE'](this.addField)
                }
                return false
            },

        },
    },
    methods: {
        openInputFile(){
            this.$refs.fileUpload.click();
        },

        removeLoad(id){

            this.inValue.splice(this.inValue.findIndex(i => i.ID === id),1)
        },
        cancelRemove(ID){
            this.inValue.find(i => i.ID === ID).del = 0
        },
        remove(ID){
            this.inValue.find(i => i.ID === ID).del = 1
        },
        onLoadFiles() {
            let filesObject = this.$refs.fileUpload.files

            for (let i = 0; i < filesObject.length; i++) {
                this.loadFile(filesObject[i]);
            }
            this.$refs.fileUpload.type = '';
            this.$refs.fileUpload.type = 'file';
        },
        loadFile(file){
            let id = this.inValue.length;
            this.inValue.push({ID:`n${id}`, l: true,  name: file.name, size: file.size, ...this.objectFile})
            const vm = this
            const progress = function(progressEvent) {
                vm.inValue[id].progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
            }
            this.$emit('lucked',true)
            BX_POST('vaganov:edp.show','upload', {file: file, id }, progress)
                .then(r => {
                    if (r.upload){
                        this.inValue[id].loading = false
                        this.inValue[id].tmp = r.tmp
                        this.inValue[id].url = r.url
                        this.inValue[id].isImage = r.isImage
                    }else{
                        this.inValue[id] = {
                            loading: false,
                            error: true,
                        }
                    }
                })
                .catch(() => {
                    this.inValue[id].loading = false
                    this.inValue[id].error = true
                    this.inValue[id].deleted = true
                })
                .finally(() => {
                    this.$emit('lucked',false)
                })
        },
        closeModal(){
            this.$refs['modal-photo'].hide()
            console.log(1);
        },
        open(key) {
            this.showPhoto = key
            this.$refs['modal-photo'].show()

        },

        onSizeBlock(){
            let r = this.$el.closest('.row');
            if (this.sizeBlock){
                r.childNodes[0].className = 'col-md-5'
                r.childNodes[1].className = 'col-md-7'
                this.sizeBlock = 0
                localStorage.removeItem('field'+this.alias)
            }else{
                r.childNodes[0].className = 'col-md-3'
                r.childNodes[1].className = 'col-md-9'
                this.sizeBlock = 1
                localStorage.setItem('field'+this.alias, this.sizeBlock)
            }
        },
        cropServer() {
            console.log(this.cropData);
            if(!isEmpty(this.cropData)){
                BX_POST('vaganov:edp.show','crop', {url: this.cropUrl, ...this.cropData})
                    .then(r => {
                        console.log(r.name);
                        this.$refs['modal-crop'].hide()
                        let img = this.inValue.find(i => i.ID === this.cropId)
                        console.log(img);
                        img.url = r.name
                        img.tmp = r.tmp
                        this.cropUpd++
                    })

            }else {
              this.$refs['modal-crop'].hide()
            }


        },
        cropModal(id){
            let img = this.inValue.find(i => i.ID === id)
            this.$refs['modal-crop'].show()

            this.cropData = {}
            this.cropUrl = img.url
            this.cropId = id

            this.$nextTick(() => {
                let crop = Crop.create({
                    parent: '#modal-crop-wrapper',
                    image: img.url,
                    bounds: {
                        width: '100%',
                        height: '50%'
                    },
                });

                crop.on('end', r => {
                    this.cropData.x = r.x
                    this.cropData.y = r.y
                    this.cropData.width = r.width
                    this.cropData.height = r.height
                });
            })



        },
        turnLeft(id){
            let img = this.inValue.find(i => i.ID === id)
            img.turn--
            if(img.turn < 0){
                img.turn = 3
            }
        },
        turnRight(id){
            let img = this.inValue.find(i => i.ID === id)
            img.turn++
            if(img.turn > 3){
                img.turn = 0
            }

        },
    }
}
</script>

<style scoped>
>>> .modalTwoWindow .modal-dialog{
    max-width: 1500px;
}
</style>