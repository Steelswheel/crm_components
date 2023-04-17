<template>
    <div class="b-block b-block__content">
        <el-button
            @click="openInputFile"
            :loading="isLoading"
            class="mr-1"
            type="success"
            plain
            size="mini"
        >
          Выписка 1с
        </el-button>

        <input v-show="false" @change="onLoadFiles" type="file" ref="fileUpload">

        <pre>{{text}}</pre>

        <in-payment
            :docs="docs.filter(i => i.payment === 'OUT')"
            :stages="stages"
        />
        <in-payment
            :docs="docs.filter(i => i.payment === 'IN')"
            :stages="stages"
        />
    </div>
</template>

<script>
import { BX_POST } from '@app/API';
import { Button } from 'element-ui';
import InPayment from './in-payments';

export default {
    name: 'money-order',
    components: {
        'el-button': Button,
        InPayment
    },
    data() {
        return {
            isLoading: false,
            docs: [],
            isLoadingTranche: false,
            stages: {},
            text: ''
        }
    },
    methods: {
        onClickTab(e) {
            console.log(e);
        },
        loadFile(file) {
            this.isLoading = true;

            BX_POST('vaganov:edp.show', 'upload', {file: file})
            .then(fileTmp => {
                BX_POST('vaganov:money.orders', 'parser', {file: fileTmp.tmp})
                .then(parserRes => {
                    this.stages = parserRes.stages;
                    this.docs = parserRes.docs;
                    this.text = parserRes.text;
                }).finally(() => {
                    this.isLoading = false;
                });
            });
        },
        onLoadFiles() {
            let filesObject = this.$refs.fileUpload.files;

            if (filesObject && filesObject[0]) {
                this.loadFile(filesObject[0]);
            }

            this.$refs.fileUpload.type = '';
            this.$refs.fileUpload.type = 'file';
        },
        openInputFile() {
            this.$refs.fileUpload.click();
        }
    }
}
</script>