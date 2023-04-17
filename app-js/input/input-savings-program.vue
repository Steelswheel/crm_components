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
                <div v-html="UF_SAVINGS_PROGRAM_NAME ? UF_SAVINGS_PROGRAM_NAME : items.find(i => i.id === value).label.replace(/\n/g, '<br>')"></div>
            </template>
            <template v-else>
                {{ UF_SAVINGS_PROGRAM_NAME }}
            </template>
        </span>
    </div>
    <div v-else>
        {{ UF_SAVINGS_PROGRAM_NAME }}
    </div>
</template>

<script>
import { Select, Option } from 'element-ui';
import { mapGetters } from 'vuex';
import { getForm } from '@app/../store/helpStore';

export default {
    inheritAttrs: false,
    name: 'input-savings-program',
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

            let programName = this.items.find(i => i.id === value)?.label;

            if (programName) {
                this.$store.commit('form/SET_VALUE', {attribute: 'UF_SAVINGS_PROGRAM_NAME', value: programName});
            }
        }
    },
    computed: {
        ...mapGetters('form', [
            'GET_STAGES_HIS'
        ]),
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
        STAGE_ID: getForm('STAGE_ID'),
        UF_SAVINGS_PROGRAM_NAME: getForm('UF_SAVINGS_PROGRAM_NAME')
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
