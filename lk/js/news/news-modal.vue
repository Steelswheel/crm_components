<template>
    <el-dialog
        :visible.sync="getVisibility"
        :modal-append-to-body="false"
        :close-on-click-modal="false"
        width="65%"
        @closed="closeModal"
    >
        <div class="lk-news-form d-flex flex-column align-items-center">
            <div class="w-100">
                <b>Заголовок</b>
                <el-input
                    class="mt-2"
                    v-model="localTitle"
                />
            </div>
            <div class="mt-2 w-100">
                <b>Текст</b>
                <inputTiptap v-model="localText"/>
            </div>
            <el-button
                v-if="localText.length > 0 && addVisibility"
                class="mt-4"
                type="primary"
                @click="$emit('addNewsItem')"
            >
                Сохранить
            </el-button>
            <el-button
                v-if="localText.length > 0 && changeVisibility"
                class="mt-4"
                type="primary"
                @click="$emit('changeItem')"
            >
                Изменить
            </el-button>
        </div>
    </el-dialog>
</template>

<script>

import { Dialog, Button, Input } from 'element-ui';
import inputTiptap from '@app/input/input-tiptap';

export default {
    name: 'news-modal',
    components: {
        'el-dialog': Dialog,
        'el-button': Button,
        'el-input': Input,
        inputTiptap,
    },
    props: {
        visibility: Boolean,
        addVisibility: Boolean,
        changeVisibility: Boolean,
        text: String,
        title: String
    },
    watch: {
        text(value) {
            this.localText = value;
        },
        localText(value) {
            this.$emit('setText', value);
        },
        title(value) {
            this.localTitle = value;
        },
        localTitle(value) {
            this.$emit('setTitle', value);
        }
    },
    computed: {
        getVisibility: {
            get: function() {
                return this.visibility;
            },
            set: function(value) {
                this.$emit('update:visibility', value);
            }
        },
    },
    data() {
        return {
            localText: '',
            localTitle: ''
        }
    },
    methods: {
        closeModal() {
            this.localText = '';
            this.localTitle = '';
        }
    }
}
</script>

<style scoped>

</style>