<template>
    <div class="b-block b-block__content">
        <div class="d-flex justify-content-between">
            <div>
                <div v-if="isAdmin">
                    <el-button
                        v-if="!isEdit"
                        @click="edit"
                        class="mb-2"
                    >
                      Редактировать
                    </el-button>
                    <el-button
                        v-if="isEdit"
                        @click="isEdit = false"
                        class="mb-2"
                    >
                      Отмена
                    </el-button>
                </div>
            </div>
            <div>
                <el-button
                    v-if="!isEdit"
                    @click="createPDF"
                    class="mb-2"
                >
                  PDF
                </el-button>
            </div>
        </div>
        <div class="d-flex flex-column align-items-center" id="savings-tables">
            <h1 class="text-center black mb-1">
                СБЕРЕГАТЕЛЬНЫЕ ПРОГРАММЫ
            </h1>

            <div v-for="(program, programIndex) in savingsProgramList.programs" :class="`table-wrapper m-1 table-border-${program.background}`" :key="program.name">
                <table :class="`table table-border table-sm`">
                    <thead>
                        <tr>
                            <th :class="`thead-background-${program.background} thead-color-${program.color} table-border-${program.background}`" colspan="4">
                                {{ program.name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(parameter, parameterIndex) in program.parameters" :key="programIndex + program.name + parameter.name">
                            <template v-if="parameterIndex === 0">
                                <td :class="`table-border-${program.background}`" v-html="parameter.name"></td>
                                <td colspan="3" :class="`table-border-${program.background}`">
                                    <template v-if="isEdit">
                                        <input type="text" v-model="parameter.values" class="table-input">
                                    </template>
                                    <template v-else>
                                        {{ parameter.values }}
                                    </template>
                                </td>
                            </template>
                            <template v-else>
                                <td :class="`table-border-${program.background}`" v-html="parameter.name"></td>
                                <td v-for="(value, index) in parameter.values" :key="index + programIndex + program.name + parameter.name" :class="`table-border-${program.background}`">
                                    <template v-if="isEdit">
                                        <input type="text" v-model="parameter.values[index]" class="table-input">
                                    </template>
                                    <template v-else>
                                        {{ value }}
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                <template v-if="isEdit">
                    <input type="text" v-model="savingsProgramList.description" style="font-size: 14px; width: 350px;">
                </template>
                <template v-else>
                    <b>{{ savingsProgramList.description }}</b>
                </template>
            </div>
        </div>
        <el-button
            v-if="isAdmin"
            @click="save"
            :loading="isLoadingSave"
        >
            Сохранить
        </el-button>
    </div>
</template>

<script>

import { Button } from 'element-ui';
import { BX_POST } from '@app/API';
import html2pdf from 'html2pdf.js';

export default {
    name: 'savings-programs',
    components: {
        'el-button': Button
    },
    data() {
        return {
            isLoadingSave: false,
            savingsProgramList: [],
            isEdit: false,
            isAdmin: false,
        }
    },
    mounted() {
        this.load();
    },
    methods: {
        removeProgram(index, programIndex) {
            this.savingsProgramList[index].programs.splice(programIndex, 1);
        },
        addValues(index, programIndex) {
            this.savingsProgramList[index].programs[programIndex].values.push(this.values);
        },
        addProgram(index) {
            this.savingsProgramList[index].programs.push({...this.programObj});
        },
        save() {
            this.isLoadingSave = true;

            BX_POST('vaganov:work.schedule', 'updateSavingsProgram', {
                savingsProgram: JSON.stringify(this.savingsProgramList)
            })
            .then(() => {
                this.isLoadingSave = false
                this.isEdit = false
            });
        },
        load() {
            BX_POST('vaganov:work.schedule', 'savingsProgram')
            .then(r => {
                this.savingsProgramList = r.list;
                this.isAdmin = r.isAdmin;
            });
        },
        edit() {
            this.isEdit = true;
        },
        createPDF() {
            const div = document.createElement('div');

            const table = this.$el.querySelector('#savings-tables');

            table.querySelectorAll('.table-wrapper').forEach((wrapper, index) => {
                if (index === 2) {
                    wrapper.style = 'margin-top: 30px';
                }
            });

            const clone = table.cloneNode(true);

            table.querySelectorAll('.table-wrapper').forEach((wrapper, index) => {
                if (index === 2) {
                  wrapper.style = 'margin-top: 30px';
                }
            });

            clone.style = 'margin-top: 30px';

            clone.querySelector('h1').style = 'font-size: 24px';

            clone.querySelectorAll('.table').forEach(t => {
                t.style.width = '625px';

                t.querySelector('th').style = 'font-size: 15px';

                t.querySelectorAll('td').forEach(td => {
                    td.style = 'font-size: 12px';
                });
            });

            div.append(clone);

            const opt = {
                filename: 'СБЕРЕГАТЕЛЬНЫЕ ПРОГРАММЫ',
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                html2canvas:  { scale: 2 }
            };

            html2pdf().set(opt).from(div).save();
        }
    }
}
</script>

<style lang="scss">
.work-schedule {
    .b-block__content {
        padding: 40px;
        width: 1240px;
        margin: 0 auto;
    }
}

#savings-tables .table-wrapper {
    border-radius: 5px;

    &.table-border-purple {
        border: 1px solid #8757e8;
    }

    &.table-border-yellow {
        border: 1px solid #FBBF00;
    }
}

#savings-tables .table {
    width: 670px;
    margin: 0;

    td, th {
        vertical-align: middle;
        text-align: center!important;
    }

    td {
        font-weight: bold;


        &:first-child {
            width: 272px;
        }

        &:not(:first-child) {
            min-width: 130px;
        }

        &.table-border-purple {
            border: 1px solid #8757e8;
        }

        &.table-border-yellow {
            border-color: #FBBF00;
        }
    }

    tr {
        td {
            border-width: 1px;
            border-style: solid;

            &:first-child {
                border-left: none;
            }

            &:last-child {
                border-right: none;
            }
        }

        &:last-child {
            td {
                border-bottom: none;

                &:first-child {
                    border-radius: 0 0 0 5px;
                }

                &:last-child {
                    border-radius: 0 0 5px 0;
                }
            }
        }
    }

    th {
        border: none;
        border-radius: 5px 5px 0 0;
        font-weight: bold;
        font-size: 18px;

        &.thead-background-purple {
            background-color: #8757e8;
        }

        &.thead-background-yellow {
            background-color: #FBBF00;
        }

        &.thead-color-white {
            color: #fff;
        }

        &.thead-color-black {
            color: #000;
        }
    }
}
</style>