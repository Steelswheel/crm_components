<template>
    <div>


        <div v-if="value">
            <a :href="value.src"><i class="el-icon-document"></i> {{value.name}}</a>

        </div>

        <el-button
            v-if="isEdit"
            @click="openInputFile"
            :loading="isLoading"
            class="mr-1"
            type="success"
            plain
            size="mini"
        >Загрузить докумет</el-button>

        <input v-show="false" @change="onLoadFiles" type="file" ref="fileUpload">
    </div>
</template>

<script>
import { BX_POST } from '@app/API'
import {Button} from 'element-ui'
export default {
    name: "input-file",
    components: {
        'el-button': Button,
    },
    props: {
        value: Object,
        isEdit: Boolean,
    },
    data(){
        return {
            isLoading: false,
        }
    },
    methods: {
        loadFile(file){
            this.isLoading = true
            this.errorText = ''
            BX_POST('vaganov:edp.show','upload', {file: file })
                .then(fileTmp => {
                    let doc = {
                        name: fileTmp['file']['file']['name'],
                        tmp: fileTmp['tmp']
                    }
                    this.$emit('input', doc)

                })
                .catch(() => {
                    this.isError = true
                    this.isLoading = false
                })
                .finally(() => {
                    this.isLoading = false
                })
        },

        onLoadFiles() {
            // this.isLoading = true
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