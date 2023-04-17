<template>
    <div>
        <div v-if="isEdit">
            <div v-for="(item, key) in inValue" v-if="!deletedIds.includes(item.ID)" :key="key">
                <div class="d-flex mb-2">
                    <div class="flex-grow-1 ">
                        <input v-model="item.VALUE" type="text" class="form-control" :disabled="disabled" v-phone maxlength="15">
                    </div>
                    <div v-if="!disabled">
                        <div @click="remove(item.ID)" class="btn btn-sm ">
                            <BIconX/>
                        </div>
                    </div>
                </div>
            </div>
            <span @click="add" v-if="!disabled" class="add-dotted">Добавить</span>
        </div>
        <div v-else >
            <div v-if="!disabled">
                <div  v-for="item in inValue" v-if="item.VALUE !== ''"  :key="item.ID">
                    <span @click="call(item)" class="click-edit" v-phone>{{item.VALUE}}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { getForm } from '@app/../store/helpStore'
import {cloneDeep, isEqual} from 'lodash'
import {Message} from "element-ui";
export default {
    inheritAttrs: false,
    name: "input-phone",
    props: {
        value: {
            type: Array,
            default: () => ([])
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
        value_type: String
    },
    data(){
        return {
            inValue: cloneDeep(this.value),
            typePhone: [
                {value: 'WORK', attribute: 'Рабочий'},
                {value: 'MOBILE', attribute: 'Мобильный'},
                {value: 'FAX', attribute: 'Факс'},
                {value: 'HOME', attribute: 'Домашний'},
                {value: 'PAGER', attribute: 'Пейджер'},
                {value: 'MAILING', attribute: 'Для рассылок'},
                {value: 'OTHER', attribute: 'Другой'},
            ],
            addObject: { 'VALUE_TYPE': this.value_type ? this.value_type : 'WORK', 'VALUE': '', 'TYPE_ID': "PHONE" },
            deletedIds: []
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.inValue.forEach(item => {
                    item.VALUE = item.VALUE.replace(/[()-]/gm, '');
                });

                this.$emit('input', this.inValue);
            }
        },
        value() {
            if (!isEqual(this.value,this.inValue)) {
                this.inValue = cloneDeep(this.value);
            }
        },
    },
    computed:{
        contactId: getForm('CONTACT_ID')
    },
    methods: {
        add() {
            let addObject = { ...this.addObject };
            addObject.ID = `n`;
            this.inValue.push(addObject);
        },

        edit() {
            if (this.isClickEdit) {
                this.$emit('edit');
            }
        },
        remove(id) {
            this.deletedIds.push(id);
            this.inValue.find(i => i.ID === id).VALUE = '';
        },
        call(item) {
            if (item.ID !== 'n') {
                let number = item.VALUE.replace(/\D+/g,"")
                top.BXIM.phoneTo(number, {"ENTITY_TYPE_NAME":"CONTACT","ENTITY_ID":this.contactId,"AUTO_FOLD":true})
            } else {
                Message({
                    showClose: true,
                    dangerouslyUseHTMLString: true,
                    message: 'Номер не сохранен',
                    type: 'error',
                    duration: 5000
                });
            }
        }
    }
}
</script>
