<template>
    <div>

        <el-checkbox v-if="isEdit" v-model="inValue" :label="yes" :border="border" size="mini"></el-checkbox>


        <span v-if="!isEdit && view === 'default'" >{{ value === '1' ? yes : 'Нет' }}</span>
        <input
            v-if="!isEdit && view === 'component'"
            ref="input"
            class="form-control1"
            disabled="disabled"
            v-model="inValue"
            type="checkbox"
        >
    </div>
</template>

<script>
import { Checkbox } from 'element-ui'
export default {
    inheritAttrs: false,
    name: "input-boolean",
    components: {
        'el-checkbox': Checkbox
    },
    props: {
        value: [String, Number],
        disabled: {
            type: Boolean,
            default: false
        },
        border: {
            type: Boolean,
            default: false
        },
        yes: {
            type: String,
            default: 'Да'
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
        view: {
            type: String,
            default: 'default'
        }
    },
    data(){
        return {
            inValue: this.value === "1"
        }
    },
    watch: {
        value(){
            if (this.inValue !== this.value){
                this.inValue = this.value === "1"
            }
        },
        inValue(){
            this.$emit('input', this.inValue ? "1" : "0")
        }

    },
    methods: {

    }
}
</script>
