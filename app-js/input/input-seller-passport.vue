<template>
    <div class="mb-4">
        <wrap-input-v
            v-if="LOAN_PROGRAM === '2' || LOAN_PROGRAM === '4' || UF_THIRD_PARTY_CONTRIBUTION_SELLER === '1'"
            alias="UF_THIRD_PARTY_CONTRIBUTION_SELLER"
        />
        <wrap-input-v
            v-if="UF_THIRD_PARTY_CONTRIBUTION_SELLER === '1'"
            v-for="atrAlias in Object.keys(GET_ATTRIBUTES['stage_1_guarantor']['fields'])"
            :alias="atrAlias"
            :key="atrAlias"
        />
        <div
            v-for="(item,key) in inValue"
            :key="key"
            class="bf-table"
        >
            <div class="bf-table-label">
                Продавец {{key + 1 + (UF_THIRD_PARTY_CONTRIBUTION_SELLER === '1' ? 1 : 0) }} <br>
                <small v-if="isEdit" @click="remove(key)" class="cursor-remove">
                  удалить
                </small>
            </div>
            <div class="bf-table-input">
                <inputPassportApi v-if="isEdit" @passport="onPassportApi(arguments[0], key)" class="mb-2" />
                <input-group
                    v-if="isEdit"
                    :value="item"
                    @input="onInput(arguments[0],key)"
                    :attribute="attribute"
                    :attributes="attribute.fields"
                />
                <div v-else>
                    {{item.UF_FIO}}<br>
                    <small>Номер:</small> {{ item.UF_NUMBER || '-'}}; <small>Серия:</small> {{item.UF_SER || '-'}}
                    <br>
                    <small>Код:</small> {{item.UF_KOD.value || '-'}}; <small>Дата выдачи:</small> {{item.UF_DATE || '-'}}
                    <br>
                    <small>Кем выдан:</small> {{item.UF_KEM_VIDAN || '-'}}
                    <br>
                    <small>Место рождения:</small> {{item.UF_BIRTH_SELLER || '-'}}
                    <br>
                    <small>Место прописки:</small> {{item.UF_ADDRESS || '-'}}
                    <br>
                    <small>Пол:</small> {{ item.UF_GENDER === 'm' ? 'Муж.' : item.UF_GENDER === 'w' ? 'Жен.': '-' }}
                    <br>
                    <small>Дата рождения:</small> {{ item.UF_SELLER_BIRTHDATE || '-' }}
                    <br>
                    <wrap-input
                        type="file"
                        :value="item.UF_SKAN"
                        methodUpdate="no"
                    />
                </div>
            </div>
        </div>
        <el-button v-if="isEdit" class="mt-2" type="primary" plain @click="add">
          Добавить продавца
        </el-button>
    </div>
</template>

<script>
import { getForm } from '@app/../store/helpStore';
import wrapInputV from '@app/input/wrap-input-v';
import inputPassportApi from '@app/input/input-passport-api';
import { Button } from 'element-ui';
import { cloneDeep, isEqual } from 'lodash';
import inputGroup from './input-group';
import wrapInput from './wrap-input';
import { mapGetters } from 'vuex';

export default {
    inheritAttrs: false,
    name: 'input-seller-passport',
    components: {
        'el-button': Button,
        inputGroup,
        wrapInput,
        inputPassportApi,
        wrapInputV
    },
    props: {
        value: {
            type: Array,
            default: () => ([{
              UF_NUMBER: '',
              UF_SER: '',
              UF_KOD: '',
              UF_DATE: '',
              UF_KEM_VIDAN: '',
              UF_FIO: '',
              UF_BIRTH_SELLER: '',
              UF_ADDRESS: '',
              UF_GENDER: '',
              UF_SELLER_BIRTHDATE: ''
            }])
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        alias: String,
        attribute: {},
        attributes: {},
        label: String,
    },
    data() {
        return {
            inValue: this.convertValue()
        }
    },
    computed:{
        ...mapGetters('form', [
            "GET_ATTRIBUTES",
        ]),
        UF_THIRD_PARTY_CONTRIBUTION_SELLER: getForm('UF_THIRD_PARTY_CONTRIBUTION_SELLER'),
        LOAN_PROGRAM: getForm('LOAN_PROGRAM')
    },
    watch: {
        value() {
            let value = this.deConvertValue();

            if (!isEqual(this.value,value)) {
                this.inValue = this.convertValue()
            }
        },
    },

    mounted() {
        if (this.inValue.length === 0) {
            this.inValue.push({
                UF_NUMBER: '',
                UF_SER: '',
                UF_KOD: { value: '' },
                UF_DATE: '',
                UF_KEM_VIDAN: '',
                UF_FIO: '',
                UF_BIRTH_SELLER: '',
                UF_ADDRESS: '',
                UF_GENDER: '',
                UF_SELLER_BIRTHDATE: ''
            });
        }
    },
    methods: {
        convertValue() {
            let valClone = cloneDeep(this.value);

            return valClone.map(i => {
                i.UF_KOD = { value: i.UF_KOD };

                return i;
            });
        },
        deConvertValue() {
            let inValClone = cloneDeep(this.inValue);

            return inValClone.map(i => {
                i.UF_KOD = i.UF_KOD ? i.UF_KOD.value : '';

                return i;
            })
        },
        remove(key) {
            this.inValue.splice(key, 1);

            this.$emit('input', this.deConvertValue());
        },
        onInput(val, key) {
            if (val.UF_KOD && val.UF_KOD.data) {
                val.UF_KEM_VIDAN = val.UF_KOD.data.name;
                val.UF_KOD = { value:val.UF_KOD.value };
            }

            this.$set(this.inValue, key, val);
            this.$emit('input',this.deConvertValue());
        },
        add() {
            this.inValue.push({
                UF_NUMBER: '',
                UF_SER: '',
                UF_KOD: { value: '' },
                UF_DATE: '',
                UF_KEM_VIDAN: '',
                UF_FIO: '',
                UF_BIRTH_SELLER: '',
                UF_ADDRESS: '',
                UF_GENDER: '',
                UF_SELLER_BIRTHDATE: ''
            });
        },
        onPassportApi(data, key) {
            let inValueFormat = {
                UF_NUMBER: data.NUMBER,
                UF_SER: data.SER,
                UF_KOD: { value: data.KOD},
                UF_DATE: data.DATE,
                UF_KEM_VIDAN: data.KEM_VIDAN,
                UF_FIO: data.LAST_NAME + ' ' + data.NAME + ' ' + data.SECOND_NAME,
                UF_BIRTH_SELLER: data.BIRTH_PLACE,
                UF_GENDER: data.GENDER,
                UF_SELLER_BIRTHDATE: data.BIRTH_DATE
            };

            this.$set(this.inValue, key, inValueFormat);
        }
    }
}
</script>

<style scoped>

</style>