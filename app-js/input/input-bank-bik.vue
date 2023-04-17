<template>
    <div>
        <input type="text" v-model="value" class="form-control">

    </div>
</template>

<script>
import API from '@app/API'
export default {
    inheritAttrs: false,
    components: {

    },
    name: "input-passport-code",
    props: {
        alias: String,
        isEdit: {
            type: Boolean,
            default: true
        },
        field_bank_name: String,
    },
    data(){
        return {

        }
    },
    watch:{
        value(){

            if (this.value.length > 8){
                console.log(1);
                API.dadata('bank',this.value)
                    .then(r => {
                        if (r['suggestions'] && r['suggestions'].length > 0){
                            this.BANK_NAME = r['suggestions'][0].value;
                        }
                    })
            }
        }
    },
    mounted() {

    },
    computed:{
        value: {
            get: function () {return this.$store.getters['form/GET_VALUE'](this.alias)},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.alias, value})}
        },
        BANK_NAME: {
            get: function () {return this.$store.getters['form/GET_VALUE'](this.field_bank_name)},
            set: function(value){this.$store.commit('form/SET_VALUE', {attribute: this.field_bank_name, value})}
        },
    },
    methods: {

    },
}
</script>
