<template>
    <el-checkbox
        v-model="inValue"
        :disabled="!enabled"
    >
      {{text ? text : attribute.label}}
    </el-checkbox>
</template>

<script>
    import { Checkbox } from 'element-ui'
    import { isEqual } from 'lodash';

    export default {
        inheritAttrs: false,
        name: 'input-checkbox-edit',
        components: {
            'el-checkbox': Checkbox,
        },
        props: {
            enabled: {
                type: Boolean,
                default: true
            },
            text: String,
            value: {},
            attribute: {
                type: Object,
                default: () => ({})
            }
        },
        data() {
            return {
                inValue: (this.value === '1' || this.value === true)
            }
        },
        watch: {
            inValue: {
                deep: true,
                handler(value) {
                    this.$emit('input', value);
                }
            },
            value() {
                if (!isEqual(this.value, this.inValue)) {
                    this.inValue = (this.value === '1' || this.value === true);
                }
            },
        }
    }
</script>
