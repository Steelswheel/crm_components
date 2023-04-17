<template>
    <div class="sber-promotions">
        <el-button
            size="small"
            class="mb-2"
            type="primary"
            @click="setAddDialogVisible"
        >
            Добавить
        </el-button>
        <promotionsModal
            :visibility.sync="visibility"
            :add-visibility="addVisibility"
            :change-visibility="changeVisibility"
            @addPromotionsItem="addPromotionsItem"
            :text="text"
            @setText="setText"
            :title="title"
            @setTitle="setTitle"
            @changeItem="changeItem"
        />
        <promotionsTable
            @setChangeDialogVisible="setChangeDialogVisible"
            @onUpdateRow="onUpdateRow"
            @onRefreshTable="onRefreshTable"
        />
    </div>
</template>

<script>
import promotionsModal from './promotions-modal';
import promotionsTable from './promotions-table';
import { Button } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    components: {
        promotionsModal,
        promotionsTable,
        'el-button': Button
    },
    name: 'promotions',
    data() {
        return {
            visibility: false,
            addVisibility: false,
            changeVisibility: false,
            changeItemId: '',
            text: '',
            title: ''
        }
    },
    methods: {
        onUpdateRow(fn) {
            this.onUpdateRowFn = fn;
        },
        updateRow(id, value = false) {
            this.onUpdateRowFn(id, value);
        },
        onRefreshTable(fn) {
            this.onRefreshTableFn = fn;
        },
        refreshTable() {
            this.onRefreshTableFn();
        },
        setText(text) {
            this.text = text;
        },
        setTitle(text) {
            this.title = text;
        },
        setAddDialogVisible() {
            this.visibility = true;
            this.addVisibility = true;
            this.changeVisibility = false;
        },
        setChangeDialogVisible(row) {
            this.visibility = true;
            this.addVisibility = false;
            this.changeVisibility = true;
            this.changeItemId = row.ID;
            this.text = JSON.parse(row.UF_TEXT) ? JSON.parse(row.UF_TEXT) : '';
            this.title = row.UF_TITLE;
        },
        changeItem() {
            BX_POST('vaganov:lk', 'updatePromotionsItem', {id: this.changeItemId, text: JSON.stringify(this.text), title: this.title})
            .then(() => {
                this.updateRow(this.changeItemId);
                this.changeVisibility = false;
                this.addVisibility = false;
                this.visibility = false;
                this.text = '';
                this.title = '';
                this.changeItemId = '';
            })
            .catch(e => console.log(e));
        },
        addPromotionsItem() {
            BX_POST('vaganov:lk', 'addPromotionsItem', {text: JSON.stringify(this.text), title: this.title})
            .then(() => {
                this.refreshTable();
                this.text = '';
                this.title = '';
                this.visibility = false;
                this.addVisibility = false;
                this.changeVisibility = false;
            })
            .catch(e => console.log(e));
        }
    }
}
</script>

<style>
    .sber-promotions {
        width: fit-content;
    }

    .sber-promotions .el-pagination .el-select .el-input {
        width: 150px;
    }

    .sber-promotions-text {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sber-promotions .ProseMirror {
        height: 310px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>