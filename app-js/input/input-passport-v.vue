<template>
    <div>

        <inputPassportApi v-if="isEdit" @passport="onPassportApi" class="mb-2" />

        <inputGroupV v-if="isEdit" :alias="this.alias" />

        <div v-else>
            <small>Номер:</small> {{ NUMBER || '-'}}; <small>Серия:</small> {{SER || '-'}}
            <br>
            <small>Код:</small> {{KOD || '-'}}; <small>Дата выдачи:</small> {{DATE || '-'}}
            <br>
            <small>Кем выдан:</small> {{KEM_VIDAN || '-'}}
            <br>
            <small>Место рождения:</small> {{BIRTH_PLACE || '-'}}
            <br>
            <small>Дата рождения:</small> {{ BIRTHDATE || '-' }}
            <br>
            <small>Пол:</small> {{ GENDER === 'm' ? 'Муж.' : GENDER === 'w' ? 'Жен.': '-' }}
            <br>
            <wrap-input-v :alias="fields['SCAN']" />
        </div>
    </div>
</template>

<script>

import wrapInputV from '@app/input/wrap-input-v'
import inputGroupV from '@app/input/input-group-v'
import inputPassportApi from '@app/input/input-passport-api'
export default {
    inheritAttrs: false,
    components: {
        inputGroupV,
        wrapInputV,
        inputPassportApi,
    },
    name: "input-passport",
    props: {
        alias: String,
        isEdit: {
            type: Boolean,
            default: true
        },
        fields: Object,
    },
    computed: {
        GENDER:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['GENDER'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['GENDER'], value})}
        },
        NUMBER:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['NUMBER'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['NUMBER'], value})}
        },
        SER:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['SER'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['SER'], value})}
        },
        KOD:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['KOD'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['KOD'], value})}
        },
        DATE:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['DATE'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['DATE'], value})}
        },
        KEM_VIDAN:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['KEM_VIDAN'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['KEM_VIDAN'], value})}
        },
        BIRTH_PLACE:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['BIRTH_PLACE'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['BIRTH_PLACE'], value})}
        },
        BIRTHDATE:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['BIRTHDATE'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['BIRTHDATE'], value})}
        },
        SCAN:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['SCAN'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['SCAN'], value})}
        },
        NAME:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['NAME'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['NAME'], value})}
        },
        LAST_NAME:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['LAST_NAME'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['LAST_NAME'], value})}
        },
        SECOND_NAME:{
            get: function () {return this.$store.getters['form/GET_VALUE'](this.fields['SECOND_NAME'])},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.fields['SECOND_NAME'], value})}
        },
    },
    methods: {

        onPassportApi(data){
            console.log(data);
            this.GENDER = data.GENDER
            this.NUMBER = data.NUMBER
            this.SER = data.SER
            this.KOD = data.KOD
            this.DATE = data.DATE
            this.KEM_VIDAN = data.KEM_VIDAN
            this.NAME = data.NAME
            this.LAST_NAME = data.LAST_NAME
            this.SECOND_NAME = data.SECOND_NAME
            this.BIRTH_PLACE = data.BIRTH_PLACE
            this.BIRTHDATE = data.BIRTH_DATE
        }
    }
}
</script>
