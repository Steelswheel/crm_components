<template>
    <div>
        <div class="d-flex mb-2 align-items-center justify-content-center">
            <div class="mr-2 ml-2">
                <b>Всего сделок: {{ this.counter.all }}</b>
            </div>
            <div class="mr-2 ml-2">
                <b>Включено в план: {{ this.counter.included }}</b>
            </div>
            <div class="mr-2 ml-2">
                <b>Исключено из плана: {{ this.counter.excluded }}</b>
            </div>
            <div class="mr-2 ml-2">
                <b>Не обработано: {{ this.counter.unhandled }}</b>
            </div>
        </div>
        <el-table
            :data="values"
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
                width="60"
                type="index"
                :index="indexMethod"
            />
            <el-table-column
                label="Дата"
                prop="date"
                width="100"
            />
            <el-table-column
                label="Заемщик"
                width="250"
            >
                <template #default="{row}">
                    <a :href="row.borrower_link" target="_blank">
                        {{ row.borrower_fio }}
                    </a>
                    <div>
                        <small>
                            {{ row.stage }}
                        </small>
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                label="Партнер"
                width="250"
            >
                <template #default="{row}">
                    <a :href="row.partner_link" target="_blank">
                        {{ row.partner_fio }}
                    </a>
                </template>
            </el-table-column>
            <el-table-column
                label="Статус сделки"
            >
                <template #default="{row}">
                    <template v-if="isAdmin">
                        <el-button
                            class="add-to-plan"
                            :class="row.dealStatus ? 'include' : 'inactive'"
                            @click="setDealState(row.id, true)"
                            :loading="loading === `${row.id}+`"
                        >
                            <template v-if="row.dealStatus">
                                Включена в {{ getTextMonth(row.dealStatus_date) }}
                            </template>
                            <template v-else>
                                Включить
                            </template>
                        </el-button>
                        <el-button
                            class="add-to-plan"
                            :class="row.dealStatus ? 'inactive' : row.dealStatus_date ? 'exclude' : 'inactive'"
                            @click="setDealState(row.id, false)"
                            :loading="loading === `${row.id}-`"
                        >
                            <template v-if="row.dealStatus">
                                Не включать
                            </template>
                            <template v-if="!row.dealStatus && row.dealStatus_date">
                                Не включена в {{ getTextMonth(row.dealStatus_date) }}
                            </template>
                            <template v-if="!row.dealStatus && !row.dealStatus_date">
                                Не включать
                            </template>
                        </el-button>
                    </template>
                    <template v-else>
                        <div v-if="row.dealStatus" class="add-to-plan include">
                            Включена в {{ getTextMonth(row.dealStatus_date) }}
                        </div>
                        <div v-if="!row.dealStatus && row.dealStatus_date" class="add-to-plan exclude">
                            Не включена в {{ getTextMonth(row.dealStatus_date) }}
                        </div>
                        <div v-if="!row.dealStatus && !row.dealStatus_date" class="add-to-plan inactive">
                            Не обработана
                        </div>
                    </template>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script>

import { BX_POST } from '@app/API';
import { Table, TableColumn, Button } from 'element-ui';
import { getTextMonth, getDataCounter } from './salePlanHelper';
import moment from 'moment';

export default {
    name: 'modal-deals-in-process',
    components: {
        'el-table': Table,
        'el-button': Button,
        'el-table-column': TableColumn,
    },
    props: {
        values: Array,
        isAdmin: Boolean
    },
    data() {
        return {
            getTextMonth,
            loading: false,
            counter: getDataCounter(this.values),
        }
    },
    watch: {
        values: {
            deep: true,
            handler() {
                this.counter = getDataCounter(this.values);
            }
        }
    },
    methods: {
        setDealState(id, state ) {
            this.loading = id + (state ? '+' : '-');

            BX_POST(
                'vaganov:reports.all',
                'setDealPlanState',
                {id, state}
            ).then(() => {
                this.$emit('updateField', {
                    report: 'dealsInProcess',
                    dealId: id,
                    field: 'dealStatus',
                    value: state
                });

                this.$emit('updateField', {
                    report: 'dealsInProcess',
                    dealId: id,
                    field: 'dealStatus_date',
                    value: moment(new Date(), 'MM').locale('ru')
                });

            }).finally(() => {
                this.loading = false;
            });
        },
        indexMethod(index) {
            return index + 1;
        }
    }
}
</script>

<style scoped>

</style>