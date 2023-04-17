<template>
    <div>
        <el-tabs v-model="activeName">
            <el-tab-pane label="Текущие сделки" name="current">
                <el-table
                    :data="values.filter(item => item.is_current)"
                    height="400px"
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
                        label="Заемщик"
                        width=""
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
                        label="Подача в ПФР СОЦ"
                        width="120"
                        prop="prf_date"
                    >
                        <template #default="{row}">
                            <div v-if="row.prf_date">
                                {{ row.prf_date }}
                            </div>
                            <div v-if="row.rsk_date">
                                {{ row.rsk_date }}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="Зачисление ПФР СОЦ"
                        width="110"
                    >
                        <template #default="{row}">
                            <div>
                                {{ row.payment_pfr_date }}
                            </div>
                            <div>
                                {{ row.payment_rsk_date }}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="% выполнения"
                        width="140"
                        prop="plan_percent"
                    ></el-table-column>
                    <el-table-column
                        label="Задолжность"
                        width="140"
                        prop="dep"
                    >
                        <template #default="{row}">
                            <div>
                                {{ row.dep > 1 ? "Задолжность" : '' }}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="Бонус выплачен"
                        width="190"
                    >
                        <template #default="{row}">
                            <div v-if="row.bonus_paided" class="add-to-plan include">
                                Да {{ getTextMonth(row.bonus_paided) }}
                            </div>
                            <div v-else class="">
                                <span class="add-to-plan exclude px-2 mr-2">Нет</span>
                                <el-button
                                    v-if="rules.zp"
                                    size="mini"
                                    @click="pay(row.id)"
                                    :loading="isLoading === row.id"
                                >
                                    Выплатить
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tab-pane>
            <el-tab-pane label="Неоплаченные сделки (прошлый период)" name="without-payment">
                <el-table
                    :data="values.filter(item => (item.without_bonus && !item.is_current))"
                    height="400px"
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
                        label="Заемщик"
                        width=""
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
                        label="Подача в ПФР СОЦ"
                        width="120"
                        prop="prf_date"
                    >
                        <template #default="{row}">
                            <div v-if="row.prf_date">
                                {{ row.prf_date }}
                            </div>
                            <div v-if="row.rsk_date">
                                {{ row.rsk_date }}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="Зачисление ПФР СОЦ"
                        width="110"
                    >
                        <template #default="{row}">
                            <div>
                                {{ row.payment_pfr_date }}
                            </div>
                            <div>
                                {{ row.payment_rsk_date }}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="% выполнения"
                        width="140"
                        prop="plan_percent"
                    ></el-table-column>
                    <el-table-column
                        label="Задолжность"
                        width="140"
                        prop="dep"
                    >
                        <template #default="{row}">
                            <div>
                                {{ row.dep > 1 ? "Задолжность" : '' }}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="Бонус выплачен"
                        width="190"
                    >
                        <template #default="{row}">
                            <div v-if="row.bonus_paided" class="add-to-plan include">
                                Да {{ getTextMonth(row.bonus_paided) }}
                            </div>
                            <div v-else class="">
                                <span class="add-to-plan exclude px-2 mr-2">Нет</span>
                                <el-button
                                    v-if="rules.zp"
                                    size="mini"
                                    @click="pay(row.id)"
                                    :loading="isLoading === row.id"
                                >
                                    Выплатить
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import { Table, TableColumn, Button, Tabs, TabPane } from 'element-ui';
import { getTextMonth } from './salePlanHelper';
import { BX_POST } from '@app/API';

export default {
    name: 'modal-loan-repayment-deals',
    components: {
        'el-table': Table,
        'el-table-column': TableColumn,
        'el-button': Button,
        'el-tabs': Tabs,
        'el-tab-pane': TabPane
    },
    props: {
        values: Array,
        rules: Object,
        month: String,
    },
    data() {
        return {
            activeName: 'current',
            getTextMonth,
            isLoading: false
        }
    },
    methods: {
        indexMethod(index) {
            return index + 1;
        },
        pay(dealId) {
            this.isLoading = dealId;

            BX_POST('vaganov:reports.all', 'salePlanPay', {
                dealId: dealId,
                month: this.month
            }).then(date => {
                this.$emit('updateField', {
                    report: 'loanRepaymentDeals',
                    dealId,
                    field: 'bonus_paided',
                    value: date
                })
            }).finally(() => {
                this.isLoading = false
            });
        }
    }
}
</script>

<style scoped>

</style>