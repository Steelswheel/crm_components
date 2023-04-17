<template>
    <div>
        <div class="moneyOrder-inPaymentWrapper">
            <div class="moneyOrder-inPaymentItem moneyOrder-inPaymentItem--header bg-blue mt-3">
                <div class="moneyOrder-inPaymentItem__doc">
                    Документ
                </div>
                <div class="moneyOrder-inPaymentItem__crm">
                    CRM
                </div>
                <div class="moneyOrder-inPaymentItem__fin">
                    Сумма из документа
                </div>
                <div class="moneyOrder-inPaymentItem__fin">
                    ВЫПЛАТА <br> ВКЛАДА
                </div>
                <div class="moneyOrder-inPaymentItem__fin">
                    ПЛАН
                </div>
                <div class="moneyOrder-inPaymentItem__fin">
                    ВЫПЛАТА <br> ЗАЙМА
                </div>
                <div class="moneyOrder-inPaymentItem__borrower">
                    ФАКТ <br>ЗАЧИСЛЕНИЯ
                </div>
                <div class="moneyOrder-inPaymentItem__btn">
                    КНОПКА
                </div>
            </div>

            <div v-for="(item,key) in inDocs" :key="`${item.number}-${item.DEAL_ID}`">
                <div class="moneyOrder-inPaymentItem">
                    <div class="moneyOrder-inPaymentItem__doc">
                        <div v-if="!item.isSkip">
                            <span class="badge badge-dark mr-2">{{ item.payment }}</span>
                            <template v-if="item.status === 'success'">
                                <span  class="badge badge-success mr-2"> ОБРАБОТАН</span>
                            </template>
                            <template v-else>
                                <span class="badge badge-danger mr-2"> НЕ ОБРАБОТАН </span>
                                <span @click.prevent="onSkipDocument(key)" class="add-dotted">Убрать</span>
                                <br>
                                <span class="badge badge-warning" v-if="!item.DZ && !item.DV">
                                Не найден номер договора
                                </span>
                                <div v-else class="badge badge-success">
                                    <span v-if="item.DZ">{{ item.DZ }}</span>
                                    <span v-if="item.DV">{{ item.DV }}</span>
                                </div>
                            </template>
                        </div>
                        <div>
                            <span class="add-dotted mr-2" @click="docOpen(key)">Информация о документе</span>
                            <span v-if="item.isSkip" class="add-dotted"  @click.prevent="onSkipDocument(key)" >восстановить</span>
                        </div>
                    </div>
                    <template v-if="!item.isSkip">
                        <template v-if="item.crmAr.length > 0 ">
                            <div class="moneyOrder-inPaymentItem__crmAr">
                                <div class="d-flex justify-content-between w-100" v-for="crmItem in item.crmAr" :key="crmItem.DEAL_ID">
                                    <div class="moneyOrder-inPaymentItem__crm">
                                        <a :href="`/b/edz/?deal_id=${crmItem.DEAL_ID}&show#stage-C8:3`" class="mr-2"
                                           target="_blank">{{ crmItem.FIO }}</a>
                                        <div v-if="crmItem.FIO_GUARANTOR">{{crmItem.FIO_GUARANTOR}}</div>
                                        <span class="badge badge-primary"><i class="el-icon-search "></i>{{ crmItem.findInfo }}</span>
                                        <br>

                                        {{ kpk[crmItem.KPK_WORK] ? kpk[crmItem.KPK_WORK] : 'НЕТ КПК' }}
                                        <a :href="`/finance_reestr/?deal_id=${crmItem.DEAL_ID}&show`" target="_blank">ФинПлан</a> <br>

                                        {{
                                            stages[crmItem.STAGE_ID]
                                                ? stages[crmItem.STAGE_ID]['NAME']
                                                : crmItem.STAGE_ID
                                        }}
                                    </div>

                                    <div class="d-flex">
                                        <div class="moneyOrder-inPaymentItem__fin">
                                            {{ item.sum | price }}
                                        </div>
                                        <div class="moneyOrder-inPaymentItem__fin">
                                            {{crmItem.REFOUND_CONTRIB_SUM | price}}
                                            <br>
                                            <span v-if="crmItem.setTranche === 'V' " class="badge badge-info">
                                                 ВНЕСТИ <br>
                                                 {{ crmItem.enrolledSum | price}}
                                              </span>
                                            <span class="badge badge-light">
                                                Вступление <br>
                                                {{crmItem.UF_CHECK_PAYMENT_ENTER_KPK_SUM}}
                                            </span>
                                            <span v-if="crmItem.PAYMENT_PFR_SUM" class="badge badge-success">
                                                ПФР <br>
                                                {{crmItem.PAYMENT_PFR_SUM}}
                                            </span>
                                        </div>
                                        <div class="moneyOrder-inPaymentItem__fin">
                                            <!-- ПЛАН-->
                                            <span :class="{'alert alert-success alert-xs ':crmItem.setTranche === 'T1'}">{{crmItem.T1 | price}}</span> <br>
                                            <span :class="{'alert alert-success alert-xs ':crmItem.setTranche === 'T2'}">{{crmItem.T2 | price}}</span> <br>
                                            <span :class="{'alert alert-success alert-xs ':crmItem.setTranche === 'T3'}">{{crmItem.T3 | price}}</span> <br>
                                            <span :class="{'alert alert-success alert-xs ':crmItem.setTranche === 'T4'}">{{crmItem.T4 | price}}</span>

                                        </div>
                                        <div class="moneyOrder-inPaymentItem__fin">
                                            <!-- ВЫПЛАТА ЗАЙМА-->
                                            {{crmItem.FT1 | price}} <br>
                                            <div>
                                                <p class="p-mini" style="margin-top: -5px">П {{ crmItem.AMOUNT_DZ }}</p>
                                                <p class="p-mini">Д {{ crmItem.DOU_SUMM }}</p>
                                            </div>

                                            {{crmItem.FT2 | price}} <br>
                                            {{crmItem.FT3 | price}} <br>
                                            {{crmItem.FT4 | price}}
                                        </div>
                                        <div class="moneyOrder-inPaymentItem__fin">
                                            <!-- ФАКТ ЗАЧИСЛЕНИЯ -->
                                            <span>K.П {{crmItem.COMISSION_SUM | price}}</span> <br>
                                            <span>K.Д {{crmItem.DOU_SUM_FACT | price}}</span> <br>
                                            <span>{{crmItem.FFT1 | price}}</span> <br>
                                            <span>{{crmItem.FFT2 | price}}</span> <br>
                                            <span>{{crmItem.FFT3 | price}}</span>
                                        </div>
                                        <div class="moneyOrder-inPaymentItem__btn">
                                            <div v-if="item.handler">
                                                <span class="badge badge-success">
                                                    {{methodName.find(i => i.value === item.handler).label}}
                                                </span>
                                            </div>
                                            <div v-else>
                                                <div class="mb-2">
                                                    <el-select
                                                        v-model="crmItem.setTranche"
                                                        placeholder="Select"
                                                        style="width: 110px"
                                                        class="mb-2"
                                                        size="mini">
                                                        <el-option value="" label=""/>
                                                        <el-option
                                                            v-for="item in methodName"
                                                            :value="item.value"
                                                            :label="item.label"
                                                            :key="item.key"
                                                        />
                                                    </el-select>
                                                    <el-button
                                                        v-if="crmItem.setTranche"
                                                        :loading="isLoadingTranche === crmItem.DEAL_ID"
                                                        :disabled="isLoadingTranche !== false"
                                                        @click="onSetTranche(item,crmItem)"
                                                        size="mini"
                                                    >Внести</el-button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="moneyOrder-inPaymentItem__info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered table-sm table-success">
                                            <thead>
                                            <tr>
                                                <th> Дата</th>
                                                <th> Сумма</th>
                                                <th> Назначение платежа</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td> {{ item.date }}</td>
                                                <td> {{ item.sum }}</td>
                                                <td> {{ item.PurposeOfPayment }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table v-if="item.payment === 'IN'" class="table table-bordered table-sm table-warning">
                                            <thead>
                                            <tr>
                                                <th>Плательщик</th>
                                                <th>ИНН</th>
                                                <th>Банк</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="">{{ item.payer }}</td>
                                                <td>{{ item.payerInn }}</td>
                                                <td>{{ item.payerBank }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <table v-else class="table table-bordered table-sm table-info">
                                            <thead>
                                            <tr>
                                                <th >Плательщик</th>
                                                <th>ИНН</th>
                                                <th>Банк</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>{{ item.recipient }}</td>
                                                <td>{{ item.recipientInn }}</td>
                                                <td>{{ item.recipientBank }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                </div>

                <div v-if="docOpenArr.includes(key)">
                    <table class="table table-bordered table-sm table-success">
                        <thead>
                        <tr>
                            <th> Номер</th>
                            <th> Дата</th>
                            <th> Сумма</th>
                            <th> Код оплаты</th>
                            <th> Назначение платежа</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="bg-white"> {{ item.number }}</td>
                            <td class="bg-white"> {{ item.date }}</td>
                            <td class="bg-white"> {{ item.sum }}</td>
                            <td class="bg-white"> {{ item.typeOfPayment }}</td>
                            <td> {{ item.PurposeOfPayment }}</td>
                        </tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered table-sm">
                        <thead>
                        <tr>
                            <th colspan="3" style="width: 50%" class="table-warning">Информация о плательщике</th>
                            <th colspan="3" style="width: 50%" class="table-info">Информация о получателье</th>
                        </tr>
                        <tr>
                            <th class="table-warning">Плательщик</th>
                            <th>ИНН</th>
                            <th>Банк</th>
                            <th class="table-info">Получатель</th>
                            <th>ИНН</th>
                            <th>Банк</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="table-warning">{{ item.payer }}</td>
                            <td>{{ item.payerInn }}</td>
                            <td>{{ item.payerBank }}</td>
                            <td class="table-info">{{ item.recipient }}</td>
                            <td>{{ item.recipientInn }}</td>
                            <td>{{ item.recipientBank }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { BX_POST } from '@app/API'
import { Button, Select, Option } from 'element-ui'
import { cloneDeep } from 'lodash'
export default {
    name: "in-payment",
    components: {
        'el-button': Button,
        'el-select': Select,
        'el-option': Option,
    },
    props: {
        docs: {
            type: Array,
            default: () => ([])
        },
        stages: {
            type: Object,
            default: () => ({})
        },
    },
    data() {
        return {
            inDocs: cloneDeep(this.docs),
            kpk: {
                739: ''
            },
            docOpenArr: [],
            isLoading: false,
            isLoadingTranche: false,
            methodName: [
                {value:'T1', label: '1 Транш'},
                {value:'T2', label: '2 Транш'},
                {value:'T3', label: '3 Транш'},
                {value:'T4', label: '4 Транш'},
                {value:'V', label: 'Вклад пай'},
                {value:'in_entranceKpk', label: 'Вступление'},
                {value:'in_ff1', label: 'пай 1'},
                {value:'in_ff2', label: 'пай 2'},
                {value:'in_ff3', label: 'пай 3'},
                {value:'in_pfr', label: 'ПФР'}
            ],
        }
    },
    watch: {
        docs() {
            this.docOpenArr = []
            this.inDocs = cloneDeep(this.docs)
        }
    },
    methods: {
        docOpen(key) {
            if (this.docOpenArr.includes(key)) {
                let index = this.docOpenArr.indexOf(key)
                this.docOpenArr.splice(index, 1)
            } else {
                this.docOpenArr.push(key)
            }
        },
        onSkipDocument(key){
            this.inDocs[key].isSkip = !this.inDocs[key].isSkip

            BX_POST('vaganov:money.orders', 'onSkipDocument', {
                docId: this.inDocs[key].docId,
                isSkip: this.inDocs[key].isSkip ? 1 : 0,
            })
        },
        onSetTranche(doc, deal) {
            this.isLoadingTranche = deal.DEAL_ID;

            BX_POST('vaganov:money.orders', 'setTranche', {
                docId: doc.docId,
                dealId: deal.DEAL_ID,
                date: doc.date,
                sum: doc.sum,
                setTranche: deal.setTranche,
            })
            .then(r => {
                console.log(r);
                doc.status = 'success'
                doc.crmAr = r.crm
                doc.handler = deal.setTranche

                this.isLoadingTranche = false
            });
        },
    },
}

</script>

<style scoped>

</style>
