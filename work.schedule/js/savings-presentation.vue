<template>
    <div class="b-block b-block__content">
        <el-button
            class="mb-4"
            v-if="!isEdit"
            @click="createPDF('.presentation', 'portrait')"
        >
          PDF
        </el-button>
        <div class="presentation">
            <div class="slide break">
                <img src="../img/savings/slide-1.jpg" alt="slide-1">
            </div>
            <div class="slide break">
                <img src="../img/savings/slide-2.jpg" alt="slide-2">
            </div>
            <div class="slide break">
                <img src="../img/savings/slide-3.jpg" alt="slide-3">
            </div>
            <div class="slide break">
                <img src="../img/savings/slide-4.jpg" alt="slide-4">
            </div>
            <div class="slide break">
              <img src="../img/savings/slide-5.jpg" alt="slide-5">
            </div>
        </div>
    </div>
</template>

<script>

import html2pdf from 'html2pdf.js';
import { Button } from 'element-ui';

export default {
    name: 'savings-presentation',
    components: {
        'el-button': Button
    },
    methods: {
        createPDF(selector, orientation) {
            const div = document.createElement('div');
            const elem = this.$el.querySelector(selector);
            const clone = elem.cloneNode(true);

            div.append(clone);

            const opt = {
                filename: 'ПРЕЗЕНТАЦИЯ (СБЕРЕЖЕНИЯ)',
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

    .presentation .slide img {
        max-width: 100%;
    }
</style>