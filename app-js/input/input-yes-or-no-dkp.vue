<template>
  <div v-if="isEdit && editable && !['Y', 'N'].includes(value) && GET_IS_ECONOMIST">
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
    <small v-if="UF_DKP_CHANGE_DATE_TIME">Выбрано: {{ UF_DKP_CHANGE_DATE_TIME }}</small>
  </div>
  <div v-else>
    {{ getState }} <small class="ml-2" v-if="UF_DKP_CHANGE_DATE_TIME">Выбрано: {{ UF_DKP_CHANGE_DATE_TIME }}</small>
  </div>
</template>

<script>
import { Radio } from 'element-ui';
import { BX_POST } from '@app/API';
import { getForm } from '../../store/helpStore';
import { mapGetters } from 'vuex';

export default {
    components: {
        'el-radio': Radio
    },
    inheritAttrs: false,
    name: 'input-yes-or-no-dkp',
    props: {
        dealId: {},
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
                this.$emit('input', this.inValue);

                BX_POST('vaganov:reports.all', 'saleDkpDate', {
                    dealId: this.dealId
                }).then(response => {
                    console.log(response);
                }).catch(error => {
                    console.log(error);
                });
            }
        }
    },
    computed: {
        ...mapGetters('form', [
            'GET_IS_ECONOMIST'
        ]),
        UF_DKP_CHANGE_DATE_TIME: getForm('UF_DKP_CHANGE_DATE_TIME'),
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
