<template>
    <div>

        <inputPassportApi v-if="isEdit" @passport="onPassportApi" class="mb-2" />
        <div v-if="isEdit">
            <div class="mb-2 d-flex">
                <wrapInput type="string" :attribute="{label: 'Фамилия', className: 'mr-1 bf-mini-label', }" v-model="inValue.LAST_NAME" />
                <wrapInput type="string" :attribute="{label: 'Имя', className: 'mr-1 bf-mini-label'}" v-model="inValue.NAME" />
                <wrapInput type="string" :attribute="{label: 'Отчество', className: 'mr-1 bf-mini-label'}" v-model="inValue.SECOND_NAME" />
            </div>
        
            <div class="mb-2 d-flex">
                <wrapInput type="date" :attribute="{label: 'Дата рождения', className: 'mr-1 bf-mini-label',}" v-model="inValue.DATE_OF_BIRTH" />
                <wrapInput type="enumeration" :attribute="{label: 'Пол', className: 'bf-mini-label', settings: genderSettings }" v-model="inValue.GENDER" />
            </div>
            <div class="mb-2 d-flex">
                <wrapInput type="string" :attribute="{label: 'Серия', className: 'mr-1 bf-mini-label'}" v-model="inValue.SER" />
                <wrapInput type="string" :attribute="{label: 'Номер', className: 'mr-1 bf-mini-label'}" v-model="inValue.NUMBER" />
        
            </div>
            <div class="mb-2 d-flex">
                <wrapInput type="string" :attribute="{label: 'Kод подразделения', className: 'mr-1 bf-mini-label'}" v-model="inValue.KOD" />
                <wrapInput type="date" :attribute="{label: 'дата выдачи', className: 'mr-1 bf-mini-label'}" v-model="inValue.DATE" />
            </div>
            <div class="mb-2 ">
                <wrapInput type="string" :attribute="{label: 'Кем выдан', className: 'mr-1 bf-mini-label'}" v-model="inValue.KEM_VIDAN" />
            </div>
            <div class="mb-2 ">
                <wrapInput type="address" :attribute="{label: 'Место рождения', className: 'mr-1 bf-mini-label'}" v-model="inValue.BIRTH_PLACE" />
            </div>
            <div class="mb-2 ">
                <wrapInput type="address" :attribute="{label: 'Место прописки', className: 'mr-1 bf-mini-label'}" v-model="inValue.ADDRESS" />
            </div>
            <div class="mb-2">
                <small>Скан паспорта (первая страница регистрация)</small>
                <wrapInput type="file" :attribute="{label: '', className: 'mr-1 bf-mini-label'}" v-model="inValue.SCAN_PASSPORT" />
            </div>
        </div>
        <div v-else>
            <small>ФИО</small> {{inValue.LAST_NAME}} {{inValue.NAME}} {{inValue.SECOND_NAME}}
            <br>
            <small>Дата рождения:</small> {{ inValue.DATE_OF_BIRTH || '-'}};
            <small>Пол:</small> {{ inValue.GENDER === 'm' ? 'Муж.' : inValue.GENDER === 'w' ? 'Жен.': '-' }}
            <br>
            <small>Серия:</small> {{ inValue.SER || '-'}}; <small>Номер:</small> {{inValue.NUMBER || '-'}}
            <br>
            <small>Код:</small> {{inValue.KOD || '-'}}; <small>Дата выдачи:</small> {{inValue.DATE || '-'}}
            <br>
            <small>Кем выдан:</small> {{inValue.KEM_VIDAN || '-'}}
            <br>
            <small>Место рождения:</small> {{inValue.BIRTH_PLACE || '-'}}
            <br>
            <small>Место прописки:</small> {{inValue.ADDRESS || '-'}}
            <br>

            <small>Скан паспорта (первая страница регистрация)</small>
            <wrapInput type="file" methodUpdate="no" :attribute="{label: '', className: 'mr-1 bf-mini-label'}" v-model="inValue.SCAN_PASSPORT" />

        </div>
               

    </div>
</template>

<script>

import inputPassportApi from '@app/input/input-passport-api'
import wrapInput from '@app/input/wrap-input'
import {clone} from 'lodash'
export default {
    inheritAttrs: false,
    components: {
        wrapInput,
        inputPassportApi,
    },
    name: "input-passport",
    props: {
        alias: String,
        isEdit: {
            type: Boolean,
            default: true
        },
        value: Object,
    },
    data(){
        return {
            inValue: clone(this.value),
            genderSettings:{
                options: [
                    {id: 'm', label:'Муж.'},
                    {id: 'w', label:'Жен.'},
                ]
            }
        }
    },

    watch: {
        value(){
            if (this.value !== this.inValue){
                this.inValue = this.value
            }
        },
        inValue: {
            deep: true,
            handler() {
                this.$emit('input',this.inValue)
            }
        },

    },
    computed: {
        methodUpdate(){
            return this.isEdit ? 'follow' : 'no'
        }
    },
    methods: {

        onPassportApi(data){
            console.log(data);
            this.inValue.GENDER = data.GENDER

            this.inValue.NUMBER = data.NUMBER
            this.inValue.SER = data.SER
            this.inValue.KOD = data.KOD
            this.inValue.DATE = data.DATE
            this.inValue.KEM_VIDAN = data.KEM_VIDAN
            this.inValue.NAME = data.NAME
            this.inValue.LAST_NAME = data.LAST_NAME
            this.inValue.SECOND_NAME = data.SECOND_NAME
            this.inValue.BIRTH_PLACE = data.BIRTH_PLACE
            // this.inValue.DATE_OF_BIRTH = data.BIRTH_DATE
        }
    }
}
</script>
