<template>
    <div :class="attribute.classNameGroup || ''" :style="attribute.style || ''">


        <wrap-input

            v-for="(item, alias) in attribute.fields" :key="item.field"
            v-model="inValue[alias]"
            :isEdit="isEdit"
            :alias="alias"
            :attribute="attributes[alias]"
            :attributes="attributes"
            :size="attributes[alias].size"
            :type="attributes[alias].type"
            :styleGroup="attributes[alias].styleGroup"
            :methodUpdate="attributes[alias].methodUpdate"
        />

    </div>
</template>

<script>
import wrapInput from './wrap-input'
import {cloneDeep, isEqual} from "lodash";

export default {
    inheritAttrs: false,
    name: "input-group",
    components: {
        wrapInput
    },
    props: {
        value: {},
        attribute: Object,
        attributes: Object,
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
        }
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
    methods: {

        edit(){
            if (this.isClickEdit){
                this.$emit('edit')
            }
        }
    }
}
</script>
