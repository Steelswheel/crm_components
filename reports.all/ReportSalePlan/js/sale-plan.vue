<template>
    <div class="b-block p-3">

        <div class="d-flex align-items-center mb-2">
            <div class="sale-report-filter-title mr-2">
                Дата
            </div>
            <el-date-picker
                v-model="month"
                value-format="MM.yyyy"
                format="MM.yyyy"
                type="month"
                size="mini"
                class="mr-4"
            />


            <el-button
                        @click="getFile"
                       size="mini"
                       :loading="isLoadingFile" >
                ЗП Excel
            </el-button>

        </div>

        <div class="mt-4 mb-4">
            <h4>ПЛАН ПРОДАЖ
                <i @click="report" class="el-icon-refresh-right"></i>
            </h4>
        </div>

        <div class="">
            <saleDepart
                v-if="!isLoading"
                :departs="departs"
                :reports="reports"
            >
                <template #dealsInProcess="{value, userId}">
                    <a href="#" @click.prevent="openModal('dealsInProcess', userId)">{{value}}</a>
                </template>


                <template #UF_DEALS_IN_PROCESS="{value, userId}">
                    <template v-if="rules.admin">
                        <input type="text"
                               :value="value"
                               class="sale-report-input"
                               @change="e => setKPI(e.target.value,userId,'DEALS_IN_PROCESS')">
                    </template>
                    <template v-else>{{value}}</template>
                </template>
                <template #UF_ADDITIONAL_SALES="{value, userId}">
                    <template v-if="rules.admin">
                        <input type="text"
                               :value="value"
                               class="sale-report-input"
                               @change="e => setKPI(e.target.value,userId,'ADDITIONAL_SALES')">
                    </template>
                    <template v-else>{{value}}</template>
                </template>
                <template #UF_PLAN="{value, userId}">
                    <template v-if="rules.admin">
                        <input type="text"
                               :value="value"
                               class="sale-report-input"
                               @change="e => setKPI(e.target.value,userId,'PLAN')">
                    </template>
                    <template v-else>{{value}}</template>
                </template>


                <template #factDeals="{value, userId}">

                    <cellTwoNumbers
                        :isClick="true"
                        @click="openModal('factDeals', userId)"
                        :value="value"
                        :fact="reports.find(i => i.name === 'factDeals').values
                            .filter(i => i.userId === userId && i.UF_IS_PASS_CONFIRMED).length"/>



                </template>

                <template #percent="{userId}">

                    <template v-if="percentFact(userId) > 0">{{ percentFact(userId) }}</template>
                    <template v-else>{{ percent(userId) }}</template>

                </template>

                <template #loanRepaymentDeals="{value, userId,report}">
                    <cellTwoNumbers
                        :isClick="true"
                        @click="openModal('loanRepaymentDeals', userId)"
                        :value="report.values.filter(i => i.is_current && i.userId === userId).length"
                        :fact="report.values.filter(i => '01.'+month === i.bonus_paided && i.userId === userId).length"/>
                </template>



                <template #iTogo_percent="{usersIds}">
                    <b v-if="percentDepartFact(usersIds)" class="text-success">{{ percentDepartFact(usersIds) }}</b>
                    <template v-else>{{percentDepart(usersIds)}}</template>
                </template>
                <template #iTogo_factDeals="{report, usersIds}">
                    <cellTwoNumbers
                        :value="report.values.filter(i => usersIds.includes(i.userId)).length"
                        :fact="report.values.filter(i => usersIds.includes(i.userId) && i.UF_IS_PASS_CONFIRMED).length"/>
                </template>
                <template #iTogo_loanRepaymentDeals="{usersIds,report}">
                    <cellTwoNumbers
                        :value="report.values.filter(i => i.is_current && usersIds.includes(i.userId)).length"
                        :fact="report.values.filter(i => '01.'+month === i.bonus_paided && usersIds.includes(i.userId)).length"/>
                </template>



            </saleDepart>
            <div v-else>loading</div>
        </div>

        <modals
            ref="openModal"
            :departs="departs"
            :reports="reports"
            :month="month"
            :rules="rules"
            @updateField="updateField"
        />


    </div>
</template>

<script>
/*global BX*/
import cellTwoNumbers from './cell-two-numbers'
import modals from './modals'
// import factDeals from './modal-fact-deals'
import { DatePicker, Button } from 'element-ui';
import { BX_POST } from '@app/API';
import saleDepart from './sale-depart'
import { findReports } from './salePlanHelper'
import moment from "moment";
export default {
    name: 'sale-plan',
    components: {
        saleDepart,
        'el-date-picker': DatePicker,
        'el-button': Button,
        modals,
        cellTwoNumbers,
    },
    data() {
        return {
            rules: {},
            month: moment(new Date()).format('MM.YYYY'),
            departs: [],
            isLoading: true,
            reports:[],
            isLoadingFile: false,

        }
    },
    watch: {
        month() {
            this.report();
            this.addOrUpdateUrlParam('date', this.month)
        }
    },
    mounted() {
        const url = new URL(window.location);
        const date = url.searchParams.get('date')
        if(moment(date,'MM.YYYY',true).isValid()){
            this.month = date
        }


        this.moutEvent()

        this.report()

    },
    methods: {

        moutEvent(){
            console.log('set event');
            BX.addCustomEvent('onPullEvent-sales_plan', BX.delegate((command, params) => {
                console.log('event',command, params);
                if (command === 'updateDeal') {
                    let data = this.reports.find(i => i.name === 'factDeals').values.find(i => i.id === params.ID)
                    if(['UF_IS_PASS_CONFIRMED'].includes(params.field)){
                        data[params.field] = params.value
                    }

                }
            }));

        },
        updateField({report, dealId, field, value}){
            let data = this.reports.find(i => i.name === report).values.find(i => i.id === dealId)
            if(data){
                data[field] = value
            }

        },
        openModal(name,userId){
            this.$refs.openModal.openModal(name,userId)
        },

        percent(userId){
            let UF_PLAN = findReports(this.reports,'UF_PLAN','one',userId)
            let factDeals = findReports(this.reports,'factDeals','values',userId)

            if(factDeals === 0 || UF_PLAN === 0){
                return 0
            }
            return  Math.ceil(factDeals * 100 / UF_PLAN)
        },
        percentFact(userId){
            let UF_PLAN = findReports(this.reports, 'UF_PLAN','one', userId)
            let report = this.reports.find(i => i.name === 'factDeals')
            let fact = report.values.filter(i => i.userId === userId && i.UF_IS_PASS_CONFIRMED).length

            if(fact === 0 || fact === 0){
                return 0
            }

            return  Math.ceil(fact * 100 / UF_PLAN)
        },
        percentDepart(userIds){
            let plan = 0
            let fact = 0

            userIds.forEach(userId => {
                plan += parseInt(findReports(this.reports,'UF_PLAN','one',userId))
                fact += parseInt(findReports(this.reports,'factDeals','values',userId))
            })
            if(plan === 0 || fact === 0){
                return 0
            }

            return  Math.ceil(fact * 100 / plan)
        },
        percentDepartFact(userIds){
            let plan = 0
            let fact = 0

            userIds.forEach(userId => {
                plan += parseInt(findReports(this.reports,'UF_PLAN','one',userId))
                let report = this.reports.find(i => i.name === 'factDeals')
                fact += parseInt(report.values.filter(i => i.userId === userId && i.UF_IS_PASS_CONFIRMED).length)
            })
            if(plan === 0 || fact === 0){
                return 0
            }

            return  Math.ceil(fact * 100 / plan)
        },




        addOrUpdateUrlParam(name, value){
            const url = new URL(window.location);
            url.searchParams.set(name, value);
            history.pushState({}, null,url.href)
        },
        report(){
            this.isLoading = true
            BX_POST('vaganov:reports.all', 'salePlanReport',{
                    month: this.month

            }).then(report => {
                this.rules = report.rules
                this.departs = report.departs
                this.reports = report.reports
            }).finally(() => {
                this.isLoading = false
            })
        },
        getFile(){

            let users = []
            let userGroup = this.departs.map(i => i.usersIds)
            users = users.concat(...userGroup)


            BX_POST('vaganov:reports.all', 'salePlanReportExcel',{
                month: this.month,
                users: users.join()
            }).then(link => {
                location.href = link;
            }).finally(() => {

            })
        },
        setKPI(value,userId,inputType) {
            if (this.month) {
                BX_POST('vaganov:reports.all', 'salePlanSetPlan', {
                    month: this.month,
                    value,
                    userId,
                    inputType,
                });
            }
            return false;
        }
    },


}
</script>
<style scoped>
>>> .el-table td.el-table__cell div  {
    word-break: break-word!important;
}
>>> .add-to-plan {
    width: 100%;
    border: none;
    padding: 5px 0;
    border-radius: 2px;
    transition: background-color 0.4s;
    text-transform: uppercase;
    color: #FFF;
    font: 600 12px/100% "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
    margin: 3px 0!important;
    text-align: center;
    line-height: 120%;
}
>>> div.add-to-plan {
    display: inline-block;
    width: 80%;
}
>>> .include {
    background-color: #5ce56a;
}
>>> button.include:hover {
    background-color: #5ced6bb4;
}
>>> .exclude {
    background-color: #fc8383;
}
>>> button.exclude:hover {
    background-color: #fc8383b4;
}
>>> .not-include {
    background-color: #fa9b57;
}
>>> .inactive {
    background-color: #d0d0d0!important;
}

>>> #sale-plan {
    min-height: 200px;
    min-width: 300px;
}

</style>

