<template>
    <div>

        <div v-if="isEdit">
            <el-checkbox
                    v-for="item in items"
                    @input="onCheckbox(item.id)"
                    :value="inValue.includes(item.id)"
                    :label="item.label"
                    :key="item.id"
                    border
                    size="small"
            />
        </div>
        <template v-else >
            <span v-for="item in items"
                  v-if="inValue.includes(item.id)"
                  class="mr-2"
                  :key="item.id"
            >{{item.label}}</span>
        </template>

    </div>
</template>

<script>
    import { Checkbox } from 'element-ui'
    import { cloneDeep, isEqual } from "lodash";

    export default {
        inheritAttrs: false,
        name: "input-checkbox",
        components: {
            'el-checkbox': Checkbox,
        },
        props: {
            value: {
                type: Array,
                default: () => ([])
            },
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
        mounted() {
            this.items = this.items.map(i => {
                i.id = parseInt(i.id)
                return i
            })
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
            onCheckbox(val){
                if (this.inValue.includes(val)){
                    this.inValue.splice(this.inValue.findIndex(i => i === val),1)
                }else{
                    this.inValue.push(val)
                }
            },
            focus() {

            },
            edit() {
                if (this.isClickEdit) {
                    this.$emit('edit')
                }
            }
        }
    }
</script>
