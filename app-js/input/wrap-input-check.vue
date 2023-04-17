<template>
    <div>
        <el-tooltip placement="top" effect="light">
            <div slot="content">


                <div class="text-center1">
                    Поле: <b>{{attribute.label}}</b>
                </div>
                <div class="text-center1 mt-2">
                    Проверяющий: <b>{{settings.userName}}</b>
                    <template v-if="checkDate">
                        <div class="mt-2" style="border: 1px solid #67C23A; padding: 6px 9px;" v-if="checkStatus === 'success'">
                            <b class="mr-1">{{ checkUserLastName.toUpperCase() }}:</b>
                            <b>ПОЛЕ ЗАПОЛНЕНО КОРРЕКТНО</b>
                            {{checkDate}}
<!--                            <a v-if="settings.rule" href="#" @click.prevent="cancel">
                                отмена
                            </a>-->
                        </div>
                        <div class="mt-2" style="border: 1px solid #F56C6C; padding: 6px 9px;" v-if="checkStatus === 'failure'">
                            <b class="mr-1">{{ checkUserLastName.toUpperCase() }}:</b>
                            <b>ПОЛЕ ЗАПОЛНЕНО НЕ КОРРЕКТНО</b>
                            {{checkDate}}
<!--                            <a v-if="settings.rule" href="#" @click.prevent="cancel">
                                отмена
                            </a>-->
                        </div>
                        <div class="mt-2" style="border: 1px solid #E6A23C; padding: 6px 9px;" v-if="checkStatus === 'revision'">
                            <b class="mr-1">{{ checkUserLastName.toUpperCase() }}:</b>
                            <b>ПОЛЕ НА ДОРАБОТКЕ</b>
                            {{checkDate}}
<!--                            <a v-if="settings.rule" href="#" @click.prevent="cancel">
                                отмена
                            </a>-->
                        </div>
                    </template>
                    <template v-else>
                        <el-button
                            v-if="settings.rule"
                            size="mini"
                            @click="check('success')"
                            class="ml-2"
                            type="success"
                            :loading="isLoading"
                            plain
                            style="background-color: #fff; font-weight: 900; color: #67C23A; padding: 2px 7px;"
                        >
                          ВЕРНО
                        </el-button>
<!--                        <el-button
                            v-if="settings.rule"
                            size="mini"
                            @click="check('failure')"
                            class="ml-2"
                            type="danger"
                            :loading="isLoading"
                            plain
                            style="background-color: #fff; color: #F56C6C; font-weight: 900; padding: 2px 7px;"
                        >
                            НЕ ВЕРНО
                        </el-button>-->
                        <el-button
                            v-if="settings.rule"
                            size="mini"
                            @click="check('revision')"
                            class="ml-2"
                            type="warning"
                            :loading="isLoading"
                            plain
                            style="background-color: #fff; color: #E6A23C; font-weight: 900; padding: 2px 7px;"
                        >
                          ПОСТАВИТЬ ЗАДАЧУ
                        </el-button>
                    </template>
                </div>
            </div>

            <div>
<!--                <div v-if="checkDate && checkStatus === 'success'" class="stamp-green">
                    ВЕРНО
                </div>
                <div v-else-if="checkDate && checkStatus === 'failure'" class="stamp-red">
                    НЕ ВЕРНО
                </div>
                <div v-else-if="checkDate && checkStatus === 'revision'" class="stamp-yellow">
                    НА ДОРАБОТКЕ
                </div>
                <i v-else class="el-icon-circle-close iconLong iconLong&#45;&#45;red" ></i>-->
            </div>

        </el-tooltip>

        <template v-if="checkDate">

            <el-radio class="mt-2"  size="small" border  >Да, верно</el-radio>

        </template>


        <div v-if="settings.rule && !checkDate">

            <el-radio  size="small" border :value="false"  @change="check('success')">Да, верно</el-radio>
<!--            <el-button

                size="mini"
                @click="check('success')"
                class="ml-2"
                style="padding: 7px 20px 7px 20px;     border-radius: 3px;"
                :loading="isLoading"
                plain

            >
                Да, верно
            </el-button>-->

            <el-button
                href="#"
                @click.prevent="check('revision')"
                class="ml-2"
                :loading="isLoading"
                type="text"
            >
                Нет, поставить задачу
            </el-button>

        </div>
    </div>
</template>

<script>

import { BX_POST } from '@app/API';
import { Tooltip, Button, Radio } from 'element-ui';
import {mapActions, mapMutations} from "vuex";

export default {
    name: 'wrap-input-check',
    components: {
        'el-tooltip': Tooltip,
        'el-button': Button,
        'el-radio': Radio,
    },
    props:{
        settings: {},
        alias: {},
        attribute: {},
        dealId: {},
        assigned: {},
        fio: {}
    },
    data() {
        return {
            isLoading: false,
            /*checkDate: this.settings.checkDate,
            checkUserLastName: this.settings.checkUserLastName,
            checkStatus: this.settings.checkStatus*/
        }
    },
    computed: {
        UF_SETTINGS_FIELDS_ITEM: {
            get: function () {
                const fields = this.$store.getters['form/GET_VALUE']('UF_SETTINGS_FIELDS')
                const item = fields.filter(i => i.field === this.alias )
                return item.length > 0 ? item[0] : false
            },
            set: function(value){
                const fields = this.$store.getters['form/GET_VALUE']('UF_SETTINGS_FIELDS')
                const item = fields.filter(i => i.field === this.alias )[0]
                item.checkDate = value.checkDate
                item.checkUserLastName = value.checkUserLastName
                item.checkStatus = value.checkStatus


                // this.$store.commit('form/SET_VALUE', {attribute: 'UF_SETTINGS_FIELDS', fields})
            }
        },
        checkDate(){
            return this.UF_SETTINGS_FIELDS_ITEM.checkDate
        },
        checkUserLastName(){
            return this.UF_SETTINGS_FIELDS_ITEM.checkUserLastName
        },
        checkStatus(){
            return this.UF_SETTINGS_FIELDS_ITEM.checkStatus
        },

    },
    methods: {
        ...mapActions('form', [
            'FETCH',
        ]),
        ...mapMutations('form', [
            'SET_TASKS',
        ]),
        check(status) {
            this.isLoading = true;

            BX_POST('vaganov:edz.show', 'checkField', {
                dealId: this.dealId,
                assigned: this.assigned,
                field: this.settings.field,
                status: status,
                label: this.attribute.label,
                fio: this.fio
            }).then(r => {

                if(status === 'revision'){
                    this.SET_TASKS(r)
                }
                if(status === 'success'){
                    this.UF_SETTINGS_FIELDS_ITEM = {
                        checkDate: r.checkDate,
                        checkUserLastName: r.checkUserLastName,
                        checkStatus: r.checkStatus,
                    }
                }


            }).finally(() => {
                this.isLoading = false;
            });
        },
        cancel() {
            this.isLoading = true;

            BX_POST('vaganov:edz.show', 'checkFieldCancel', {
                dealId: this.dealId,
                field: this.settings.field,
            }).then(() => {

                this.UF_SETTINGS_FIELDS_ITEM = {
                    checkDate: false,
                    checkUserLastName: false,
                    checkStatus: false,
                }

            }).finally(() => {
                this.isLoading = false;
            });
        }
    }
}
</script>

<style scoped>
    .stamp-yellow {
        font-size: 12px;
        border: 1px solid #E6A23C;
        background-color: #fff;
        font-weight: 900;
        color: #E6A23C;
        padding: 2px 7px;
    }
    .stamp-red {
      font-size: 12px;
      border: 1px solid #F56C6C;
      background-color: #fff;
      font-weight: 900;
      color: #F56C6C;
      padding: 2px 7px;
    }
    .stamp-green {
      font-size: 12px;
      border: 1px solid #67C23A;
      background-color: #fff;
      font-weight: 900;
      color: #67C23A;
      padding: 2px 7px;
    }
    .iconLong {
        font-size: 28px;
        line-height: 28px;
        height: 28px;
        display: block;
    }
    .iconLong--red {
        color:red;
    }
    .iconLong--success {
        color: #9dff00;
    }
</style>