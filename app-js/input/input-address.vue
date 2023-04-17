<template>

    <div class="dadata-options-wrapper">
        <small v-if="isRegion">
            {{dadataData ? dadataData.region_with_type : '-'}}
        </small>
        <div   v-if="isEdit">
            <addressDadata v-model="inValue" @dadata="onDadata"/>
        </div>
        <div v-else>
            {{inValue}}
        </div>

    </div>

</template>
<script>

import addressDadata from './input-address-dadata'

export default {
    inheritAttrs: false,
    name: "input-address",
    components: {
        addressDadata,
    },
    props: {
        isEdit: {
            type: Boolean,
            default: true
        },
        isRegion: {
            type: Boolean,
            default: false,
        },
        className: {
            type: String,
            default: '',
        },
        value: String,
        fieldDadata: String,
    },
    data() {
        return {
            inValue: this.value,
        }
    },
    computed: {
        dadataData: {
            get: function () {
                let value = this.$store.getters['form/GET_VALUE'](this.fieldDadata)
                return value ? JSON.parse(value) : null
            },
            set: function(value){
                let newValue = value
                    ? JSON.stringify(value)
                    : ''
                this.$store.commit('form/SET_VALUE', {
                    attribute: this.fieldDadata,
                    value: newValue
                })
            }
        },
    },
    watch: {
        value(){
            if (this.value !== this.inValue){
                this.inValue = this.value
            }
        },
        inValue(){
            this.$emit('input', this.inValue)
        },

    },
    methods: {
        onDadata(dadata){
            if (this.isRegion && this.fieldDadata){
                this.dadataData = dadata
            }
        }


    }

}
</script>

<style scoped>

</style>
