<template>
    <div>

        <el-button
            v-if="isEdit"
            @click="openInputFile"
            :loading="isLoading"
            class="mr-1"
            type="success"
            plain
            size="mini"
        >Распознать реквизиты по фото, PDF</el-button>

        <div class="text-danger">{{errorText}}</div>

        <el-dialog
            title="ПАСПОРТ API"
            :visible.sync="dialogVisible"
            width="70%"
            :modal-append-to-body="false"
        >

            <div class="row">
                <div class="col-md-6">
                    <div style="position: sticky; top: 15px">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="">Фамилия</label>
                                <input v-model="passportData.LAST_NAME" type="text" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="">Имя</label>
                                <input v-model="passportData.NAME" type="text" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="">Отчество</label>
                                <input v-model="passportData.SECOND_NAME" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Серия</label>
                                    <input v-model="passportData.SER" type="text" class="form-control">
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Номер</label>
                                    <input v-model="passportData.NUMBER" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Код</label>
                                    <input v-model="passportData.KOD" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Дата выдачи</label>
                                    <input v-model="passportData.DATE" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Место рождения</label>
                                    <input v-model="passportData.BIRTH_PLACE" type="text" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">Кем выдан</label>
                            <textarea v-model="passportData.KEM_VIDAN" rows="5" class="form-control"></textarea>
                        </div>

                      <div class="form-group">
                        <label for="">
                          Пол
                        </label>
                        <select class="form-control" v-model="passportData.GENDER">
                          <option value="m">МУЖ</option>
                          <option value="w"> ЖЕН</option>
                        </select>
                      </div>
                    </div>


                </div>
                <div class="col-md-6">

                    <img :src="imgFile" width="100%">

                </div>
            </div>


            <div slot="footer" class="text-center">
                <el-button @click="setPassport">Вставить паспортные данные</el-button>
            </div>
        </el-dialog>

        <el-dialog
            title="Выберете документ"
            :visible.sync="dialogImages"
            width="70%"
        >
            <div class="d-flex " style="flex-wrap: wrap;">
                <div v-for="(item, key) in fileDataServer" :key="key"
                     style="width: 150px; height: 150px; padding:10px">
                    <a href="#" @click.prevent="selectKey(key)">
                        <img :src="item.thumb" style="object-fit: contain;    width: 100%;    height: 100%;">
                    </a>

                </div>
            </div>




        </el-dialog>

        <input v-show="false" @change="onLoadFiles" type="file" ref="fileUpload">
    </div>
</template>

<script>
import API, { BX_POST } from '@app/API'
import { Button, Dialog } from 'element-ui'
import { upperFirst, lowerCase } from 'lodash';
import moment from 'moment';

export default {
    name: "input-passport-api",
    components: {
        'el-button': Button,
        'el-dialog': Dialog,
    },
    props: {
        isEdit: {
            type: Boolean,
            default: true
        },
    },
    data() {
        return {
            file: undefined,
            isLoading: false,
            isError: false,
            fileDataServer: undefined,
            dialogVisible: false,
            passportData: {},
            imgFile : '',
            errorText: '',
            dialogImages: false,
        }
    },
    methods:{
        setPassport(){
            this.dialogVisible = false
            this.$emit('passport',this.passportData)
        },
        loadFile(file){
            this.isLoading = true
            this.errorText = ''
            BX_POST('vaganov:edp.show','uploadPdf', {file: file })
                .then(fileTmp => {
                    this.fileDataServer = fileTmp.filter(i => i.type === 'img')

                    console.log(this.fileDataServer);

                    if (this.fileDataServer.length === 1){
                        this.parserApi(this.fileDataServer[0])
                    }else if (this.fileDataServer.length > 0){
                        this.dialogImages = true
                        this.isLoading = false
                    }else {
                        this.isError = true
                        this.isLoading = false
                        this.errorText = 'Недопустимый формат файла'
                    }



                })
                .catch(() => {
                    this.isError = true
                    this.isLoading = false
                })
                .finally(() => {

                })
        },
        selectKey(key){
            this.dialogImages = false
            console.log(this.fileDataServer[key]);
            this.parserApi(this.fileDataServer[key])
        },
        parserApi(fileTmp){
            this.isLoading = true
            API.passportApi(fileTmp['tempName'])
                .then(passportData => {
                    this.imgFile = fileTmp['url']
                    console.log(passportData)
                    this.errorText = ''
                    this.isLoading = false

                    if (passportData instanceof Object ){

                        if (passportData.result){
                            this.passportData = this.parserData(passportData.data)
                            this.dialogVisible = true
                        }else {
                            this.errorText = passportData.message
                        }

                    }else {

                        this.errorText = 'ОШИБКА СЕРВИСА !!!'
                    }


                })
        },
        parserData(passportData){

            if (passportData.length > 0){
                let ar = passportData[0].data.results;

                let NAME = upperFirst(lowerCase(ar.filter(i => i.label === 'name')[0]?.text));
                let LAST_NAME = upperFirst(lowerCase(ar.filter(i => i.label === 'lastname')[0]?.text));
                let SECOND_NAME = upperFirst(lowerCase(ar.filter(i => i.label === 'middlename')[0]?.text));

                let NUMBER = '';
                let SER = '';

                let serial_1_filter = ar.filter(i => i.label === 'serial_1')[0];

                if (serial_1_filter) {
                    let serial_1 = serial_1_filter.text;

                    if (serial_1) {
                        SER = serial_1.split(' ')[0];
                        NUMBER = serial_1.split(' ')[1];
                    }
                }

                let serial_2_filter = ar.filter(i => i.label === 'serial_2')[0];

                if (serial_2_filter) {
                    let serial_2 = serial_2_filter.text;

                    if (serial_2) {
                        SER = serial_2.split(' ')[0];
                        NUMBER = serial_2.split(' ')[1];
                    }
                }

                let KOD = ar.filter(i => i.label === 'issued_number')[0]?.text;

                let issued_date = ar.filter(i => i.label === 'issued_date')[0]?.text;

                let year = issued_date.split('-')[0];
                let month = issued_date.split('-')[1];
                let day = issued_date.split('-')[2];

                let DATE = day + '.' + month + '.' + year;



                let KEM_VIDAN = ar.filter(i => i.label === 'issued')[0]?.text;

                let BIRTH_PLACE = ar.filter(i => i.label === 'birth_place')[0]?.text;

                let GENDER = ar.filter(i => i.label === 'sex')[0]?.text;

                if(GENDER){
                    if (GENDER.match(/^МУЖ/)) {
                        GENDER = 'm';
                    } else if (GENDER.match(/^ЖЕН/)) {
                        GENDER = 'w';
                    } else {
                        GENDER = '';
                    }
                }


                let bd = ar.filter(i => i.label === 'birth_date')[0]?.text;
                let BIRTH_DATE = moment(bd).format('DD.MM.YYYY');


                return {
                    NAME,
                    LAST_NAME,
                    SECOND_NAME,
                    NUMBER,
                    SER,
                    KOD,
                    DATE,
                    KEM_VIDAN,
                    BIRTH_PLACE,
                    GENDER,
                    BIRTH_DATE
                }
            }
        },
        onLoadFiles() {
            let filesObject = this.$refs.fileUpload.files;

            if (filesObject) {
                for (let file of filesObject) {
                    this.loadFile(file);
                }
            }

            this.$refs.fileUpload.type = '';
            this.$refs.fileUpload.type = 'file';
        },
        openInputFile(){
            this.$refs.fileUpload.click();
        },
    }
}
</script>

<style scoped>

</style>