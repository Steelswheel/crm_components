<template>
    <el-dialog
        :visible.sync="getVisibility"
        :modal-append-to-body="false"
        :close-on-click-modal="false"
        width="65%"
        @closed="closeModal"
    >
        <div class="lk-instructions-form d-flex flex-column align-items-center">
            <div class="w-100">
                <b>Заголовок</b>
                <el-input
                    v-if="getAccess"
                    class="mt-2"
                    v-model="localTitle"
                />
                <div class="mt-2" v-else>
                    {{title}}
                </div>
            </div>
            <div class="mt-2 w-100">
                <b>Текст</b>
                <inputTiptap
                    v-if="getAccess"
                    v-model="localText"
                />
                <div
                    v-else
                    class="mt-2"
                    v-html="table.find(item => +item.ID === showId) ? JSON.parse(table.find(item => +item.ID === showId).UF_TEXT) : ''"
                ></div>
            </div>
            <template v-if="getAccess">
                <el-button
                    v-if="localText.length > 0 && addVisibility"
                    class="mt-4"
                    type="primary"
                    @click="$emit('save')"
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
            </template>
        </div>
    </el-dialog>
</template>

<script>
import { Button, Dialog, Input } from 'element-ui';
import inputTiptap from '@app/input/input-tiptap'

export default {
    name: 'instructions-modal',
    props: {
        table: Array,
        visibility: Boolean,
        getAccess: Boolean,
        addVisibility: Boolean,
        changeVisibility: Boolean,
        showId: Number,
        title: String,
        text: String
    },
    components: {
        'el-dialog': Dialog,
        'el-input': Input,
        'el-button': Button,
        inputTiptap
    },
    watch: {
        title(value) {
            this.localTitle = value;
        },
        text(value) {
            this.localText = value;
        },
        localTitle(value) {
            this.$emit('setTitle', value);
        },
        localText(value) {
            this.$emit('setText', value);
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
            localTitle: '',
            localText: ''
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