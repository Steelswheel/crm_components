<template>
    <div class="b-block b-block__content">
        <div class="d-flex justify-content-between">
            <div>
                <div v-if="isAdmin">
                    <el-button v-if="!isEdit" @click="edit" class="mb-2">
                      Редактировать
                    </el-button>
                    <el-button v-if="isEdit" @click="isEdit = false" class="mb-2">
                      Отмена
                    </el-button>
                </div>
            </div>
            <div>
                <el-button v-if="!isEdit" @click="createPDF('table', 'portrait')" class="mb-2" >PDF</el-button>
            </div>
        </div>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th colspan="3" style="width: 820px" class="bt-b bl-b text-center bg-2">
                      КРЕДИТНЫЕ ПРОГРАММЫ
                    </th>
                    <th style="width: 200px" class="bt-b br-b bg-2" >
                        <p class="small_text text-center">
                          Примерная сумма расходов, в зависимости от суммы займа,
                          срока, процентной ставки, поручителей, кредитной истории, долгах ФССП -
                          оплачивается заемщиком за счет собственных средств
                        </p>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-for="(programHead, programHeadKey) in loanProgramList">
                    <template v-if="isEdit">
                        <tr :key="`${programHeadKey}head`">
                            <td :rowspan="programHead.programs.length + 2" class="bl-b"><b>{{programHeadKey + 1}}</b></td>
                            <td colspan="3" class="br-b bg-1">
                                <input type="text" class="form-control form-control-sm" v-model="programHead.header">
                            </td>
                        </tr>

                        <tr v-for="(program, programKey) in  programHead.programs" :key="`${programKey}${programHeadKey}`">
                            <td>{{programKey + 1}}</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" v-model="program.title">
                                <textarea class="form-control form-control-sm mt-1" rows="4" v-model="program.description"></textarea>

                            </td>
                            <td class="br-b">
                                <input type="text" class="form-control form-control-sm mb-3" v-model="program.price">

                                <span class="add-dotted" @click="programRemove(programHeadKey,programKey)">Удалить</span>
                            </td>

                        </tr>
                        <tr :key="`${programHeadKey}add`">
                            <td colspan="3">
                                <span class="add-dotted" @click="addProgram(programHeadKey)">Добавить программу</span>
                            </td>
                        </tr>

                        <tr v-if="loanProgramList.length === programHeadKey + 1" :key="`${programHeadKey}add1`">
                            <td colspan="4">
                                <span class="add-dotted" @click="addHead">Добавить раздел</span>
                            </td>
                        </tr>

                    </template>
                    <template v-else>
                        <tr :key="`${programHeadKey}head`">
                            <td :rowspan="programHead.programs.length + 1" class="bl-b"><b>{{programHeadKey + 1}}</b></td>
                            <td colspan="3" class="br-b bg-1">
                                {{programHead.header}}
                            </td>
                        </tr>

                        <tr v-for="(program, programKey) in  programHead.programs" :key="`${programKey}${programHeadKey}`">
                            <td>{{programKey + 1}}</td>
                            <td><b class="mr-2">{{program.title}}</b>{{program.description}}</td>
                            <td class="br-b text-center">{{program.price}}</td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>


        <el-button v-if="isAdmin" @click="save" :loading="isLoadingSave" >Сохранить</el-button>

        <div class="mt-4 d-flex flex-column align-items-end">
          <el-button v-if="!isEdit" @click="createPDF('.loan-image', 'landscape')" class="mb-2" >
            PDF
          </el-button>
          <div class="mt-2 loan-image">
            <img src="../img/table.png" alt="table">
          </div>
        </div>

    </div>
</template>

<script>

import { Button } from 'element-ui';
import { BX_POST } from '@app/API';
import html2pdf from 'html2pdf.js';

export default {
    name: 'loan-programs-old',
    components: {
        'el-button': Button,
    },
    data(){
        return {
            loanProgramList: [],
            isLoading: true,
            isLoadingSave: false,
            isEdit: false,
            isAdmin: false,
            programObj: {
                "title":'',
                "description":'',
                "price":'',
            },
        }
    },
    mounted() {
        this.load();
    },
    methods: {
        load() {
            BX_POST('vaganov:work.schedule', 'loanProgram')
            .then(r => {
                this.loanProgramList = r.list;
                this.isAdmin = r.isAdmin;
            })
        },
        edit() {
            this.isEdit = true;
        },
        addProgram(programHeadKey) {
            this.loanProgramList[programHeadKey].programs.push({...this.programObj});
        },
        addHead() {
            this.loanProgramList.push({
                'header': '',
                'programs': []
            })
        },
        programRemove(programHeadKey,programKey) {
            this.loanProgramList[programHeadKey].programs.splice(programKey,1)
        },
        save() {
            this.isLoadingSave = true
            BX_POST('vaganov:work.schedule', 'updateLoanProgram', {
                loanProgram: JSON.stringify(this.loanProgramList)
            })
            .then(() => {
                this.isLoadingSave = false;
                this.isEdit = false;
            })
        },
        createPDF(selector, orientation) {
            const div = document.createElement('div');
            div.style.marginTop = '25px';
            div.style.marginRight = '25px';
            div.style.marginLeft = '25px';

            const table = this.$el.querySelector(selector);
            const clone = table.cloneNode(true);

            div.append(clone);

            const opt = {
                filename: 'КРЕДИТНЫЕ ПРОГРАММЫ',
                jsPDF: { unit: 'mm', format: 'a4', orientation: orientation },
                html2canvas:  { scale: 2 }
            }

            html2pdf().set(opt).from(div).save();
        }
    }
}
</script>

<style >
.work-schedule .b-block__content {
  padding: 40px;
  width: 1240px;
  margin: 0 auto;
}

.table-tariff__table__name{
    font-size: 11px;
    font-weight: bold;
}

.table-tariff__table td{
    text-align: center;
}

.small_text{
    font-size: 10px;
    margin-bottom: 0;
}

.bt-b{
    border-top: 3px solid #222222 !important;
}
.bb-b{
    border-bottom: 3px solid #222222 !important;
}
.bl-b{
    border-left: 3px solid #222222 !important;
}
.br-b{
    border-right: 3px solid #222222 !important;
}

.bg-1{
    background: #e0e0e0;
}
.bg-2{
    background: #bbbbbb;
}

.table-bordered th, .table-bordered td {
    border: 1px solid #85898c !important;
}

.table-bordered, .table-bordered td, .table-bordered th {
    border: 1px solid #85898c !important;
}

.work-schedule .b-block__content {
    padding: 40px;
    width: 1024px;
    margin: 0 auto;
}

.loan-image img {
  max-width: 100%;
}

</style>