<template>
    <div>

        <el-button
            v-if="!message"
            @click="query"
            :loading="isLoading"
            size="mini"
            class="mr-2"
            :disabled="!(tranche === '2' && STAGE_ID === 'C8:6' ||
                       tranche === '3' && STAGE_ID === 'C8:9' ||
                       tranche === '4' && STAGE_ID === 'C8:11' ||
                       tranche === '6' && STAGE_ID === 'C8:30' || isEdit || STAGE_ID === 'C14:PAYMENT_DS')"
        >{{label}}</el-button>

        <span v-if="message" class="small mr-2">{{message}}</span>

    </div>
</template>

<script>
import { BX_POST } from '@app/API';
import { getForm } from '@app/../store/helpStore';
import { mapGetters } from 'vuex';
import { Button } from 'element-ui';

export default {
    name: 'input-tranche',
    components: {
        'el-button': Button,
    },
    props: {
        dealType: String,
        tranche: String,
        des: String,
        isEdit: {
            type: Boolean,
            default: true
        },
        label: String,
    },
    data() {
        return {
            isLoading: false,
            message: undefined,
            sum: undefined,
        }
    },
    computed: {
        ...mapGetters('form', [
            'GET_TRANCHE',
            'GET_VALUES',
        ]),
        STAGE_ID: getForm('STAGE_ID'),
        sumToPay() {
          return Number(this.$store.getters['form/GET_VALUE']('RKO_SUM_TO_PAY'));
        }
    },
    mounted() {
      if (this.GET_TRANCHE?.[this.tranche]?.['REQUEST']) {
        this.message = this.GET_TRANCHE[this.tranche]['REQUEST'];
        this.sum = this.GET_TRANCHE[this.tranche]['SUMM'];
      }
    },
    methods: {
        query() {
            BX_POST('vaganov:order.pay', 'addPay',{
                dealId: this.GET_VALUES.DEAL_ID,
                trancheNum: this.tranche,
                inSum: this.dealType === 'eds' && this.sumToPay > 0 ? this.sumToPay : ''
            }).then(r => {
                console.log(r);
                this.message = r.message
            })

        }
    }
}
</script>

<style scoped>
.labelPlay{
    background: #d9f2ff;
    padding: 5px 6px;
}
</style>