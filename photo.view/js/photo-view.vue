<template>
    <div class="bg-dark">
        <div class="photo-view__wrap-control">
            <el-button @click="zoom('+')" size="mini" >+</el-button>
            <el-button @click="zoom('-')" size="mini" >-</el-button>
        </div>
        <div style="height: 40px"></div>
        <div
            v-for="(item, key) in images.img"
            :key="key"
            class="text-center py-3"
        >
            <img
                :style="`transform: rotate(${rotate}deg)`"
                :src="item.src" alt=""
                :width="width"
            >
        </div>
    </div>
</template>

<script>
import { BX_POST } from '@app/API';
import { Button } from 'element-ui';

export default {
    name: 'photo-view',
    components: {
        'el-button': Button
    },
    props: {
        ids: {}
    },
    data(){
        return {
            images: { img: []},
            width: 800,
            rotate: 0
        }
    },
    mounted() {
        this.init();
    },
    methods: {
        onRotatePhoto() {
            this.rotate += 90;
        },
        zoom(z) {
            if(z === '+') this.width += 150;
            if(z === '-') this.width -= 150;
        },
        async init() {
            this.images = await BX_POST('vaganov:photo.view', 'img', {ids: this.ids });
        }
    }
}
</script>

<style scoped>
    .photo-view__wrap-control{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        height: 37px;
        background: #ffffff;
        text-align: center;
        padding: 5px;
    }

    .img-right {
        transform: rotate(180deg);
    }
</style>