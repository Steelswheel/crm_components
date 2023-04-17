<template>
    <div class="b-block b-block__content">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <el-button
                v-if="!isEdit"
                @click="createPDF('.presentation', 'landscape')"
            >
                PDF
            </el-button>

            <div>
                <el-button
                    v-if="!isEdit"
                    @click="isEdit = true"
                >
                    Редактировать
                </el-button>
                <el-button
                    v-if="isEdit"
                    @click="cancel"
                >
                    Отмена
                </el-button>
            </div>
        </div>
        <div class="presentation">
            <div class="slide break">
                <img src="../img/zaym/slide-1.png" alt="slide-1">
            </div>
            <div class="slide break">
                <img src="../img/zaym/slide-2.png" alt="slide-2">
            </div>
            <div class="slide break">
                <img src="../img/zaym/slide-3.png" alt="slide-3">
            </div>
            <div class="slide break">
                <img src="../img/zaym/slide-4.png" alt="slide-4">
            </div>
            <div class="custom-slide d-flex align-items-center break">
                <div style="width: 63%;">
                    <img src="../img/zaym/slide-5.png" alt="slide-5">
                </div>
                <div class="d-flex align-items-center justify-content-center" style="width: 37%;" :key="componentKey">
                    <div>
                        <div class="info mb-4">
                            <div class="info-label">
                                Ваш персональный менеджер:
                            </div>
                            <div class="info-text">
                                <template v-if="isEdit">
                                    <el-input v-model="info.name"></el-input>
                                </template>
                                <template v-else>
                                    {{ info.name }}
                                </template>
                            </div>
                        </div>
                        <div class="info mb-4">
                            <div class="info-label mb-2">
                                Телефон:
                            </div>
                            <div class="info-text">
                                <template v-if="isEdit">
                                    <el-input v-model="info.phone"></el-input>
                                </template>
                                <template v-else>
                                    {{ info.phone }}
                                </template>
                            </div>
                        </div>
                        <div class="info mb-4">
                            <div class="info-label">
                                Почта:
                            </div>
                            <div class="info-text">
                                <template v-if="isEdit">
                                    <el-input v-model="info.email"></el-input>
                                </template>
                                <template v-else>
                                    {{ info.email }}
                                </template>
                            </div>
                        </div>
                        <div class="info mb-4">
                            <div class="info-label">
                                Сайт:
                            </div>
                            <div class="info-text">
                                <template v-if="isEdit">
                                    <el-input v-model="info.site"></el-input>
                                </template>
                                <template v-else>
                                    {{ info.site }}
                                </template>
                            </div>
                        </div>
                        <el-button
                            v-if="isEdit"
                            class="mt-2"
                            @click="set"
                            :loading="isLoading"
                        >
                            Сохранить
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { Button, Input } from 'element-ui';
import html2pdf from 'html2pdf.js';
import {BX_POST} from "@app/API";

export default {
    name: 'zaym-presentation',
    components: {
        'el-button': Button,
       'el-input': Input
    },
    data() {
        return {
            isLoading: false,
            isEdit: false,
            componentKey: 0,
            info: {
                name: 'ИМЯ ФАМИЛИЯ',
                phone: '+7 000 000-00-00',
                email: 'example@kooperatiff.ru',
                site: 'example.ru'
            }
        }
    },
    mounted() {
        this.load();
    },
    methods: {
        set() {
            this.isLoading = true;

            BX_POST('vaganov:work.schedule', 'setUserData', {
                name: this.info.name,
                phone: this.info.phone,
                email: this.info.email,
                site: this.info.site
            })
            .then(r => {
                if (r.name) {
                    this.info.name = r.name;
                }

                if (r.email) {
                    this.info.email = r.email;
                }

                if (r.phone) {
                    this.info.phone = r.phone;
                }

                if (r.site) {
                    this.info.site = r.site;
                }
            })
            .finally(() => {
                this.isEdit = false;
                this.isLoading = false;
            });
        },
        load() {
            BX_POST('vaganov:work.schedule', 'getUserData')
            .then(r => {
                if (r.name) {
                    this.info.name = r.name;
                }

                if (r.email) {
                    this.info.email = r.email;
                }

                if (r.phone) {
                    this.info.phone = r.phone;
                }

                if (r.site) {
                    this.info.site = r.site;
                }
            });
        },
        cancel() {
            this.isEdit = false;
            this.componentKey++;
        },
        createPDF(selector, orientation) {
            const div = document.createElement('div');

            const elem = this.$el.querySelector(selector);

            const clone = elem.cloneNode(true);

            clone.children[4].querySelector('img').style.height = '791px';
            clone.children[4].querySelector('img').style.width = '750px';

            div.append(clone);

            const opt = {
                filename: 'ПРЕЗЕНТАЦИЯ (ЗАЙМЫ)',
                jsPDF: { unit: 'mm', format: 'a4', orientation: orientation },
                html2canvas:  { scale: 2 },
                pagebreak: {
                    after: '.break'
                }
            }

            html2pdf().set(opt).from(div).save();
        }
    }
}
</script>

<style>
    @font-face {
        font-family: 'Gilroy Semibold';
        src: local('Gilroy Semibold'), local('Gilroy-Semibold'),
        url('../font/Gilroy-Semibold.woff2') format('woff2'),
        url('../font/Gilroy-Semibold.woff') format('woff'),
        url('../font/Gilroy-Semibold.ttf') format('truetype');
        font-weight: 600;
        font-style: normal;
    }

    @font-face {
        font-family: 'Gilroy Bold';
        src: local('Gilroy Bold'), local('Gilroy-Bold'),
        url('../font/Gilroy-Bold.woff2') format('woff2'),
        url('../font/Gilroy-Bold.woff') format('woff'),
        url('../font/Gilroy-Bold.ttf') format('truetype');
        font-weight: 700;
        font-style: normal;
    }

    @font-face {
        font-family: 'Gilroy Extrabold';
        src: local('Gilroy Extrabold'), local('Gilroy-Extrabold'),
        url('../font/Gilroy-Extrabold.woff2') format('woff2'),
        url('../font/Gilroy-Extrabold.woff') format('woff'),
        url('../font/Gilroy-Extrabold.ttf') format('truetype');
        font-weight: 800;
        font-style: normal;
    }

    .work-schedule .b-block__content {
        padding: 40px;
        width: 1300px!important;
        margin: 0 auto;
    }

    .presentation .slide img, .presentation .custom-slide img {
        max-width: 100%;
    }

    .presentation .info-label {
        font-family: 'Gilroy Semibold', sans-serif;
        font-size: 18px;
        line-height: 100%;
    }

    .presentation .info-text:nth-child(1), .presentation .info-text:nth-child(2) {
        font-family: 'Gilroy Extrabold', sans-serif;
        font-size: 28px;
        line-height: 100%;
    }

    .presentation .info-text:nth-child(3), .presentation .info-text:nth-child(4) {
        font-family: 'Gilroy Bold', sans-serif;
        font-size: 24px;
        line-height: 100%;
    }

    .presentation .info-text {
        color: #2196F3;
        margin-top: 10px;
    }
</style>