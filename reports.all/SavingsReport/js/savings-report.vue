<template>
    <div :id="isAdmin ? '' : 'savings-table'" style="color: red" v-if="!isAdmin">
        Недостаточно прав для просмотра
    </div>
    <div id="savings-report" class="savings-report" v-else>
        <div class="mb-4">
            <div class="d-flex align-items-center mb-4">
                <div class="mr-2">
                    <small>
                        Дата:
                    </small>
                </div>
                <el-date-picker
                    v-model="date"
                    value-format="dd.MM.yyyy"
                    format="dd.MM.yyyy"
                    type="daterange"
                    size="mini"
                    unlink-panels
                    range-separator="До"
                    start-placeholder="Начало"
                    end-placeholder="Конец"
                    :picker-options="{
                    firstDayOfWeek: 1
                }"
                />
            </div>
            <h4>
                ОТЧЕТ ОТДЕЛА СБЕРЕЖЕНИЙ
                <b-icon @click="getData" icon="arrow-counterclockwise"/>
            </h4>
        </div>

        <el-table
            id="savings-table"
            :data="tableData"
            style="width: 1072px; border: 1px solid #000"
            :header-cell-style="{
                height: '57px',
                backgroundColor: '#e2e2e2',
                textAlign: 'center',
                fontSize: '12px',
                padding: 0,
                borderRight: '1px solid #000',
                borderBottom: '1px solid #000',
                color: '#000',
                wordBreak: 'break-word'
            }"
            :cell-style="{
                textAlign: 'center',
                borderRight: '1px solid #000',
                borderBottom: '1px solid #000',
                wordBreak: 'break-word'
            }"
        >
            <el-table-column
                label="Сотрудник"
                width="300"
            >
                <template #default="{row}">
                    {{ row.MANAGER.value }}
                </template>
            </el-table-column>
            <el-table-column
                label="Кол-во набранных"
                width="110"
            >
                <template #default="{row}">
                    <span
                        :class="row.ATTEMPTS.value > 0 ? 'linked' : ''"
                        @click="openModal(row.ATTEMPTS.modal)"
                    >
                        {{ row.ATTEMPTS.value }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column
                label="Кол-во дозвонов"
                width="110"
            >
                <template #default="{row}">
                    <span
                        :class="row.SUCCESS_CALLS.value > 0 ? 'linked' : ''"
                        @click="openModal(row.SUCCESS_CALLS.modal)"
                    >
                        {{ row.SUCCESS_CALLS.value }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column
                label="Время разговоров"
                width="110"
            >
                <template #default="{row}">
                    {{ row.CALLS_TIME.value }}
                </template>
            </el-table-column>
            <el-table-column
                label="Назначенные встречи"
                width="110"
            >
                <template #default="{row}">
                    <span
                        :class="row.APPOINTMENTS.value > 0 ? 'linked' : ''"
                        @click="openModal(row.APPOINTMENTS.modal)"
                    >
                        {{ row.APPOINTMENTS.value }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column
                label="Проведенные встречи"
                width="110"
            >
                <template #default="{row}">
                    <span
                        :class="row.MEETINGS.value > 0 ? 'linked' : ''"
                        @click="openModal(row.MEETINGS.modal)"
                    >
                        {{ row.MEETINGS.value }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column
                label="Кол-во контрактов"
                width="110"
            >
                <template #default="{row}">
                    <span
                        :class="row.CONTRACTS_COUNT.value > 0 ? 'linked' : ''"
                        @click="openModal(row.CONTRACTS_COUNT.modal)"
                    >
                        {{ row.CONTRACTS_COUNT.value }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column
                label="Сумма контрактов"
                width="110"
            >
                <template #default="{row}">
                    {{ row.CONTRACTS_SUM.value }}
                </template>
            </el-table-column>
        </el-table>

        <el-dialog
            :visible.sync="modalVisibility"
            :modal-append-to-body="false"
            :title="modalData.title"
            center
        >
            <el-table
                v-if="modalData.type === 'calls'"
                :data="modalData.content"
                height="400"
                fit
                border
                :cell-style="{
                    color: 'black',
                    textAlign: 'center',
                    wordBreak: 'break-word'
                }"
                :header-cell-style="{
                    color: 'black',
                    textAlign: 'center',
                    wordBreak: 'break-word'
                }"
            >
                <el-table-column
                    label="Дата"
                >
                    <template #default="{row}">
                        {{ row.DATE }}
                    </template>
                </el-table-column>
                <el-table-column
                    label="Длительность"
                >
                    <template #default="{row}">
                        {{ row.CALL_DURATION }}
                    </template>
                </el-table-column>
                <el-table-column
                    label="Имя"
                >
                    <template #default="{row}">
                        <a :href="row.LINK" target="_blank">
                            {{ row.NAME }}
                        </a>
                    </template>
                </el-table-column>
            </el-table>

            <el-table
                v-if="modalData.type === 'meetings'"
                :data="modalData.content"
                height="400"
                fit
                border
                :cell-style="{
                    color: 'black',
                    textAlign: 'center',
                    wordBreak: 'break-word'
                }"
                :header-cell-style="{
                    color: 'black',
                    textAlign: 'center',
                    wordBreak: 'break-word'
                }"
            >
                <el-table-column
                    label="Дата"
                >
                    <template #default="{row}">
                        {{ row.DATE }}
                    </template>
                </el-table-column>
                <el-table-column
                    label="Имя"
                >
                    <template #default="{row}">
                        <a :href="`/crm/lead/details/${row.ID}/`" target="_blank">
                            {{ row.TITLE }}
                        </a>
                    </template>
                </el-table-column>
            </el-table>

            <el-table
                v-if="modalData.type === 'contracts'"
                :data="modalData.content"
                height="400"
                fit
                border
                :cell-style="{
                    color: 'black',
                    textAlign: 'center',
                    wordBreak: 'break-word'
                }"
                :header-cell-style="{
                    color: 'black',
                    textAlign: 'center',
                    wordBreak: 'break-word'
                }"
            >
                <el-table-column
                    label="Дата создания"
                >
                    <template #default="{row}">
                        {{ row.DATE }}
                    </template>
                </el-table-column>
                <el-table-column
                    label="ФИО"
                >
                    <template #default="{row}">
                        <a :href="`/b/eds/?deal_id=${row.ID}`" target="_blank">
                            {{ row.FIO }}
                        </a>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Сумма"
                >
                    <template #default="{row}">
                        {{ row.SUM }}
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>
    </div>
</template>

<script>
import { Table, TableColumn, DatePicker, Dialog, Loading } from 'element-ui';
import { fixPositionSticky } from '@app/helper';
import { BX_POST } from '@app/API';
import moment from 'moment';

export default {
    name: 'savings-report',
    components: {
        'el-table': Table,
        'el-table-column': TableColumn,
        'el-date-picker': DatePicker,
        'el-dialog': Dialog
    },
    data() {
        return {
            tableData: [],
            date: this.getDate(),
            modalVisibility: false,
            modalData: [],
            isAdmin: false
        }
    },
    watch: {
        date() {
            this.getData();
        }
    },
    methods: {
        openModal(data) {
            this.modalVisibility = true;
            this.modalData = data;
        },
        getDate() {
            let begin = moment().startOf('isoWeek').format('DD.MM.YYYY');
            let end = moment().endOf('isoWeek').format('DD.MM.YYYY');

            return [begin, end];
        },
        getData() {
            if (this.date && this.date.length > 1) {
                let load = Loading.service({
                    target: '#savings-table',
                    fullscreen: false,
                    background: '000'
                });

                BX_POST('vaganov:reports.all', 'reportSavings', {
                    startDate: this.date[0],
                    endDate: this.date[1]
                })
                .then(r => {
                    this.tableData = r.table;
                    this.isAdmin = r.isAdmin
                })
                .catch((e) => {
                    console.log(e);
                })
                .finally(() => load.close());
            }
        }
    },
    mounted() {
        const header = document.querySelector('#savings-report .el-table .el-table__header-wrapper thead');

        if (header) {
            console.log('работает');
            fixPositionSticky(header);
        }

        this.getData();
    }
}
</script>

<style lang="scss">
    .savings-report  {
        .el-table .el-table__body td.el-table__cell:last-child {
            border-right: none!important;
        }

        .el-table .el-table__header th.el-table__cell {
            .cell  {
                word-break: break-word!important;
            }

            &:nth-last-child(2) {
                border-right: none!important;
            }

            .gutter {
                border: none;
            }
        }
    }

    .linked {
        color: #409EFF;
        text-decoration: underline;
        cursor: pointer;

        &:hover {
            color: #66b1ff;
        }
    }
</style>