<template>
    <div>

        <el-date-picker
            v-model="date"
            type="month"
            value-format="yyyy-MM-dd"
            format="dd.MM.yyyy"
            placeholder="Pick a month">
        </el-date-picker>

        <div>
            <exel-table
                v-if="table"
                :table="table"
                :isUpdate="true"
                @update="report"
            />
            <span v-else>loading</span>
        </div>


    </div>
</template>

<script>
import { DatePicker } from 'element-ui'
import { BX_POST } from '@app/API'
import exelTable from '@app/components/table-old'
import moment from "moment"
export default {
    name: "pay-on-applications",
    components: {
        'el-date-picker': DatePicker,
        exelTable
    },
    data(){
        return {
            table: false,
            date: (new moment()).format('DD.MM.YYYY'),
         }
    },
    mounted() {
        this.report()
    },
    watch:{
        date(){
            this.report()
        }
    },
    methods:{
        report(){
            this.table = false
            BX_POST('vaganov:reports.all', 'PayOnApplicationsTable',{date: this.date}).then(table => {
                this.table = table
            })
        },
    },
}
</script>