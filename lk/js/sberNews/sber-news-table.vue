<template>
    <div class="sber-news-table" id="sber-news">
        <grid
            controller="vaganov:lk"
            getRowsFn="sberNewsRows"
            updateRowFn="sberNewsUpdateRow"
            @onUpdateRow="onUpdateRow"
            @onRefreshTable="onRefreshTable"
        >
            <template #UF_DATE_CREATE="{row}">
                {{ row.UF_DATE_CREATE }}
            </template>
            <template #UF_PUBLICATION_DATE="{row}">
                <el-date-picker
                    v-if="row.UF_IS_ACTIVE === 'Y'"
                    v-model="row.UF_PUBLICATION_DATE"
                    @change="setPublicationDate(row)"
                    value-format="dd.MM.yyyy"
                    format="dd.MM.yyyy"
                    :picker-options="{
                        firstDayOfWeek: 1
                    }"
                />
                <span v-else>
                    {{row.UF_PUBLICATION_DATE}}
                </span>
            </template>
            <template #UF_ASSIGNED_BY_ID="{row}">
                {{row.UF_ASSIGNED_BY_ID}}
            </template>
            <template #UF_IS_ACTIVE="{row}">
                <el-checkbox
                    :value="row.UF_IS_ACTIVE === 'Y'"
                    @change="changeSberNewsState(row)"
                >
                    {{ row.UF_IS_ACTIVE === 'Y' ? 'Да' : 'Нет' }}
                </el-checkbox>
            </template>
            <template #UF_TITLE="{row}">
                {{row.UF_TITLE}}
            </template>
            <template #UF_TEXT="{row}">
                <div
                    v-if="row.UF_TEXT"
                    v-html="JSON.parse(row.UF_TEXT)"
                    class="sber-news-text"
                ></div>
            </template>
            <template #UF_IMAGE="{row}">
                <sberNewsAddImage
                    :id="row.ID"
                    :file="row.UF_IMAGE"
                    @addImage="updateRow(row.ID)"
                    @deleteImage="updateRow(row.ID)"
                />
            </template>
            <template #default="{row}">
                <div class="m-2">
                    <el-button
                        type="primary"
                        size="small"
                        @click="$emit('setChangeDialogVisible', row)"
                    >
                        Изменить
                    </el-button>
                </div>
                <div class="m-2">
                    <el-button
                        type="primary"
                        size="small"
                        @click="deleteSberNewsItem(row.ID)"
                    >
                        Удалить
                    </el-button>
                </div>
            </template>
        </grid>
    </div>
</template>

<script>
import { Checkbox, Button, DatePicker } from 'element-ui';
import grid from '@app/bx/grid';
import sberNewsAddImage from './sber-news-add-image';
import { BX_POST } from '@app/API';

export default {
    name: 'sber-news-table',
    components: {
        'el-checkbox': Checkbox,
        'el-button': Button,
        'el-date-picker': DatePicker,
        sberNewsAddImage,
        grid
    },
    methods: {
        onUpdateRow(fn) {
            this.onUpdateRowFn = fn;
            this.$emit('onUpdateRow', fn);
        },
        onRefreshTable(fn) {
            this.onRefreshTableFn = fn;
            this.$emit('onRefreshTable', fn);
        },
        updateRow(id, value = false) {
            this.onUpdateRowFn(id, value);
        },
        refreshTable() {
            this.onRefreshTableFn();
        },
        changeSberNewsState(row) {
            BX_POST('vaganov:lk', 'changeSberNewsItemState', {
                id: row.ID,
                state: row.UF_IS_ACTIVE
            })
            .then(() => {
                this.updateRow(row.ID);
            })
            .catch(e => console.log(e));
        },
        setPublicationDate(row) {
            BX_POST('vaganov:lk', 'changeSberNewsPublicationDate', {id: row.ID, date: row.UF_PUBLICATION_DATE})
            .then(() => this.updateRow(row.ID))
            .catch(e => console.log(e));
        },
        deleteSberNewsItem(id) {
            BX_POST('vaganov:lk', 'deleteSberNewsItem', {id})
            .then(() => this.refreshTable())
            .catch(e => console.log(e));
        }
    }
}
</script>

<style lang="scss">
    .sber-news table {
        .el-date-editor {
            width: 130px;
            font-size: 12px;
        }

        td {
            font-size: 12px;
            text-align: center;
            vertical-align: middle!important;

            &:nth-child(3) {
                width: 150px;
            }

            &:nth-child(5), &:nth-child(6), &:nth-child(7) {
                width: 200px;
            }
        }
    }
</style>