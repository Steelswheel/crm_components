<template>
    <div class="b-block b-block__content">
        <div class="d-flex justify-content-between mb-2">
            <div>
                <div v-if="isAdmin">
                    <el-button
                        v-if="!isEdit"
                        @click="edit"
                    >
                        Редактировать
                    </el-button>
                    <el-button
                        v-if="isEdit"
                        @click="cancel"
                    >
                        Отмена
                    </el-button>
                </div>
            </div>
            <div>
                <el-button
                    v-if="!isEdit"
                    @click="createPDF('.loan-programs-wrapper', 'landscape')"
                >
                    PDF
                </el-button>
            </div>
        </div>
        <div class="loan-programs-wrapper">
            <table class="loan-programs-table" :key="`table-${componentKey}`">
                <thead>
                    <tr>
                        <th :colspan="item.colspan" v-for="item in loanProgramList.header" :key="item.text">
                            <template v-if="isEdit">
                                <el-input type="textarea" v-model="item.text"></el-input>
                            </template>
                            <template v-else>
                                {{ item.text }}
                            </template>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in loanProgramList.body" :key="index">
                        <td
                            v-for="(i, ind) in item"
                            :key="'item' + index + ind"
                            :rowspan="i.rowspan"
                        >
                            <template v-if="isEdit">
                                <el-input type="textarea" v-model="i.text"></el-input>
                                <span
                                    class="add-dotted"
                                    v-if="ind === 0 && item.length === 1"
                                    @click="addItem(index)"
                                >
                                    Добавить
                                </span>
                                <span
                                    class="add-dotted"
                                    v-if="ind === 0 && item.length === 1"
                                    @click="deleteItem(index, ind)"
                                >
                                    Удалить
                                </span>
                                <span v-if="ind === 1">
                                    <span
                                        class="add-dotted"
                                        @click="deleteProgram(index)"
                                    >
                                        Удалить программу
                                    </span>
                                </span>
                            </template>
                            <template v-else>
                                {{ i.text }}
                            </template>
                        </td>
                    </tr>
                    <tr v-if="isEdit">
                        <td colspan="4">
                            <span class="add-dotted" @click="addProgram">
                                Добавить программу
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <el-button
            class="mt-2"
            v-if="isEdit"
            :loading="isLoadingSave"
            @click="save"
        >
            Сохранить
        </el-button>
    </div>
</template>

<script>

import { Button, Input } from 'element-ui';
import { BX_POST } from '@app/API';
import html2pdf from 'html2pdf.js';

export default {
    name: 'loan-programs',
    components: {
        'el-button': Button,
        'el-input': Input
    },
    data() {
        return {
            componentKey: 0,
            loanProgramList: {},
            isAdmin: false,
            isEdit: false,
            isLoadingSave: false
        }
    },
    mounted() {
        this.load();
    },
    methods: {
        cancel() {
          this.isEdit = false;
          this.componentKey++;
        },
        save() {
            this.isLoadingSave = true;

            BX_POST('vaganov:work.schedule', 'updateLoanProgram', {
                loanProgram: JSON.stringify(this.loanProgramList)
            })
            .then(() => {
                this.isLoadingSave = false;
                this.isEdit = false;
            });
        },
        addItem(index) {
            let arr = [
                {
                    text: '',
                    rowspan: ''
                }
            ];

            this.loanProgramList.body.splice(index + 1, 0, arr);

            let parentIndex = index;

            while (this.loanProgramList.body[parentIndex].length <= 1) {
                parentIndex--;
            }

            if (Object.prototype.hasOwnProperty.call(this.loanProgramList.body[parentIndex][0], 'rowspan')) {
                this.loanProgramList.body[parentIndex][0].rowspan++
            } else {
                this.loanProgramList.body[parentIndex][0].rowspan = 2;
            }

            if (Object.prototype.hasOwnProperty.call(this.loanProgramList.body[parentIndex][1], 'rowspan')) {
                this.loanProgramList.body[parentIndex][1].rowspan++
            } else {
                this.loanProgramList.body[parentIndex][1].rowspan = 2;
            }

            if (Object.prototype.hasOwnProperty.call(this.loanProgramList.body[parentIndex][2], 'rowspan')) {
                this.loanProgramList.body[parentIndex][2].rowspan++
            } else {
                this.loanProgramList.body[parentIndex][2].rowspan = 2;
            }
        },
        deleteItem(index, ind) {
            this.loanProgramList.body[index].splice(ind, 1);

            let parentIndex = index;

            while (this.loanProgramList.body[parentIndex].length <= 1) {
                parentIndex--;
            }

            this.loanProgramList.body[parentIndex][0].rowspan === 0 ? '' : --this.loanProgramList.body[parentIndex][0].rowspan;
            this.loanProgramList.body[parentIndex][1].rowspan === 0 ? '' : --this.loanProgramList.body[parentIndex][1].rowspan;
            this.loanProgramList.body[parentIndex][2].rowspan === 0 ? '' : --this.loanProgramList.body[parentIndex][2].rowspan;
        },
        deleteProgram(index) {
            if (this.loanProgramList.body[index][0].rowspan) {
                this.loanProgramList.body.splice(index, this.loanProgramList.body[index][0].rowspan);
            } else {
                this.loanProgramList.body.splice(index, 1);
            }
        },
        addProgram() {
            let arr = [
                {
                    text: '',
                    rowspan: ''
                },
                {
                    text: '',
                    rowspan: ''
                },
                {
                    text: '',
                    rowspan: ''
                },
                {
                    text: '',
                    rowspan: ''
                }
            ];

            this.loanProgramList.body.push(arr);
        },
        load() {
            BX_POST('vaganov:work.schedule', 'loanProgram')
            .then(r => {
                this.loanProgramList = r.list;
                this.isAdmin = r.isAdmin;
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
        },
        edit() {
            this.isEdit = true;
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

  .loan-programs-wrapper {
      border-radius: 11px;
      border: 1px solid #699;
  }

  .loan-programs-table td {
      padding: 5px 10px;
      font-size: 14px;
      border-right: 1px solid #699;
      border-bottom: 1px solid #699;
  }

  .loan-programs-table tr td:nth-child(2), .loan-programs-table tr td:nth-child(3), .loan-programs-table tr td:nth-child(4) {
      width: 32%;
  }

  .loan-programs-table tr td:nth-child(3) {
      text-align: center;
  }

  .loan-programs-table tr td:last-child {
      border-right: none;
  }

  .loan-programs-table tr:last-child td {
      border-bottom: none;
  }

  .loan-programs-table th {
      text-align: center;
      background-color: #699;
      color: #fff;
      font-size: 16px;
      padding: 5px 10px;
      border-right: 1px solid #fff;
  }

  .loan-programs-table th:last-child {
      border-right: none;
  }

  .loan-programs-table th:first-child {
      border-radius: 9px 0 0 0;
  }

  .loan-programs-table th:last-child {
      border-radius: 0 9px 0 0;
  }
</style>