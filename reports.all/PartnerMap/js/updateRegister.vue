<template>
    <div>
        <el-button :loading="isLoading" @click="openInputFile" size="mini">Загрузить</el-button>

        <input v-show="false" @change="onLoadFiles" type="file" ref="fileUpload">

        <span class="mx-2" v-if="loading" >{{loading}} <i class="el-icon-loading"></i></span>
        <span class="mx-2 bg-success" v-if="success">{{success}} </span>
        <pre class="mx-2 bg-danger" v-if="error">{{error}}</pre>

    </div>
</template>

<script>
/*global BX*/
import { Button } from 'element-ui'
import { BX_POST } from '@app/API';
export default {
    name: "updateRegister",
    components: {
        'el-button': Button
    },
    data(){
        return {
            isLoading: false,
            loading: '',
            success: '',
            error: '',
        }
    },
    mounted() {
        this.event()
    },

    methods:{
        event(){
            BX.addCustomEvent("onPullEvent", BX.delegate((module_id,command,params) => {
                if(module_id === 'downloadregisterpay'){
                    console.log(module_id, command, params);

                    this.loading = params.loading
                    this.success = params.success
                    this.error = params.error
                }

            }, this));
        },
        updateRegister(file){
            BX_POST('vaganov:reports.all', 'PartnerMapDownload',{
                file
            })
                .then(r => {
                    this.isLoading = true
                    console.log(r);
                }).finally(() => {
                    this.isLoading = false
                })
        },

        loadFile(file){
            this.isLoading = true
            this.errorText = ''
            BX_POST('vaganov:edp.show','upload', {file: file })
                .then(fileTmp => {

                    this.updateRegister(fileTmp.tmp)



                })
                .catch(() => {
                    this.isLoading = false
                })
                .finally(() => {
                    this.isLoading = false
                })
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