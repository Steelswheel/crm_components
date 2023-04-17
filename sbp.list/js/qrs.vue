<template>
    <div class="sbp-list-qrs">
        <div class="sbp-list-qrs-filter d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center p-2" style="background-color: #FFF; height: 42px;">
                <div class="mr-4">
                    Дата создания:
                </div>
                <el-date-picker
                    v-model="date"
                    type="date"
                    value-format="dd.MM.yyyy"
                    format="dd.MM.yyyy"
                >
                </el-date-picker>
            </div>
            <el-button
                @click="getExcel"
                v-show="tableData.length > 0"
            >
                <div class="d-flex align-items-center">
                    <i class="icon-excel mr-2"></i>
                    <span>
                    СКАЧАТЬ EXCEL
                </span>
                </div>
            </el-button>
        </div>
        <div class="sbp-list-qrs-table">
            <el-table
                :data="tableData"
                :default-sort = "{prop: 'orderCreateDate', order: 'descending'}"
                style="width: 100%; border: 1px solid #000"
                border
                height="400"
                :header-cell-style="{
                    textAlign: 'center',
                    borderRight: '1px solid #000',
                    borderBottom: '1px solid #000',
                    backgroundColor: '#fff',
                    color: '#000'
                }"
                :cell-style="{
                    textAlign: 'center',
                    borderRight: '1px solid #000',
                    borderBottom: '1px solid #000',
                    color: '#000'
                }"
            >
                <el-table-column
                    label="ДАТА СОЗДАНИЯ"
                    prop="orderCreateDate"
                ></el-table-column>
                <el-table-column
                    label="ДАТА ОПЛАТЫ"
                    prop="operationDateTime"
                ></el-table-column>
                <el-table-column
                    label="СДЕЛКА"
                    prop="deal"
                >
                    <template slot-scope="scope">
                        <a :href="`/b/eds/?deal_id=${scope.row.deal_id}`" target="_blank">
                            {{ scope.row.deal_name }}
                        </a>
                    </template>
                </el-table-column>
                <el-table-column
                    label="СУММА"
                    prop="operationSum"
                ></el-table-column>
            </el-table>
        </div>
    </div>
</template>

<script>
import { Table, TableColumn, DatePicker, Button } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    name: 'qrs',
    components: {
        'el-button': Button,
        'el-table': Table,
        'el-table-column': TableColumn,
        'el-date-picker': DatePicker
    },
    data() {
        return {
            date: '',
            tableData: []
        }
    },
    watch: {
        date(value) {
            if (value.length > 0) {
                BX_POST('vaganov:sbp.list', 'getSbpData', {
                    date: value
                }).then((response) => {
                    this.tableData = response;
                    console.log(response);
                }, (error) => {
                    console.log(error);
                });
            } else {
                this.tableData = [];
            }
        }
    },
    methods: {
        getExcel() {
            BX_POST('vaganov:sbp.list', 'getExcel', {
                data: JSON.stringify(this.tableData)
            }).then(r => {
                console.log(r);

                let link = document.createElement('a');
                link.setAttribute('href', r);
                link.setAttribute('download', 'QR.xlsx');
                link.click();
            });
        }
    }
}
</script>

<style scoped>
 .icon-excel {
     width: 16px;
     height: 16px;
 }
</style>