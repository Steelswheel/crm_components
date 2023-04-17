<template>
    <div>
        <table class="lk-settings-table">
            <thead ref="tableHead">
                <tr>
                    <th>
                        Сортировка
                    </th>
                    <th>
                        Дата создания
                    </th>
                    <th>
                        Автор
                    </th>
                    <th>
                        Активна
                    </th>
                    <th>
                        Название
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="row in table"
                    :key="row.ID"
                >
                    <td>
                        <el-input
                            v-model="row.UF_SORT"
                            @change="$emit('changeSort', row)"
                            v-if="getAccess"
                        />
                        <span v-else>{{row.UF_SORT}}</span>
                    </td>
                    <td>
                        {{row.UF_DATE_CREATE}}
                    </td>
                    <td>
                        <el-select
                            v-if="getAccess"
                            :value="users.find(item => +item.value === +row.UF_ASSIGNED_BY_ID).label"
                            @change="$emit('setAuthor', $event, row)"
                        >
                            <el-option
                                v-for="user in users"
                                :key="user.value"
                                :label="user.label"
                                :value="user.value"
                            />
                        </el-select>
                        <div v-else>
                            {{users.find(item => +item.value === +row.UF_ASSIGNED_BY_ID)?.label}}
                        </div>
                    </td>
                    <td>
                        <el-checkbox
                            :value="row.UF_IS_ACTIVE === 'Y'"
                            @change="$emit('changeInstructionState', row.ID, row.UF_IS_ACTIVE)"
                            v-if="getAccess"
                        >
                            {{ row.UF_IS_ACTIVE === 'Y' ? 'Да' : 'Нет' }}
                        </el-checkbox>
                        <span v-else>
                            {{ row.UF_IS_ACTIVE === 'Y' ? 'Да' : 'Нет' }}
                        </span>
                    </td>
                    <td>
                        {{row.UF_TITLE}}
                    </td>
                    <td>
                        <template v-if="getAccess">
                            <div class="m-2">
                                <el-button
                                    type="primary"
                                    size="small"

                                    @click="$emit('changeInstruction', row.ID)"
                                >
                                    Изменить
                                </el-button>
                            </div>
                            <div class="m-2">
                                <el-button
                                    type="primary"
                                    size="small"
                                    v-if="getAccess"
                                    @click="deleteVisibility = true"
                                >
                                    Удалить
                                </el-button>
                                <el-dialog
                                    :visible.sync="deleteVisibility"
                                    width="30%"
                                    :modal-append-to-body="false"
                                    :close-on-click-modal="false"
                                >
                                    <span>
                                        Удалить инструкцию?
                                    </span>
                                    <span slot="footer" class="dialog-footer">
                                        <el-button type="danger" @click="deleteTrigger(row.ID)">
                                            Да
                                        </el-button>
                                        <el-button @click="deleteVisibility = false">
                                            Нет
                                        </el-button>
                                    </span>
                                </el-dialog>
                            </div>
                        </template>
                        <template v-else>
                            <el-button
                                type="primary"
                                size="small"
                                class="m-2"
                                @click="$emit('showInstruction', row.ID)"
                            >
                                Просмотр
                            </el-button>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import { Button, Checkbox, Option, Select, Input, Dialog } from 'element-ui';
import { fixPositionSticky } from '@app/helper';

export default {
    name: 'instructions-table',
    props: {
        table: Array,
        users: Array,
        getAccess: Boolean
    },
    components: {
        'el-checkbox': Checkbox,
        'el-select': Select,
        'el-option': Option,
        'el-button': Button,
        'el-input': Input,
        'el-dialog': Dialog
    },
    data() {
        return {
            deleteVisibility: false
        }
    },
    watch: {
        table: {
            deep: true,
            handler() {
                setTimeout(() => {
                    fixPositionSticky(this.$refs.tableHead, 'rgb(0 0 0) 0px 9px 16px -16px', this.$refs.tableHead.offsetHeight/2);
                }, 0);
            }
        }
    },
    methods: {
        deleteTrigger(id) {
            this.$emit('deleteInstrunction', id);
            this.deleteVisibility = false;
        }
    }
}
</script>

<style>
    .lk-settings-table > thead {
        position: sticky;
        z-index: 2;
        background-color: white;
        border: 1px solid black;
    }

    .lk-settings-table th {
        border: 1px solid #8f8f8f;
        background-color: #e3e3e3;
        height: 70px;
    }

    .lk-settings-table td {
        border: 1px solid #d8d8d8;
        color: #535c69;
    }

    .lk-settings-table td:nth-child(1) {
        width: 110px;
    }

    .lk-settings-table th, .lk-settings-table td {
        text-align: center;
        font-size: 12px;
        padding: 5px 10px;
    }

    .lk-settings-table .el-select {
        width: 170px;
    }
</style>