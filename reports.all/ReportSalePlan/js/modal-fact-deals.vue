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
                <b>Исключено из плана:  {{ this.counter.excluded }}</b>
            </div>
            <div class="mr-2 ml-2">
                <b>Не обработано: {{ this.counter.unhandled }}</b>
            </div>
        </div>
        <div class="d-flex mb-2 align-items-center justify-content-center">
            <div>
                Процент выполнения <span class="mr-2">{{ percent }}%</span>
                Подтверждены <b>{{ percentFact }}%</b>
            </div>
        </div>
        <div class="d-flex mb-2 align-items-center justify-content-center">
            <div>
                Сдача в пенсионный фонд подтверждена:
                <el-tag type="success" class="mr-2">
                    <b> ДА: {{ values.filter(i => i.UF_IS_PASS_CONFIRMED).length }} </b>
                </el-tag>
                <el-tag type="warning" class="mr-3">
                    <b> НЕТ: {{ values.filter(i => !i.UF_IS_PASS_CONFIRMED).length }}</b>
                </el-tag>

                <el-button
                    v-if="rules.zp"
                    type="success"
                    size="mini"
                    @click="fixProcent"
                    :loading="isLoading"
                >
                    Зафиксировать результат
                </el-button>

            </div>
        </div>

        <el-table
            :data="values"
            height="400px"
            :summary-method="getSummaries"
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
                label="Статус сделки"
            >
                <template #default="{row}">
                    <div v-if="row.dealStatus">
                        <div class="add-to-plan include">
                            Включена в {{ getTextMonth(row.dealStatus_date) }}
                        </div>
                    </div>
                    <div v-else>
                        <div class="add-to-plan exclude" v-if="row.dealStatus_date">
                            Не включена в {{ getTextMonth(row.dealStatus_date) }}
                        </div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                label="% выполнения"
                width="140"
                prop="plan_percent"

            />
            <el-table-column
                label="Сдача в пенсионный фонд подтверждена"
                width="200"
                prop="UF_IS_PASS_CONFIRMED"
            >
                <template #default="{row}">
                    <el-tag :type="row.UF_IS_PASS_CONFIRMED ? 'success' : 'warning'">
                        <b>{{ row.UF_IS_PASS_CONFIRMED ? 'Да' : 'Нет' }}</b>
                    </el-tag>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script>
import { findReports } from './salePlanHelper';
import { Table, TableColumn, Tag, Button } from 'element-ui';
import { BX_POST } from '@app/API';
import { getTextMonth, getDataCounter } from './salePlanHelper';

export default {
    name: 'modal-fact-deals',
    components: {
        'el-tag': Tag,
        'el-table': Table,
        'el-table-column': TableColumn,
        'el-button': Button,
    },
    props: {
        rules: Object,
        values: Array,
        userId: {},
        reports: Array,
    },
    data(){
        return {
            isLoading: false,
            findReports,
            getTextMonth,
            counter: getDataCounter(this.values),
        }
    },
    computed: {
        percent() {
            let factDeals = findReports(this.reports, 'factDeals','values', this.userId);
            let UF_PLAN = findReports(this.reports, 'UF_PLAN','one', this.userId);

            if (factDeals === 0 || UF_PLAN === 0) {
                return 0;
            }

            return  Math.ceil(factDeals * 100 / UF_PLAN);
        },
        percentFact() {
            let UF_PLAN = findReports(this.reports, 'UF_PLAN','one', this.userId);
            let report = this.reports.find(i => i.name === 'factDeals');
            let fact = report.values.filter(i => i.userId === this.userId && i.UF_IS_PASS_CONFIRMED).length;

            if (fact === 0 || fact === 0) {
                return 0;
            }

            return  Math.ceil(fact * 100 / UF_PLAN);
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
        indexMethod(index) {
            return index + 1;
        },
        getSummaries(param) {
            let confirmed = param.data.filter(i => i.UF_IS_PASS_CONFIRMED).length;
            let notConfirmed = param.data.filter(i => !i.UF_IS_PASS_CONFIRMED).length;

            return {5:` ${confirmed} / ${notConfirmed}`};
        },
        fixProcent() {
            this.isLoading = true;

            BX_POST('vaganov:reports.all', 'salePlanFixPercent', {
                deals: this.values.map(i => i.id).join(),
                percent: this.percentFact
            }).then(() => {
                this.values.forEach(item => {
                    this.$emit('updateField', {
                        report: 'factDeals',
                        dealId: item.id,
                        field: 'plan_percent',
                        value: this.percentFact
                    });

                    this.$emit('updateField', {
                        report: 'loanRepaymentDeals',
                        dealId: item.id,
                        field: 'plan_percent',
                        value: this.percentFact
                    });
                });
            }).finally(() => {
                this.isLoading = false;
            });
        }
    }
}
</script>

<style scoped>

</style>