<template>
    <div class="sale-report b-block b-block__content">
        <div class="d-flex align-items-сenter mb-2">
            <div class="sale-report-filter-title mr-2">
                Дата
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
            >
            </el-date-picker>
        </div>
        <div id="sale-report">
            <sale-report-table
                :table="table"
                @update="report"
                @setKPI="setKPI"
                :isAdmin="isAdmin"
            />
        </div>
    </div>
</template>

<script>
    import { DatePicker, Loading } from 'element-ui';
    import { BX_POST } from '@app/API';
    import saleReportTable from './sale-report-table';
    import moment from 'moment';

    export default {
        name: 'sale-report',
        components: {
            'el-date-picker': DatePicker,
            saleReportTable
        },
        data() {
            return {
                table: false,
                date: this.getDate(),
                input: {
                    value: '',
                    managerId: '',
                    type: ''
                },
                isAdmin: false
            }
        },
        methods: {
            getDate() {
                let begin = moment().startOf('isoWeek').format('DD.MM.YYYY');
                let end = moment().endOf('isoWeek').format('DD.MM.YYYY');

                return [begin, end];
            },
            setKPI(isInput, input) {
                if (isInput) {
                    if (input.value !== '') {
                        this.input = input;

                        BX_POST('vaganov:reports.all', 'saleReport',
                        {
                            startDate: this.date[0],
                            endDate: this.date[1],
                            inputValue: this.input.value,
                            inputManagerId: this.input.managerId,
                            isInput: true
                        }).then((data) => {
                            console.log(data);
                        });
                    }
                }

                return false;
            },
            report() {
                if (this.date && this.date.length > 1) {
                    let load = Loading.service({
                        target: '#sale-report',
                        fullscreen: false,
                        background: '000'
                    });

                    this.table = false;

                    BX_POST('vaganov:reports.all', 'saleReport',
                    {
                        startDate: this.date[0],
                        endDate: this.date[1],
                        inputValue: this.input.value,
                        inputManagerId: this.input.managerId
                    }).then(table => {
                        this.table = table;
                        this.isAdmin = table.isAdmin;
                    }).finally(() => load.close());
                }
            },
        },
        mounted() {
            this.report();
        },
        watch: {
            date() {
                this.report();
            }
        }
    }
</script>

<style scoped>

</style>