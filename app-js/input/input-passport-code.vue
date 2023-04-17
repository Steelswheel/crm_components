<template>
    <div>
        <fmsCode v-if="isShow" v-model="dadata"/>
    </div>
</template>

<script>
import fmsCode from '@app/input/input-dadata-fms'
// import inputGroupV from '@app/input/input-group-v'
export default {
    inheritAttrs: false,
    components: {
        fmsCode
    },
    name: "input-passport-code",
    props: {
        alias: String,
        isEdit: {
            type: Boolean,
            default: true
        },
        field_kem_vidan: String,
    },
    data(){
        return {
            dadata: undefined,
            isShow: false
        }
    },
    watch:{
        value(){
            this.dadata = { value: this.value }
        },
        dadata(){
            // this.value = this.dadata.value

            this.value = this.dadata.value
            if (this.dadata.data){
                this.value = this.dadata.data.code
                this.kem_vidan = this.dadata.data.name
            }
        }
    },
    mounted() {
        this.dadata = { value: this.value }
        this.isShow = true
    },
    computed:{
        value: {
            get: function () {return this.$store.getters['form/GET_VALUE'](this.alias)},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.alias, value})}
        },
        kem_vidan: {
            get: function () {return this.$store.getters['form/GET_VALUE'](this.field_kem_vidan)},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.field_kem_vidan, value})}
        },
    },
}
</script>
