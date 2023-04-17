<template>
    <div>
        <div v-if="isEdit">
            <el-radio
                    v-for="item in items"
                    v-model="inValue"
                    :label="item.id"
                    :key="item.id"
                    border
                    size="small"
            >{{item.label}}</el-radio>
        </div>
        <span v-else >{{items.find(i => i.id === value) ? items.find(i => i.id === value).label : ''}}</span>

    </div>
</template>

<script>
    import { Radio } from 'element-ui'
    import {cloneDeep, isEqual} from "lodash";

    export default {
        inheritAttrs: false,
        name: "input-radio",
        components: {
            'el-radio': Radio
        },

        props: {
            value: {},
            options: Array,
            disabled: {
                type: Boolean,
                default: false
            },
            attribute: {
                type: Object,
                default: () => ({})
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
                items: this.options ? this.options : this.attribute.items
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
            focus() {
                this.$nextTick(() => {
                    this.$refs.input.focus()
                })
            },
            edit() {
                if (this.isClickEdit) {
                    this.$emit('edit')
                }
            }
        }
    }
</script>
