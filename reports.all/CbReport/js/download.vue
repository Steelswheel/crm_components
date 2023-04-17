<template>
    <div class="action-btn d-flex align-items-center">
        <el-button
            @click="getExcel"
            v-if="selectedIds.length > 0"
        >
            <div class="d-flex align-items-center">
                <i class="icon-excel mr-2"></i>
                <span>
                    СКАЧАТЬ EXCEL
                </span>
            </div>
        </el-button>
        <el-button @click="prepare" :loading="loading" :disabled="loading" v-if="!show_cancel">
            {{ btnText }}
        </el-button>
        <el-button type="danger" @click="stop" v-if="show_cancel" icon="el-icon-circle-close">
            ПРЕРВАТЬ
        </el-button>
        <div class="ml-2 action-btn-progress" v-if="loading && percentage < 100">
            <el-progress
                :percentage="percentage"
            />
        </div>
    </div>
</template>

<script>
import { Button, Progress } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    name: 'download',
    components: {
        'el-button': Button,
        'el-progress': Progress
    },
    props: {
        ids: Array,
        date: Array
    },
    data() {
        return {
            loading: false,
            count: 0,
            percentage: 0,
            selectedIds: this.ids,
            intervalId: '',
            btnText: 'СФОРМИРОВАТЬ',
            show_cancel: false
        }
    },
    watch: {
        ids(value) {
            this.selectedIds = value;
        },
        date() {
            this.selectedIds = this.ids;
        },
        percentage(value) {
            if (value >= 100) {
                this.btnText = 'ФОРМИРОВАНИЕ АРХИВОВ';
                this.show_cancel = false;
                clearInterval(this.intervalId);

                const self = this;

                this.intervalId = setInterval(() => {
                  self.getIsCreated();
                }, 2000);
            }
        }
    },
    methods: {
        stop() {
            BX_POST('vaganov:reports.all', 'stopLoading').then(() => {
                this.percentage = 0;
                this.loading = false;
                this.show_cancel = false;
                this.btnText = 'СФОРМИРОВАТЬ';
            });
        },
        download() {
            window.location = '/upload/cb/Отчет.zip';
        },
        getExcel() {
            BX_POST('vaganov:reports.all', 'getExcel', {
              ids: JSON.stringify(this.selectedIds)
            }).then(r => {
                console.log(r);

                let link = document.createElement('a');
                link.setAttribute('href', r);
                link.setAttribute('download', 'ЦБ.xlsx');
                link.click();
            });
        },
        getIsCreated() {
            BX_POST('vaganov:reports.all', 'checkCreated').then((r) => {
                if (r) {
                    this.loading = false;
                    this.btnText = 'СФОРМИРОВАТЬ';
                    clearInterval(this.intervalId);
                }
            });
        },
        getProgress() {
            BX_POST('vaganov:reports.all', 'checkPrepare').then((r) => {
                this.percentage = r;
            });
        },
        load() {
            const self = this;
            this.show_cancel = true;

            this.intervalId = setInterval(() => {
              self.getProgress();
            }, 2000);
        },
        prepare() {
            if (this.selectedIds.length > 0) {
                this.loading = true;
                this.btnText = 'ПОДГОТОВКА';

                BX_POST('vaganov:reports.all', 'prepareArchive', {
                  ids: JSON.stringify(this.selectedIds)
                }).then((r) => {
                  console.log(r);

                  this.load();
                });
            }
        }
    },
    mounted() {
        BX_POST('vaganov:reports.all', 'checkPrepare').then((r) => {
            this.percentage = r;

            if (this.percentage > 0) {
                this.loading = true;

                if (this.percentage < 100) {
                    this.show_cancel = true;

                    const self = this;

                    this.intervalId = setInterval(() => {
                      self.getProgress();
                    }, 2000);
                }
            }
        });
    }
}
</script>

<style scoped>
    .action-btn-progress {
        width: 300px;
    }
    .action-btn-link {
        font-size: 14px;
        line-height: 100%;
    }

    .action-btn-link i {
        font-size: 16px;
        line-height: 100%;
    }

    .action-btn-progress .el-progress__text {
        font-size: 16px;
        line-height: 100%;
        color: #bbb;
    }

    .action-btn .icon-excel {
        width: 14px;
        height: 14px;
    }
</style>