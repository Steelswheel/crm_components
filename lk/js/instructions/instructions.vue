<template>
    <div class="lk-instructions">
        <el-button
            v-if="getAccess"
            type="primary"
            size="small"
            class="mt-2 mb-2"
            @click="addInstruction"
        >
            Добавить
        </el-button>
        <instructionsTable
            :table="table"
            :getAccess="getAccess"
            :users="users"
            @setAuthor="setAuthor"
            @changeInstructionState="changeInstructionState"
            @changeInstruction="changeInstruction"
            @deleteInstrunction="deleteInstrunction"
            @showInstruction="showInstruction"
            @changeSort="changeSort"
        />
        <instructionsModal
            :table="table"
            :visibility.sync="visibility"
            :getAccess="getAccess"
            :addVisibility="addVisibility"
            :changeVisibility="changeVisibility"
            :showId="showId"
            :title="title"
            :text="text"
            @setTitle="setTitle"
            @setText="setText"
            @save="save"
            @changeItem="changeItem"
        />
    </div>
</template>

<script>
import instructionsTable from './instructions-table';
import instructionsModal from './instructions-modal';
import { Button } from 'element-ui';
import { Loading } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    components: {
        'el-button': Button,
        instructionsTable,
        instructionsModal
    },
    name: 'instructions',
    props: {
        isAdmin: Boolean,
        userId: Number
    },
    data() {
        return {
            allowedUsers: [622, 418, 47, 34, 45],
            users: [],
            table: [],
            visibility: false,
            addVisibility: false,
            changeVisibility: false,
            text: '',
            title: '',
            changedId: 0,
            showId: 0
        }
    },
    methods: {
        setTitle(value) {
            this.title = value;
        },
        setText(value) {
            this.text = value;
        },
        changeSort(row) {
            BX_POST('vaganov:lk', 'changeSort', {id: row.ID, sort: row.UF_SORT})
            .catch(e => console.log(e));
        },
        setAuthor(value, row) {
            let index = this.table.findIndex(item => item.ID === row.ID);
            this.table[index].UF_ASSIGNED_BY_ID = value.toString();

            BX_POST('vaganov:lk', 'setAuthor', {id: row.ID, user_id: row.UF_ASSIGNED_BY_ID})
            .catch(e => console.log(e));
        },
        showInstruction(id) {
            this.showId = +id;
            this.changedId = 0;
            this.visibility = !this.visibility;
            this.addVisibility = this.changeVisibility = false;

            let index = this.table.findIndex(item => item.ID === id);

            this.text = this.table[index].UF_TEXT;
            this.title = this.table[index].UF_TITLE;
        },
        getData() {
            const load = Loading.service({
                target: '#lk-instructions',
                fullscreen: false,
                background: '000'
            });

            BX_POST('vaganov:lk', 'getInstructions')
            .then(r => {
                this.table = r.ITEMS;
                this.users = r.USERS;
            })
            .catch(e => console.log(e))
            .finally(() => load.close());
        },
        changeInstructionState(id, state) {
            const load = Loading.service({
                target: '#lk-instructions',
                fullscreen: false,
                background: '000'
            });

            let index = this.table.findIndex(item => item.ID === id);

            BX_POST('vaganov:lk', 'changeInstructionState', {
                id,
                state
            })
            .then(r => {
                this.table[index].UF_IS_ACTIVE = r;
            })
            .catch(e => console.log(e))
            .finally(() => load.close());
        },
        save() {
            BX_POST('vaganov:lk', 'addInstruction', {text: JSON.stringify(this.text), title: this.title})
            .then(() => {
                this.getData();
                this.text = this.title = '';
                this.visibility = this.addVisibility = this.changeVisibility = false;
            })
            .catch(e => console.log(e));
        },
        changeItem() {
            BX_POST('vaganov:lk', 'changeInstruction', {id: this.changedId, text: JSON.stringify(this.text), title: this.title})
            .then(() => {
                this.getData();
                this.text = this.title = '';
                this.visibility = this.addVisibility = this.changeVisibility = false;
            })
            .catch(e => console.log(e));
        },
        addInstruction() {
            this.visibility = !this.visibility;
            this.addVisibility = true;
            this.changeVisibility = false;
        },
        changeInstruction(id) {
            this.visibility = !this.visibility;
            this.addVisibility = false;
            this.changeVisibility = true;

            let index = this.table.findIndex(item => item.ID === id);
            this.text = this.table[index].UF_TEXT ? JSON.parse(this.table[index].UF_TEXT) : '';
            this.title = this.table[index].UF_TITLE;
            this.changedId = +id;
            this.show = 0;
        },
        deleteInstrunction(id) {
            BX_POST('vaganov:lk', 'deleteInstruction', {id})
            .then(() => {
                this.getData();
            })
            .catch(e => console.log(e));
        }
    },
    computed: {
        getAccess() {
            return this.isAdmin || this.allowedUsers.includes(this.userId);
        }
    },
    mounted() {
        this.getData();
    }
}
</script>

<style>
    .lk-instructions-text {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .lk-instructions .ProseMirror {
        height: 310px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>