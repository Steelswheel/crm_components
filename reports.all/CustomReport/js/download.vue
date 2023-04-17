<template>
    <div class="d-flex align-items-center">
        <el-button
            @click="getExcel"
        >
            <div class="action-btn-link d-flex align-items-center">
                <i class="icon-excel mr-2"></i>
                <span>
                    СКАЧАТЬ EXCEL
                </span>
            </div>
        </el-button>
    </div>
</template>

<script>
import { Button } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    name: 'report-table',
    components: {
        'el-button': Button
    },
    data() {
        return {}
    },
    methods: {
        getExcel() {
            BX_POST('vaganov:reports.all', 'getCustomExcel').then(r => {
              let link = document.createElement('a');
              link.setAttribute('href', r);
              link.setAttribute('download', 'custom.xlsx');
              link.click();
            });
        }
    }
}
</script>

<style>
    .action-btn-link i {
        font-size: 16px;
        line-height: 100%;
    }

    .action-btn-link .icon-excel {
        width: 14px;
        height: 14px;
    }
</style>