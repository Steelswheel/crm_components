<template>
    <div class="work-schedule-text">
        <h1>РЕГЛАМЕНТ РАБОТЫ <i v-if="isLoading" class="el-icon-loading"></i></h1>

        <div v-if="!isLoading" class="b-block b-block__content">
            <div v-if="isAdmin">

                <el-buttons v-if="!isEdit" @click="edit" class="mb-2" >Редактировать</el-buttons>
                <el-buttons v-if="isEdit" @click="isEdit = false" class="mb-2" >Отмена</el-buttons>

                <div v-if="isEdit" class="a4">

                    <vue-editor v-model="textUpdate"></vue-editor>


                    <div class="work-schedule-btn-block">
                        <el-buttons @click="save" :loading="isLoadingSave" :type="text === textUpdate ? '' : 'success'">Сохранить</el-buttons>
                    </div>
                </div>
                <div v-else class="a4">
                    <div v-html="text"></div>
                </div>


            </div>
            <div v-else>
                <div v-html="text" class="a4"></div>
            </div>
        </div>


    </div>
</template>

<script>
import { VueEditor } from "vue2-editor";
import { Button } from "element-ui"
import { BX_POST } from '@app/API'
export default {
    name: "work-schedule",
    components: {
        "el-buttons": Button,
        VueEditor
    },
    data(){
        return {
            isLoading: true,
            isLoadingSave: false,
            isEdit: false,
            text: '',
            textUpdate: '',
            isAdmin: false
        }
    },
    mounted() {
        this.load()
    },
    methods: {
        edit(){
            this.isEdit = true
            this.textUpdate = this.text
        },
        load(){
            BX_POST('vaganov:work.schedule', 'getWorkSchedule')
                .then(r => {

                    this.isAdmin = r.isAdmin
                    this.text = r.UF_TEXT
                    this.isLoading = false
                })
        },
        save(){
            this.isLoadingSave = true
            BX_POST('vaganov:work.schedule', 'updateWorkSchedule', {
                text: this.textUpdate
            })
                .then(() => {
                    this.text = this.textUpdate
                    this.isLoadingSave = false
                    this.isEdit = false
                })
        }
    }
}
</script>

<style>

.work-schedule .b-block__content {
  padding: 40px;
  width: 1240px;
  margin: 0 auto;
}

.ql-align-center{
    text-align: center;
}
.ql-align-right{
    text-align: right;
}

.work-schedule-text p{
    margin-bottom: 0;
}

.work-schedule-btn-block{
    z-index: 9999999;
    position: fixed;
    bottom: 0;
    right: 0;
    left: 0;
    text-align: center;
    background: #ffffffd4;
    padding: 10px;
}

.work-schedule .b-block__content {
    padding: 40px;
    width: 1024px;
    margin: 0 auto;
}

</style>