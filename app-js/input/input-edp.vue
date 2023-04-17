<template>
    <div>

<!--        <div>
            <pre>{{inValue}}</pre>
            <pre>{{UF_FIN_TARIFF_PARTNER}}</pre>
        </div>-->

        <div v-if="inValue">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="d-flex align-items-center">
                        <el-popover
                            v-if="UF_FIN_TARIFF_PARTNER"
                            placement="top-start"
                            width="400"
                            trigger="hover"
                            class="mr-2"
                        >
                            <div>
                                <h6><b>{{UF_FIN_TARIFF_PARTNER.label}}</b>
                                    <small>
                                        от
                                        {{UF_FIN_TARIFF_PARTNER.date}}

                                    </small>
                                </h6>
                            </div>
                            <div>
                                <b>Начало работы: </b> {{UF_FIN_TARIFF_PARTNER.dateCreatePartner}} <br>

                                <b>Статус: </b> {{UF_FIN_TARIFF_PARTNER.status}} <br>
                                <b>Стадия: </b> {{UF_FIN_TARIFF_PARTNER.stageId}} <br>

                            </div>



                            <i slot="reference" class="el-icon-info text-success"></i>
                        </el-popover>
                        <span v-if="inValue.status === 'sale'"  class="badge badge-success mr-2">SALE</span>

                        <span v-if="inValue.statusPartner === 'stop'" class="badge badge-danger mr-2">СТОП</span>
                        <span v-if="inValue.statusPartner === 'np'"   class="badge badge-info mr-2">НП</span>
                        <span v-if="inValue.statusPartner === 'vip'"  class="badge badge-success mr-2">VIP</span>


                        <span></span>
                        <a :href="`/b/edp/?deal_id=${inValue.dealId}/`" target="_blank">
                          <div class="d-flex align-items-center">
                            <div class="partner-widget-status partner-widget-status-reliable"  v-if="IS_RELIABLE_PARTNER === '1'">
                              Н.П.
                            </div>
                            <span>
                              {{inValue.label}}
                            </span>
                          </div>
                        </a>


                        <i v-if="isUpdatePartner" @click="updatePartner" class="el-icon-refresh text-danger ml-2 cursor-pointer"></i>

                    </div>
                    <div v-if="inValue.attorney_number && inValue.attorney_date">
                        <small>
                          ДОВЕРЕННОСТЬ №{{ inValue.attorney_number }} от {{ inValue.attorney_date }}
                        </small>
                    </div>
                    <div v-if="inValue.controlPartner">
                        У.П.
                        <small>
                            <a :href="`/b/edp/?deal_id=${inValue.controlPartner.dealId}/`" target="_blank">
                                {{inValue.controlPartner.name}}
                                <span v-if="inValue.controlPartner.status === 'stop'" class="badge badge-danger mr-2">СТОП</span>
                                <span v-if="inValue.controlPartner.status === 'np'"   class="badge badge-info mr-2">НП</span>
                                <span v-if="inValue.controlPartner.status === 'vip'"  class="badge badge-success mr-2">VIP</span>
                                <span v-if="inValue.controlPartner.status === 'sale'"  class="badge badge-success mr-2">SALE</span>
                                <div class="partner-widget-status partner-widget-status-reliable"  v-if="IS_RELIABLE_PARTNER === '1'">
                                  Н.П.
                                </div>
                            </a>

                        </small>
                    </div>
                </div>
                <div v-if="isEdit">
                    <a href="#" @click.prevent="removeValue" class="text-danger"><b-icon icon="x"/></a>
                </div>
            </div>

            <div class="d-flex justify-content-between" >
                <div class="">
                    <wrap-input
                        label="Телефон"
                        method-update="no"
                        type="phone"
                        :value="inValue.phone"
                    />

                </div>
                <div class="">
                    <wrap-input
                        :contactId="inValue.id"
                        label="Почта"
                        method-update="no"
                        type="email"
                        :value="inValue.email"
                    />
                </div>
            </div>

        </div>
        <v-select
            v-else
            :filterable="false"
            :options="options"
            @search="onSearch"
            :value="inValue "
            @input="setInValue"
        >
            <template slot="no-options">
                пусто
            </template>
            <template #option="{label, controlPartner, status}" >
                <div>
                    <span v-if="status === 'stop'" class="badge badge-danger mr-2">СТОП</span>
                    <span v-if="status === 'np'"   class="badge badge-info mr-2">НП</span>
                    <span v-if="status === 'vip'"  class="badge badge-success mr-2">VIP</span>
                    <span v-if="status === 'sale'"  class="badge badge-success mr-2">SALE</span>
                    {{label}}
                </div>
                <div v-if="controlPartner">
                    У.П.
                    <small>
                        {{controlPartner.name}}
                        <span v-if="controlPartner.status === 'stop'" class="badge badge-danger mr-2">СТОП</span>
                        <span v-if="controlPartner.status === 'np'"   class="badge badge-info mr-2">НП</span>
                        <span v-if="controlPartner.status === 'vip'"  class="badge badge-success mr-2">VIP</span>
                        <span v-if="controlPartner.status === 'sale'"  class="badge badge-success mr-2">SALE</span>
                    </small>
                </div>
            </template>

        </v-select>

    </div>
</template>

<script>
    import wrapInput from './wrap-input'
    import { BX_POST } from '@app/API'
    import vSelect from 'vue-select'
    import { debounce, cloneDeep, isEqual } from 'lodash'
    import { getForm } from '@app/../store/helpStore'
    import { Popover } from 'element-ui'
    //import store from "../../store/store";
    export default {
        inheritAttrs: false,
        name: "input-edp",
        components: {
            vSelect,
            wrapInput,
            'el-popover': Popover,
        },
        props: {
            isEdit: {
                type: Boolean,
                default: true
            },
            value: Object
        },
        watch: {
          value(value) {
            this.inValue = cloneDeep(value);
          }
        },
        data() {
            return {
                inValue: cloneDeep(this.value),
                options: this.value ? [this.value] : undefined,
                isUpdatePartner: false,

            }
        },

        computed: {
            UF_FIN_TARIFF_PARTNER: {
                get: function () {
                    let value = this.$store.getters['form/GET_VALUE']('UF_FIN_TARIFF_PARTNER')
                    return value
                        ? JSON.parse(value)
                        : undefined
                },
                set: function(value){
                    value = JSON.stringify(value)
                    this.$store.commit('form/SET_VALUE', {attribute: 'UF_FIN_TARIFF_PARTNER',value})
                }
            },
            STAGE_ID: getForm('STAGE_ID'),
            IS_PROMO: getForm('IS_PROMO'),
            IS_RELIABLE_PARTNER: getForm('IS_RELIABLE_PARTNER')
        },
        mounted() {
            if ( ["C8:NEW", "C8:EXECUTING", "C8:24", "C8:21",  "C8:20"].includes(this.STAGE_ID) ){

                if (!this.UF_FIN_TARIFF_PARTNER){
                    if (this.inValue){
                        console.log(this.UF_FIN_TARIFF_PARTNER,'ОБНОВИТЬ партнера');
                        this.updatePartner()
                    }

                } else {
                    let cpValue = cloneDeep(this.inValue)
                    delete cpValue.phone
                    delete cpValue.email
                    delete cpValue.date

                    let cp_UF_FIN_TARIFF_PARTNER = cloneDeep(this.UF_FIN_TARIFF_PARTNER)
                    delete cp_UF_FIN_TARIFF_PARTNER.date
                    delete cp_UF_FIN_TARIFF_PARTNER.phone
                    delete cp_UF_FIN_TARIFF_PARTNER.email


                    if (!isEqual(cp_UF_FIN_TARIFF_PARTNER,cpValue)){

                        this.isUpdatePartner = true

                    }
                }

            }





        },
        methods: {
            removeValue(){

                console.log('REMOVE ++ ');
                this.inValue = null
                this.UF_FIN_TARIFF_PARTNER = null
                this.IS_PROMO = '0'
                this.$emit('input', null);
            },
            updatePartner(){
                console.log('update');
                let cpValue = cloneDeep(this.inValue)
                delete cpValue.phone
                delete cpValue.email

                this.UF_FIN_TARIFF_PARTNER = cpValue

                this.IS_PROMO = cpValue.isSaleDate || cpValue.dealIsSale ? '1' : '0'
                this.isUpdatePartner = false
            },
            setInValue(value) {
                this.inValue = value
                this.$emit('input', value);

                this.updatePartner()

            },
            onSearch(search, loading) {
                loading(true);
                this.search(loading, search);
            },

            search: debounce(async function (loading, search) {
                if (search) {
                    this.options = await BX_POST('vaganov:edz.show', 'edp', {search})
                }
                loading(false);
            }, 350),

        }

    }
</script>

<style scoped>
  .partner-widget-status {
    display: inline-block;
    margin: 2px;
    color: #FFF;
    padding: 2px 4px;
    min-width: 19px;
    border-radius: 2px;
    font-size: 10px;
    font-weight: bold;
    background-color: #28a745;
  }
</style>
