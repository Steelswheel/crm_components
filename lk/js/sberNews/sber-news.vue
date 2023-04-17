<template>
    <div class="sber-news">
        <el-button
            size="small"
            class="mb-2"
            type="primary"
            @click="setAddDialogVisible"
        >
            Добавить
        </el-button>
        <sberNewsModal
            :visibility.sync="visibility"
            :add-visibility="addVisibility"
            :change-visibility="changeVisibility"
            @addSberNewsItem="addSberNewsItem"
            :text="text"
            @setText="setText"
            :title="title"
            @setTitle="setTitle"
            @changeItem="changeItem"
        />
        <sberNewsTable
            @setChangeDialogVisible="setChangeDialogVisible"
            @onUpdateRow="onUpdateRow"
            @onRefreshTable="onRefreshTable"
        />
    </div>
</template>

<script>
import sberNewsModal from './sber-news-modal';
import sberNewsTable from './sber-news-table';
import { Button } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    components: {
        sberNewsModal,
        sberNewsTable,
        'el-button': Button
    },
    name: 'sber-news',
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
            BX_POST('vaganov:lk', 'updateSberNewsItem', {id: this.changeItemId, text: JSON.stringify(this.text), title: this.title})
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
        addSberNewsItem() {
            BX_POST('vaganov:lk', 'addSberNewsItem', {text: JSON.stringify(this.text), title: this.title})
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
    .sber-news {
        width: fit-content;
    }

    .sber-news .el-pagination .el-select .el-input {
        width: 150px;
    }

    .sber-news-text {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sber-news .ProseMirror {
        height: 310px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>