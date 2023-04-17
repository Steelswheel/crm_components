<template>
    <div>
        <el-dialog
            v-if="name"
            :visible.sync="isShow"
            :modal-append-to-body="false"
            :title="paramsModal[name].title"
            :width="paramsModal[name].width"
            center
        >
            <dealsInProcess
                v-if="name === 'dealsInProcess'"
                :isAdmin="rules.admin"
                @updateField="updateField"
                :values="reports.find(i => i.name === name).values.filter(i => i.userId === userId)"
                :counter="getDataCounter(reports.find(i => i.name === name).values.filter(i => i.userId === userId))"
            />
            <factDeals
                v-if="name === 'factDeals'"
                :userId = "userId"
                :rules="rules"
                @updateField="updateField"
                :reports="reports"
                :values="reports.find(i => i.name === name).values.filter(i => i.userId === userId)"
                :counter="getDataCounter(reports.find(i => i.name === name).values.filter(i => i.userId === userId))"
            />
            <loanRepaymentDeals
                v-if="name === 'loanRepaymentDeals'"
                :rules="rules"
                :month="month"
                @updateField="updateField"
                :values="reports.find(i => i.name === name).values.filter(i => i.userId === userId)"
            />
        </el-dialog>
    </div>
</template>

<script>
import factDeals from './modal-fact-deals';
import dealsInProcess from './modal-deals-in-process';
import loanRepaymentDeals from './modal-loan-repayment-deals';
import { getDataCounter } from './salePlanHelper';
import { Dialog } from 'element-ui';

export default {
    name: 'modal',
    components: {
        'el-dialog': Dialog,
        factDeals,
        dealsInProcess,
        loanRepaymentDeals
    },
    props: {
        reports: Array,
        month: String,
        rules: Object
    },
    data() {
        return {
            getDataCounter,
            title: '',
            name: '',
            userId: null,
            isShow: false,
            width: '800px',
            paramsModal: {
                factDeals: {
                    title: 'СДАЧА В ПФР / СОЦ ФАКТ',
                    width: '1100px'
                },
                dealsInProcess: {
                    title: 'СДЕЛКИ В ПРОЦЕССЕ',
                    width: '1100px'
                },
                loanRepaymentDeals: {
                    title: 'ПОГАШЕНИЕ ЗАЙМА',
                    width: '1100px'
                }
            }
        }
    },
    methods: {
        openModal(name, userId) {
            this.isShow = true;
            this.userId = userId;
            this.name = name;
        },
        updateField({report, dealId, field, value}) {
            this.$emit('updateField', {report, dealId, field, value});
        }
    }
}
</script>

<style scoped>

</style>