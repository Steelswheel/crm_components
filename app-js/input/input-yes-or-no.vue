<template>
  <div v-if="isEdit && editable">
    <el-radio
        v-model="inValue"
        label="Y"
        border
        size="small"
    >
      {{yes}}
    </el-radio>
    <el-radio
        v-model="inValue"
        label="N"
        name="123"
        border
        size="small"
    >
      {{no}}
    </el-radio>
  </div>
  <div v-else>
    {{ getState }}
  </div>
</template>

<script>
import { Radio } from 'element-ui';

export default {
    components: {
        'el-radio': Radio
    },
    inheritAttrs: false,
    name: 'input-yes-or-no',
    props: {
        value: [String, Number, Array],
        alias: String,
        isEdit: {
            type: Boolean,
            default: true
        },
        yes: {
            type: String,
            default: 'Да'
        },
        no: {
            type: String,
            default: 'Нет'
        },
        editable: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
        view: {
            type: String,
            default: 'default'
        }
    },
    data() {
      return {
          inValue: this.value
      }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input', this.inValue)
            }
        }
    },
    computed: {
        getState() {
            let state = '';

            switch (this.value) {
                case 'Y':
                    state = this.yes;
                    break;
                case 'N':
                    state = this.no;
                    break;
                default:
                    state = 'Не выбрано';
                    break;
            }

            return state;
        }
    }
}
</script>
