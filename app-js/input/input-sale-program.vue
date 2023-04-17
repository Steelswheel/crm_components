<template>
    <div class="input-individual-program__border">

        <div v-if="value['isAgent']">
            <div >
                Условия работы с агентом:
            </div>
        </div>
        <div v-else>
            <div >
              Условия работы с партнером <span v-if="isEdit" @click="isOpenEdit = !isOpenEdit" class="add-dotted">Ред</span>
            </div>
        </div>

        <table class="mt-1">
            <tr>
                <td class="text-right"><label for="" class="mr-2">КПК:</label></td>
                <td>
                    <input-enumeration
                        v-model="inValue.UF_EDP_KPK"
                        :options="attributes.UF_EDP_KPK.items"
                        :isEdit="isOpenEdit"
                    />
                </td>
            </tr>
            <tr>
                <td class="text-right"><label for="" class="mr-2">Тариф партнера:</label></td>
                <td>
                    <input-enumeration
                        v-model="inValue.PARTNER_STATUS"
                        :options="attributes.PARTNER_STATUS.items"
                        :isEdit="isOpenEdit"
                    />
                </td>
            </tr>
<!--            <tr>
                <td class="text-right"> <label for="" class="mr-2">Увеличенная сумма первого транша:</label></td>
                <td>
                    <input-enumeration
                        v-model="inValue.UF_COMMISSION_IN_THE_FIRST_TRANCHE"
                        :options="[{'label':'Да','id':'1'}, {'label':'Нет','id':'0'}]"
                        :isEdit="isOpenEdit"
                    />
                </td>
            </tr>-->
        </table>


    </div>
</template>

<script>
/*

* */
import inputEnumeration from './input-enumeration'
import {cloneDeep, isEqual} from 'lodash'
export default {
    inheritAttrs: false,
    name: "input-individual-program",
    components: {
        inputEnumeration,
    },
    props: {
        attributes: Object,
        value: Object,
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
        size: String
    },
    data(){
        return {
            inValue: cloneDeep(this.value),
            isOpenEdit: false,
            select: {

            }
        }
    },
    created() {


    },
    computed: {
        PARTNER_STATUS(){
            let value = this.attributes.PARTNER_STATUS.items.find(i => i.id === this.inValue.PARTNER_STATUS)
            if(value){
                return value.label
            }
            return 'НЕТ';
        },
        UF_EDP_KPK(){
            let value = this.attributes.UF_EDP_KPK.items.find(i => i.id === this.inValue.UF_EDP_KPK)
            if(value){
                return value.label
            }
            return 'НЕТ';
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input',this.inValue)
            }
        },
        value(){
            if (!isEqual(this.value,this.inValue)){
                this.inValue = cloneDeep(this.value)

            }
        },
    },
    methods: {

        reset(){
            this.isOpenEdit = false
        }
    }
}
</script>
<style>
.input-individual-program__border{
    border: 1px solid #acd3e8;
    padding: 7px;
}
</style>
