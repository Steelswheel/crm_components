<template>
    <div v-if="table" class="sale-plan-wrapper">
        <div v-if="table.title" class="table-title d-flex align-items-center justify-content-between">
            <div>
                <h4 v-if="table.title">
                    {{ table.title }}
                    <b-icon @click="update" icon="arrow-counterclockwise"/>
                </h4>
            </div>
        </div>
        <div class="sale-report-table-wrapper mt-2">
            <table v-if="table.table" class="sale-report-table" cellspacing="0">
                <thead ref="headerTable" v-if="table.table.head">
                    <tr v-for="(row, i) in table.table.head" :key="i">
                        <th
                            v-for="(col ,j) in row"
                            v-if="col['value'] !== null"
                            :key="`h${i}${j}`"
                            :rowspan="col['rowspan']"
                            :colspan="col['colspan']"
                            :class="col['class']"
                            v-html="col['value']"
                        >
                        </th>
                    </tr>
                </thead>
                <tbody v-if="table.table.body">
                    <tr v-for="(row, i) in table.table.body" :key="i" :class="row['class']">
                        <td
                            v-for="(col, j) in row['value']"
                            v-if="col['value'] !== null"
                            :key="`b${i}${j}`"
                            :rowspan="col['rowspan']"
                            :colspan="col['colspan']"
                            :class="[col['class'],{'sale-report-open-modal':col['modal']}]"
                            @click="isModal(col)"
                        >
                            <div v-if="!col['input']">
                                {{ col['value'] }}
                            </div>
                            <div v-if="col['input']">
                                <input
                                    type="text"
                                    v-if="isAdmin"
                                    @change="setKPI"
                                    :data-input="col['input']"
                                    class="sale-report-input"
                                    :ref="`input-${i}-${j}`"
                                    :value="col['value']"
                                    :data-manager-id="col['manager-id']"
                                    :data-type="col['type']"
                                >
                                <div v-if="!isAdmin">
                                    {{ col['value'] }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <el-dialog
            :visible.sync="modal.show"
            :modal-append-to-body="false"
            :title="modal.title"
            :width = 'modal.width'

            center
        >

            <modalDealsInProcess
                v-if="modal.name === 'dealsInProcess'"
                :isAdmin="isAdmin"
                :content="modal.content"
                />

            <factDeals
                v-if="modal.name === 'factDeals'"
                :isAdmin="isAdmin"
                :content="modal.content"
                :values="table.values"/>

            <loanRepaymentDeals
                v-if="modal.name === 'loanRepaymentDeals'"
                :isAdmin="isAdmin"
                :content="modal.content"/>

        </el-dialog>


    </div>
</template>

<script>
import { Dialog } from 'element-ui';
import { fixPositionSticky } from '@app/helper';
import modalDealsInProcess from './modal-deals-in-process'
import factDeals from './modal-fact-deals'
import loanRepaymentDeals from './modal-loan-repayment-deals'
export default {
    name: 'sale-plan-table',
    components: {
        'el-dialog': Dialog,

        modalDealsInProcess,
        factDeals,
        loanRepaymentDeals,
    },
    props: {
        table: {},
        isAdmin: Boolean
    },
    data() {
        return {
            loading: false,
            input: {
                value: '',
                managerId: '',
                type: ''
            },
            modal: {
                show: false,
                title: '',
                name: '',
                content: '',
                width: ''
            },


        }
    },
    watch: {
        table: {
            deep: true,
            handler() {
                this.$nextTick(() => {
                    fixPositionSticky(this.$refs.headerTable);
                })
            }
        }
    },
    methods: {

        update() {
            this.$emit('update');
        },

        isModal(col) {
            if (col.modal) {
                this.modal.show = true
                this.modal.title = col.title
                this.modal.content = col.data
                this.modal.name = col.modal
                this.modal.width = col.modalWidth ? col.modalWidth : '900px'
            }
        },
        setKPI(event) {
            this.input.value = event.target.value;
            this.input.managerId = event.target.dataset.managerId;
            this.input.type = event.target.dataset.type;
            this.$emit('setKPI', event.target.dataset.input, this.input);
        },

    }
}
</script>

<style scoped>
    >>> .el-dialog{
        margin-top: 15px !important;
    }

    >>> .el-table td.el-table__cell div  {
        word-break: break-word!important;
    }
    >>> .add-to-plan {
        width: 100%;
        border: none;
        padding: 5px 0;
        border-radius: 2px;
        transition: background-color 0.4s;
        text-transform: uppercase;
        color: #FFF;
        font: 600 12px/100% "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
        margin: 3px 0;
        text-align: center;
        line-height: 120%;
    }
    >>> div.add-to-plan {
        display: inline-block;
        width: 80%;
    }
    >>> .include {
        background-color: #5ce56a;
    }
    >>> button.include:hover {
        background-color: #5ced6bb4;
    }
    >>> .exclude {
        background-color: #fc8383;
    }
    >>> button.exclude:hover {
        background-color: #fc8383b4;
    }
    >>> .not-include {
        background-color: #fa9b57;
    }
    >>> .inactive {
        background-color: #d0d0d0!important;
    }

    >>> #sale-plan {
        min-height: 200px;
        min-width: 300px;
    }
</style>