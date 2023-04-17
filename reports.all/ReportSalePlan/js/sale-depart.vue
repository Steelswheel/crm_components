<template>
    <div>

        <div class="sale-report-table-wrapper mt-2">
            <div class="sale-plan">
                <table class="sale-report-table" cellspacing="0">

                    <thead ref="headerTable" style="top: 0px; position: sticky"><tr><th rowspan="2" class="yellow">ОТДЕЛ</th><th rowspan="2" class="yellow">МЕНЕДЖЕР</th><th rowspan="2" class="green">СДЕЛКИ В ПРОЦЕССЕ (проверка СБ - подача заявления в пфр / соц)</th><th colspan="3" class="green">ПЛАН</th><th colspan="2" class="green">СДАЧА В ПФР / СОЦ</th><th rowspan="2" class="green">ПОГАШЕНИЕ ЗАЙМА</th></tr><tr><th class="green">СДЕЛКИ В ПРОЦЕССЕ</th><th class="green">ПРОДАЖИ</th><th class="green">ПЛАН</th><th class="green">ФАКТ</th><th class="green">ПРОЦЕНТЫ</th></tr></thead>

                    <tbody>

                    <template v-for="(depart, keyI) in departs">
                        <tr v-for="(user,keyJ) in depart.users" :key="`${keyI}${keyJ}`" class="oper-sales-row">
                            <td :rowspan="depart.users.length" v-if="keyJ === 0" class="sale-report-table-city"> {{ depart.name }}</td>
                            <td class="sale-report-table-manager"> {{ user.NAME }}</td>
                            <td v-for="(report,key) in reports" :key="key">


                                <slot :name='report.name'
                                      :userId="user.ID"
                                      :value="findReport(report, report.name,report.one ? 'one' : 'values',user.ID)"
                                      :report="report"  >


                                    {{ findReport(report, report.name,report.one ? 'one' : 'values',user.ID) }}



                                </slot>


                            </td>
                        </tr>
                        <tr :key="`${keyI}`" class="oper-sales-total-row light-blue">
                            <td colspan="2" class="light-blue"> ИТОГО:</td>
                            <td v-for="(report,key) in reports" :key="key">

                                <slot :name='`iTogo_${report.name}`' :report="report" :usersIds="depart.usersIds">

                                    <template v-if="report.one" >

                                        {{ report.one
                                        .filter(i => depart.usersIds.includes(i.userId))
                                        .map(i => i.value).reduce((a,b) => (a+parseInt(b)),0) }}

                                    </template>
                                    <template v-else>
                                        {{ report.values.filter(i => depart.usersIds.includes(i.userId)).length }}
                                    </template>

                                </slot>



                            </td>
                        </tr>
                    </template>
                    <tr class="oper-sales-total-company-row">
                        <td colspan="2" class="orange"> ИТОГО (КОМПАНИЯ):</td>
                        <td v-for="(report,key) in reports" :key="key" class="orange">

                            <slot :name='`iTogo_${report.name}`' :report="report" :usersIds="allUsers">

                                <template v-if="report.one">
                                    {{ report.one
                                    .filter(i => allUsers.includes(i.userId))
                                    .map(i => i.value)
                                    .reduce((a,b) => (a+parseInt(b)),0)}}
                                </template>
                                <template v-else>
                                    {{ report.values.filter(i => allUsers.includes(i.userId)).length }}
                                </template>

                            </slot>

                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

        </div>



    </div>
</template>

<script>

import {findReport} from './salePlanHelper'
import { fixPositionSticky } from '@app/helper';
export default {
    name: "sale-depart",
    components:{

    },
    props: {
        departs: {
            type: Array,
            default: () => ([])
        },
        reports: {
            type: Array,
            default: () => ([])
        }
    },
    data(){
        return {
            findReport,
        }
    },
    watch:{
        reports: {
            deep: true,
            handler() {
                this.$nextTick(() => {
                    fixPositionSticky(this.$refs.headerTable);
                })
            }
        }
    },
    mounted(){
        this.$nextTick(() => {
            fixPositionSticky(this.$refs.headerTable);
        })
    },
    computed:{
        allUsers(){
            let users = []
            let userGroup = this.departs.map(i => i.usersIds)
            let newUsers = users.concat(...userGroup)
            return newUsers
        }
    },
    methods:{
        openModal(name,UserId){
            this.$refs.openModal.openModal(name,UserId)
        }

    }
}
</script>

<style scoped>

</style>