<template>
    <div>


        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="">Имя</label>
                    <inputString v-model="inDoc.UF_NAME" :isEdit="doc.rule && doc.rule.includes('edit')"/>
                </div>

                <div class="form-group">
                    <label for="">Файл шаблона</label>
                    <inputFile v-model="inDoc.UF_FILE" :isEdit="doc.rule && doc.rule.includes('edit')"/>
                </div>

                <div class="form-group">
                    <label for="">Описание документа</label>
                    <inputTiptap v-model="inDoc.UF_DESCRIPTION" :isEdit="doc.rule && doc.rule.includes('edit')"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">


                    <div v-if="doc.rule && doc.rule.includes('edit_fields')">

                        <label for="">Имя поля в шаблоне</label>
                        <inputString v-model="inDoc.UF_NAME_EN" />

                    </div>

                    <label for="">Описание полей</label>
                    <inputTiptap v-model="inDoc.UF_FIELDS" :isEdit="doc.rule && doc.rule.includes('edit_fields')"/>

                </div>
            </div>
        </div>



        <div class="position-sticky" v-if="doc.rule && doc.rule.includes('edit')">
            <el-button @click="save" :disabled="isChange" type="success" :loading="isLoading">Сохранить</el-button>
        </div>

    </div>
</template>

<script>
import {Button} from 'element-ui'
import inputTiptap from '@app/input/input-tiptap'
import inputFile from './input-file'
import inputString from '@app/input/input-string'
import {clone} from 'lodash'
import { BX_POST } from '@app/API'
export default {
    name: "doc-form",
    components: {
        inputString,
        inputTiptap,
        inputFile,
        'el-button': Button
    },
    props: {
        doc: {
            type: Object,
        }
    },
    data(){
        return {
            inDoc: {},
            isLoading: false,
            inDocUndefined: {
                ID: "0",
                UF_NAME: "",
                UF_NAME_EN: "",
                UF_DESCRIPTION: "",
                UF_FIELDS: "",
                UF_IS_HTML: "0",
                UF_HTML: "",
                UF_FILE: ""
            },
        }
    },
    computed: {
        isChange(){
            return JSON.stringify(this.doc) === JSON.stringify(this.inDoc)
        }
    },
    mounted() {
        this.setData()
    },
    methods: {
        setData(){
            this.inDoc = this.doc
                ? clone(this.doc)
                : {...this.inDocUndefined}

        },
        save() {
            this.isLoading = true
            BX_POST('vaganov:document.generate', 'update', {
                doc: JSON.stringify(this.inDoc)
            })
                .then(r => {
                    this.$emit('value',r)
                })
                .finally(() => {
                    this.isLoading = false
            })
        }
    }

}
</script>

<style scoped>

</style>