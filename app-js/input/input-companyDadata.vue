<template>
    <div>
        <input-company class="company-list__item__company"  v-model="inValue.company"/>
    </div>
</template>

<script>

import inputCompany from './input-company'
import { cloneDeep, isEqual} from 'lodash'

export default {
    inheritAttrs: false,
    name: "input-companyDadata",
    components: {
        inputCompany
    },
    props: {

        value: {
            type: Object,
            default: () => ({ID:'n0', company: undefined})
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
    },
    data(){
        return {
            inValue: cloneDeep(this.value),

        }
    },
    mounted(){

    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input', this.inValue)
            }
        },
        value(){
            if (!isEqual(this.value, this.inValue)){
                this.inValue = cloneDeep(this.value)
            }
        },
    },

}
</script>

<style scoped>

</style>
