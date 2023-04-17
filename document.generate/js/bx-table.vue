<template>
    <div>
        <el-dialog
            :title="title"
            :visible.sync="isOpenForm"
            :close-on-click-modal="false"
            append-to-body
            width="1400px"
        >
            <docForm v-if="isOpenForm" :doc="doc" @value="updateValue"/>
        </el-dialog>

        <div class="d-flex justify-content-between">
            <div> <h1>Документы</h1></div>
            <div>
<!--                <el-button @click="newDoc">Добавить</el-button>-->
            </div>
        </div>

        <grid
            ref="grid"
            controller="vaganov:document.generate"
        >
            <template #UF_NAME="{row}">
                <a href="#" @click.prevent="editDoc(row)">{{row.UF_NAME}}</a>
            </template>

        </grid>
    </div>
</template>

<script>

import docForm from './doc-form'
import grid from '@app/bx/grid';
import {Dialog} from 'element-ui'
export default {
    name: "bx-table",
    components: {
        docForm,
        grid,
        //'el-button': Button,
        'el-dialog': Dialog,
    },
    data(){
        return {
            doc: undefined,
            title: '',
            isOpenForm: false
        }
    },
    methods:{
      /*  newDoc(){
            this.title = 'Новый документ'
            this.doc = undefined
            this.isOpenForm = true
        },*/
        editDoc(doc){
            this.doc = doc
            this.title = doc.UF_NAME
            this.isOpenForm = true
        },
        updateValue(value){
            this.isOpenForm = false
            if(typeof value.row === 'string' && value.row === 'new'){
                this.$refs.grid.load()
            }
            this.$refs.grid.onUpdateRow(value.row.ID, value.row)
        }
    },

}
</script>

<style scoped>
    >>>thead [data-attribute="ID"]{
        width: 40px
    }
</style>