<template>
    <div>
        <el-date-picker
            v-if="isEdit"
            v-model="inValue"
            value-format="dd.MM.yyyy"
            format="dd.MM.yyyy"
            :picker-options="{
                firstDayOfWeek: 1
            }"
        />
        <div v-else>
            {{value}}
        </div>
    </div>
</template>

<script>
    import {DatePicker} from 'element-ui'
    export default {
        inheritAttrs: false,
        name: "input-datetime",
        components: {
            'el-date-picker': DatePicker
        },
        props:{
            value: [String,Number],
            disabled:{
                type: Boolean,
                default: false
            },
            isEdit: {
                type: Boolean,
                default: true
            }

        },
        data(){
            return {
                inValue: this.value,
            }
        },
        watch:{
            inValue(){
                let val = this.inValue === undefined
                    ? ''
                    : this.inValue
                this.$emit('input', val )
            },
            value(){
                if (this.value !== this.inValue ){
                    this.inValue = this.value
                }
            }
        },
    }
</script>