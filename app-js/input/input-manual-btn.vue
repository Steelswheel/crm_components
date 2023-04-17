<template>
    <div class="d-flex align-items-center">



        <input
            :disabled="!isEdit"
            v-model="inValue"
            type="checkbox"
            class="mr-2"
        >

        <label @click="onManual" :class="[{'border-dashed': isEdit},`mr-2`]">Ручной ввод</label>



        <div v-if="isManualEdit && inValue">
            <el-button size="mini" @click="douPay">М</el-button>
            <el-button size="mini" @click="tranche(1)">Т1</el-button>
            <el-button size="mini" @click="tranche(2)">Т2</el-button>
            <el-button size="mini" @click="tranche(3)">Т3</el-button>
<!--            <el-button size="mini" @click="tranche(4)">Т4</el-button>-->
        </div>
    </div>

</template>

<script>

import {
    breakdownOfTrenches,
    getSpacing,
    getDeposit,
    getRskCommission,
} from '@app/calc/financial'
import { mapMutations } from 'vuex'
import { getForm, getFormInt } from '@app/../store/helpStore'
import { Button } from 'element-ui'

export default {
    name: "input-manual-btn",
    components: {
        'el-button': Button,
        //'el-checkbox': Checkbox,
    },
    props: {
        value: [String, Number],
        isEdit: {
            type: Boolean,
            default: true
        },
    },
    data(){
        return {
            inValue: this.value === "1",
            isManualEdit: false,
        }
    },
    watch: {
        value(){
            if (this.inValue !== this.value){
                this.inValue = this.value === "1"
            }
        },
        inValue(){
            if (this.inValue) this.onManual()

            this.$emit('input', this.inValue ? "1" : "0")
        }

    },
    computed: {

        MANUAL_CALC: getForm('MANUAL_CALC'),
        GET_VALUES: {
            get: function () {
                return this.$store.getters['form/GET_VALUES']
            },
            set: function(values){
                this.$store.commit('form/SET_VALUES_OBJ', values)
            }
        },
        TRANSACTION_COMMISION: getFormInt('TRANSACTION_COMMISION'),
        LOAN_PROGRAM: getForm('LOAN_PROGRAM'),
        MSK_SUMM: getFormInt('MSK_SUMM'),
        RSK_SUMM: getFormInt('RSK_SUMM'),
        SUMMA: getFormInt('SUMMA'),
        IS_RSK_DEPOSIT: getFormInt('IS_RSK_DEPOSIT'),
        AMOUNT_DZ: getFormInt('AMOUNT_DZ'),
        DOU_SUMM: getFormInt('DOU_SUMM'),
        AMOUNT_DV: getFormInt('AMOUNT_DV'),
        AMOUNT_OF_CONTRIBUTION: getFormInt('AMOUNT_OF_CONTRIBUTION'),
        UF_BALANCE_ON_HAND_MSK: getFormInt('UF_BALANCE_ON_HAND_MSK'),
        UF_LOAN_PROVIDER: getForm('UF_LOAN_PROVIDER'),
    },
    methods: {
        ...mapMutations('form',[
            'SET_ATTRIBUTE_EDIT_OBJ'
        ]),
        onManual(){
            if (this.isEdit){
                if (!this.inValue) this.inValue = true
                this.isManualEdit = true
                this.openEditFields()
            }


        },
        tranche(TRANSHEE_LPR){

            this.GET_VALUES = breakdownOfTrenches({ ...this.GET_VALUES, TRANSHEE_LPR} )

        },
        douPay(){

            let rskCommission = getRskCommission({
                RSK_SUMM: this.RSK_SUMM,
                MSK_SUMM: this.MSK_SUMM
            })

            let mskCommission = this.TRANSACTION_COMMISION - rskCommission

            let {
                AMOUNT_DZ,
                DOU_SUMM
            } = getSpacing({
                mskCommission,
                rskCommission,
                TRANSACTION_COMMISION: this.TRANSACTION_COMMISION,
                LOAN_PROGRAM: this.LOAN_PROGRAM,
                UF_LOAN_PROVIDER: this.UF_LOAN_PROVIDER
            })







            let {
                AMOUNT_DV,
                AMOUNT_OF_CONTRIBUTION,
                UF_BALANCE_ON_HAND_MSK,
            } = getDeposit({
                MSK_SUMM: this.MSK_SUMM,
                RSK_SUMM: this.RSK_SUMM,
                SUMMA: this.SUMMA,
                IS_RSK_DEPOSIT: this.IS_RSK_DEPOSIT,
                LOAN_PROGRAM: this.LOAN_PROGRAM,
                mskCommission,
                rskCommission,
            })

            this.AMOUNT_DZ = AMOUNT_DZ
            this.DOU_SUMM = DOU_SUMM
            this.AMOUNT_DV = AMOUNT_DV
            this.AMOUNT_OF_CONTRIBUTION = AMOUNT_OF_CONTRIBUTION


            this.UF_BALANCE_ON_HAND_MSK = UF_BALANCE_ON_HAND_MSK

        },

        openEditFields(){
            this.SET_ATTRIBUTE_EDIT_OBJ({
                MSK_SUMM: true,
                RSK_SUMM: true,
                LOAN_PROGRAM: true,
                SUMMA: true,
                TRANSACTION_COMMISION: true,
                AMOUNT_DZ: true,
                DOU_SUMM: true,
                AMOUNT_DV: true,
                AMOUNT_OF_CONTRIBUTION: true,
                IS_RSK_DEPOSIT: true,
                TRANSFER_OF_TRANCHE_1: true,
                TRANSFER_OF_TRANCHE_2: true,
                TRANSFER_OF_TRANCHE_3: true,
                //TRANSFER_OF_TRANCHE_4: true,
            })
        }
    }
}
</script>

<style scoped>

</style>