<template>
    <div v-if="(!dealId || (STAGE_ID && stages.includes(STAGE_ID)))">
        <el-select
            v-if="isEdit"
            :class="size ? `form-control-${size}` : ''"
            :disabled="disabled"
            v-model="inValue"
            filterable
            clearable
        >
            <el-option
                v-for="item in items"
                :value="item.id"
                :key="item.id"
                :label="item.label"
                :disabled="item.disabled"
            >
                <div class="el-select-dropdown__item__br" v-html="item.label.replace(/\n/g, '<br>')"></div>
            </el-option>
        </el-select>
        <span v-else @click="edit" :class="{'click-edit': isClickEdit}">
            <template v-if="items && items.find(i => i.id === value)">
                <template v-if="items.find(i => i.id === value).url">
                    <a :href="items.find(i => i.id === value).url">{{items.find(i => i.id === value).label}}</a>
                </template>
                <template v-else>
                    <div v-html="items.find(i => i.id === value).label.replace(/\n/g, '<br>')"></div>
                </template>
            </template>
            <template v-else>
                {{value}}
            </template>
        </span>
    </div>
    <div v-else>
        <span v-if="value === '750'">
            Ежемесячно
        </span>
        <span v-if="value === '751'">
            В конце срока
        </span>
        <span v-if="value === '763'">
            Не выплачиваются
        </span>
    </div>
</template>

<script>
import { Select, Option } from 'element-ui';
import { mapGetters } from 'vuex';
import { getForm } from '@app/../store/helpStore';

export default {
    inheritAttrs: false,
    name: 'input-interest-payment',
    components: {
        'el-select': Select,
        'el-option': Option,
    },
    props: {
        dealId: {},
        value: [String, Number],
        attribute: {
            type: Object,
            default: () => ({})
        },
        options: Array,
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
    watch: {
        inValue(value) {
            this.$emit('input', value);
        },
        UF_CONTRACT_PERIOD(value) {
            if (!this.dealId || !this.getOtherStages) {
                let newValue = this.GET_PROGRAMS[this.getProgram]['VALUES']['INTEREST_PAYMENT'][value];

                if (newValue) {
                    this.$store.commit('form/SET_VALUE', {attribute: 'UF_INTEREST_PAYMENT', value: newValue});
                    this.inValue = newValue;
                }
            }
        }
    },
    computed: {
        ...mapGetters('form', [
            'GET_PROGRAMS',
            'GET_STAGES_HIS'
        ]),
        getProgram() {
          return this.$store.getters['form/GET_VALUE']('UF_SAVINGS_PROGRAM');
        },
        getOtherStages() {
            let hisStages = Object.keys(this.GET_STAGES_HIS);

            if (hisStages.length > 0) {
                let arr = hisStages.filter(stage => {
                    if (!this.stages.includes(stage)) {
                        return stage;
                    }
                });

                return arr.length > 0;
            }

            return false;
        },
        UF_CONTRACT_PERIOD: getForm('UF_CONTRACT_PERIOD'),
        STAGE_ID: getForm('STAGE_ID')
    },
    data() {
        return {
            stages: ['C14:NEW', 'C14:ENTRY_INTO_KPK', 'C14:SIGNING_DS'],
            items: this.options ? this.options : this.attribute.items,
            inValue: this.value
        }
    },
    methods: {
        edit() {
            if (this.isClickEdit) {
                this.$emit('edit');
            }
        }
    }
}
</script>
