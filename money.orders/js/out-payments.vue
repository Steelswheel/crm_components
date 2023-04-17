<template>
    <div class="b-block b-block__content">


        <table class="table mt-2">
            <thead>
            <tr>
                <th>#</th>
                <th>Дата</th>
                <th>Плательщик</th>
                <th>Заемщик</th>

                <th>Сумма</th>
                <th>ВЫПЛАТА <br> ВКЛАДА</th>
                <th>ПЛАН</th>
                <th>ВЫПЛАТА <br> ЗАЙМА</th>
                <th>ФАКТ <br>ЗАЧИСЛЕНИЯ</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item, key) in docs" :key="`${item.number}-${item.DEAL_ID}`"
                :class="{topBorderNone: borderTop(key)}">
                <td>{{ item.number }}</td>
                <td>{{ item.date }}</td>
                <td>{{ inn[item.payerInn] }}</td>
                \
                <td>
                    <template v-if="item.DEAL_ID">

                        <a :href="`/b/edz/?deal_id=${item.DEAL_ID}&show#stage-C8:3`" class="mr-2"
                           target="_blank">{{ item.recipient }}</a>
                        <br>

                        {{ kpk[item.KPK_WORK] ? kpk[item.KPK_WORK] : 'НЕТ КПК' }}
                        <a :href="`/finance_reestr/?deal_id=${item.DEAL_ID}&show`" target="_blank">ФинПлан</a> <br>

                        {{
                            stages[item.STAGE_ID]
                                ? stages[item.STAGE_ID]['NAME']
                                : item.STAGE_ID
                        }}

                    </template>
                    <template v-else>
                        {{ item.recipient }} <br>
                        <span class="text-danger">НЕТ В СРМ</span>
                    </template>

                </td>
                <td>{{ item.sum | price }}</td>
                <td>


                    {{ item.REFOUND_CONTRIB_SUM | price }} <br>


                    <span v-if="item.setTranche === 'V' " class="badge badge-info">
                            ВНЕСТИ <br>
                            {{ item.enrolledSum | price }}
                        </span>

                </td>
                <td>
                    <span
                        :class="{'alert alert-success alert-xs ':item.setTranche === 'T1'}">{{ item.T1 | price }}</span>
                    <br>
                    <span
                        :class="{'alert alert-success alert-xs ':item.setTranche === 'T2'}">{{ item.T2 | price }}</span>
                    <br>
                    <span
                        :class="{'alert alert-success alert-xs ':item.setTranche === 'T3'}">{{ item.T3 | price }}</span>
                    <br>
                    <span
                        :class="{'alert alert-success alert-xs ':item.setTranche === 'T4'}">{{ item.T4 | price }}</span>
                </td>
                <td>
                    {{ item.FT1 | price }} <br>
                    {{ item.FT2 | price }} <br>
                    {{ item.FT3 | price }} <br>
                    {{ item.FT4 | price }}
                </td>
                <td>
                    <br>
                    {{ item.FFT1 | price }} <br>
                    {{ item.FFT2 | price }} <br>
                    {{ item.FFT3 | price }} <br>
                </td>
                <td>
                    <div class="mb-2">
                        <el-select v-model="item.setTranche" placeholder="Select" style="width: 110px" class="mr-2"
                                   size="mini">
                            <el-option value="" label=""/>
                            <el-option value="T1" label="1 Транш"/>
                            <el-option value="T2" label="2 Транш"/>
                            <el-option value="T3" label="3 Транш"/>
                            <el-option value="T4" label="4 Транш"/>
                            <el-option value="V" label="Вклад пай"/>
                        </el-select>
                    </div>
                    <span v-if="item.status === ''">
                        <el-button
                            v-if="item.setTranche"
                            :loading="isLoadingTranche === item.DEAL_ID"
                            :disabled="isLoadingTranche !== false"
                            @click="onSetTranche(item)"
                            size="mini"
                        >Внести</el-button>
                    </span>
                    <span v-else-if="item.status === 'success'" class="badge badge-success">Проведен</span>

                </td>
            </tr>
            </tbody>
        </table>


    </div>
</template>

<script>
import {BX_POST} from '@app/API'
import {Button, Select, Option} from 'element-ui'

export default {
    name: "out-payments",
    props: {
        docs: {
            type: Array,
            default: () => ([])
        },
        stages: {
            type: Object,
            default: () => ({})
        }
    },
    components: {
        'el-button': Button,
        'el-select': Select,
        'el-option': Option,
    },
    data() {
        return {
            isLoading: false,
            doc: [],
            isLoadingTranche: false,
        }
    },
    methods: {
        borderTop(key) {
            return !!(this.doc[key - 1] && this.doc[key].number === this.doc[key - 1].number);

        },
        onSetTranche(doc) {
            this.isLoadingTranche = doc.DEAL_ID

            BX_POST('vaganov:money.orders', 'setTranche', {
                dealId: doc.DEAL_ID,
                date: doc.date,
                sum: doc.sum,
                setTranche: doc.setTranche,
            })
                .then(r => {
                    doc.status = 'success'
                    if (r.STAGE_ID) {
                        doc.STAGE_ID = r.STAGE_ID
                        doc.REFOUND_CONTRIB_SUM = r.REFOUND_CONTRIB_SUM
                    }

                    this.isLoadingTranche = false
                })

        },

    },
    filter: {
        price(num) {
            const formatter = new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
            });

            return formatter.format(num)
        }
    }

}
</script>

<style scoped>
.topBorderNone td {
    border-top: 0;
}
</style>