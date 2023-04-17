<template>
    <div class="sale-report-wrapper">
        <div v-if="table.title" class="table-title d-flex align-items-center justify-content-between">
            <div>
                <h4>
                    {{ table.title }}
                    <b-icon @click="update" icon="arrow-counterclockwise"/>
                </h4>
            </div>
        </div>
        <div class="sale-report-table-wrapper mt-2">
            <table v-if="table.table" class="sale-report-table" cellspacing="0">
                <thead ref="headerTable" v-if="table.table.head">
                    <tr v-for="(row, i) in table.table.head" :key="i">
                        <th
                            v-for="(col ,j) in row"
                            v-if="col['value'] !== null"
                            :key="`h${i}${j}`"
                            :rowspan="col['rowspan']"
                            :colspan="col['colspan']"
                            :class="col['class']"
                            v-html="col['value']"
                        >
                        </th>
                    </tr>
                </thead>
                <tbody v-if="table.table.body">
                    <tr v-for="(row, i) in table.table.body" :key="i" :class="row['class']">
                        <td
                            v-for="(col, j) in row['value']"
                            v-if="col['value'] !== null"
                            :key="`b${i}${j}`"
                            :rowspan="col['rowspan']"
                            :colspan="col['colspan']"
                            :class="col['class'] ? (col['value'] !== 0 && col['class'].indexOf('openModal') !== -1 ? col['class'] + ' sale-report-open-modal' : col['class']) : ''"
                            @click="isModal(col['class'], {'row': i, 'col': j})"
                        >
                            <div v-if="!col['input']">
                                {{ col['value'] }}
                            </div>
                            <div v-if="col['input']">
                                <input
                                    type="text"
                                    v-if="isAdmin"
                                    @change="setKPI"
                                    :data-input="col['input']"
                                    class="sale-report-input"
                                    :ref="`input-${i}-${j}`"
                                    :value="col['value']"
                                    :data-manager-id="col['manager-id']"
                                    :data-type="col['type']"
                                />
                                <div v-if="!isAdmin">
                                    {{ col['value'] }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <el-dialog
            :visible.sync="edzVisibility"
            :modal-append-to-body="false"
            :title="edzModalData.title"
            center
        >
            <el-table
                :data="edzModalData.content"
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
                    label="№"
                    prop="count"
                    width="60"
                >
                    <template #default="{row}">
                        <b>{{ row.count }}</b>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Дата"
                    prop="date"
                    width="100"
                >
                </el-table-column>
                <el-table-column
                    label="Заемщик"
                >
                    <template #default="{row}">
                        <div>
                            <a :href="row.borrower_link" target="_blank">
                                {{ row.borrower_fio }}
                            </a>
                        </div>
                        <div>
                            <small>
                                {{ row.stage }}
                            </small>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Партнер"
                >
                    <template #default="{row}">
                        <a :href="row.partner_link" target="_blank">
                            {{ row.partner_fio }}
                        </a>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>

        <el-dialog
            :visible.sync="pfrVisibility"
            :modal-append-to-body="false"
            :title="pfrModalData.title"
            center
        >
            <el-table
                :data="pfrModalData.content"
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
                    label="№"
                    prop="count"
                    width="60"
                >
                    <template #default="{row}">
                        <b>{{ row.count }}</b>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Дата"
                    prop="date"
                    width="100"
                >
                </el-table-column>
                <el-table-column
                    label="Заемщик"
                >
                    <template #default="{row}">
                        <div>
                            <a :href="row.borrower_link" target="_blank">
                                {{ row.borrower_fio }}
                            </a>
                        </div>
                        <div>
                            <small>
                                {{ row.stage }}
                            </small>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Партнер"
                >
                    <template #default="{row}">
                        <a :href="row.partner_link" target="_blank">
                            {{ row.partner_fio }}
                        </a>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Подача в ПФР"
                    prop="prf_date"
                >
                </el-table-column>
                <el-table-column
                    label="Подача в РСК"
                    prop="rsk_date"
                >
                </el-table-column>
            </el-table>
        </el-dialog>

        <el-dialog
            :visible.sync="edpVisibility"
            :modal-append-to-body="false"
            :title="edpModalData.title"
            center
        >
            <el-table
                :data="edpModalData.content"
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
                    label="№"
                    prop="count"
                    width="60"
                >
                    <template #default="{row}">
                        <b>{{ row.count }}</b>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Дата"
                    prop="date"
                    width="100"
                >
                </el-table-column>
                <el-table-column
                    label="Партнер"
                >
                    <template #default="{row}">
                        <div>
                            <a :href="row.link" target="_blank">
                                {{ row.fio }}
                            </a>
                        </div>
                        <div>
                            <small>
                                {{ row.stage }}
                            </small>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>

        <el-dialog
            :visible.sync="mailsVisibility"
            :modal-append-to-body="false"
            :title="mailsModalData.title"
            center
        >
            <el-table
                :data="mailsModalData.content"
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
                    label="№"
                    prop="count"
                    width="60"
                >
                    <template #default="{row}">
                        <b>{{ row.count }}</b>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Дата"
                    prop="date"
                    width="100"
                >
                </el-table-column>
                <el-table-column
                    label="Имя"
                >
                    <template #default="{row}">
                        <a :href="row.link" target="_blank">
                            {{ row.name }}
                        </a>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Статус"
                    prop="status"
                >
                </el-table-column>
            </el-table>
        </el-dialog>

        <el-dialog
            :visible.sync="callsVisibility"
            :modal-append-to-body="false"
            :title="callsModalData.title"
            center
        >
            <el-table
                :data="callsModalData.content"
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
                    label="№"
                    prop="count"
                    width="60"
                >
                    <template #default="{row}">
                        <b>{{ row.count }}</b>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Дата"
                    prop="date"
                    width="100"
                >
                </el-table-column>
                <el-table-column
                    label="Тип звонка"
                    width="150"
                >
                    <template #default="{row}">
                        <div class="d-flex align-items-center">
                            <i :class="row.icon"></i>
                            <span>
                                {{ row.type }}
                            </span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Имя"
                >
                    <template #default="{row}">
                        <a :href="row.link" target="_blank">
                            {{ row.name }}
                        </a>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Длительность"
                    prop="duration"
                >
                </el-table-column>
            </el-table>
        </el-dialog>
    </div>
</template>

<script>
import { Dialog, Table, TableColumn } from 'element-ui';
import { fixPositionSticky } from '@app/helper';
import moment from 'moment';

export default {
    name: 'sale-report-table',
    components: {
        'el-dialog': Dialog,
        'el-table': Table,
        'el-table-column': TableColumn
    },
    props: {
        table: {},
        isAdmin: Boolean
    },
    data() {
        return {
            input: {
                value: '',
                managerId: '',
                type: ''
            },
            edzVisibility: false,
            pfrVisibility: false,
            edpVisibility: false,
            callsVisibility: false,
            mailsVisibility: false,
            edzModalData: {},
            pfrModalData: {},
            edpModalData: {},
            callsModalData: {},
            mailsModalData: {}
        }
    },
    watch: {
        table: {
            deep: true,
            handler() {
                setTimeout(() => {
                    fixPositionSticky(this.$refs.headerTable);
                }, 0);
            }
        }
    },
    methods: {
        setKPI(event) {
            this.input.value = event.target.value;
            this.input.managerId = event.target.dataset.managerId;
            this.input.type = event.target.dataset.type;
            this.$emit('setKPI', event.target.dataset.input, this.input);
        },
        update() {
            this.$emit('update');
        },
        isModal(colClass, obj) {
            if (colClass && colClass.indexOf('openModal') !== -1) {
                const arr = colClass.split(' ');

                if (arr.includes('edz')) {
                    this.openModal(this.edzModalData, 'edzVisibility', obj);
                }

                if (arr.includes('pfr')) {
                    this.openModal(this.pfrModalData, 'pfrVisibility', obj);
                }

                if (arr.includes('edp')) {
                    this.openModal(this.edpModalData, 'edpVisibility', obj);
                }

                if (arr.includes('calls')) {
                    this.openModal(this.callsModalData, 'callsVisibility', obj);
                }

                if (arr.includes('mails')) {
                    this.openModal(this.mailsModalData, 'mailsVisibility', obj);
                }
            }
        },
        openModal(modalData, visibility, obj) {
            let data = this.table.table.body[obj.row].value[obj.col].data;

            if (data.length > 0) {
                modalData.content = data;
                modalData.title = this.table.table.body[obj.row].value[obj.col].title;
                this[visibility] = true;
            }
        },
        getTextMonth(date) {
            return moment(date, 'DD.MM.YYYY').locale('ru').format('MMMM');
        }
    }
}
</script>

<style>
    .sale-report-wrapper .el-table td.el-table__cell div {
        word-break: break-word !important;
    }

    #sale-report {
        min-width: 300px;
        min-height: 200px;
    }
</style>