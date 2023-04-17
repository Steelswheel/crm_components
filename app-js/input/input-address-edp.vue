<template>

    <div class="dadata-options-wrapper">
            {{ value.dadata ? value.dadata.region_with_type : '-'}}
        <div v-if="isEdit">
            <addressDadata v-model="address" @dadata="onDadata"/>
        </div>
        <div v-else>
            {{address}}
        </div>

    </div>

</template>
<script>

import addressDadata from './input-address-dadata'

export default {
    inheritAttrs: false,
    name: "input-address-edp",
    components: {
        addressDadata,
    },
    props: {
        isEdit: {
            type: Boolean,
            default: true
        },
        className: {
            type: String,
            default: '',
        },
        value: {
            type: Object,
            default: () => ({address:'', dadata: {}}),
        },
        fieldDadata: String,
    },
    data() {
        return {
            address: this.value.address,
            dadata: this.value.dadata,
        }
    },
    watch: {
        value(){
            if (this.value !== this.value.address){
                this.address = this.value.address
            }
        },
        address(){
            this.setValue()
        },

    },
    mounted(){
        console.log(this.value,'!!!!!!!!!!!!!!')
    },
    methods: {
        setValue(){
            this.$emit('input', {address: this.address, dadata: this.dadata})
        },
        onDadata(dadata){

                this.dadata = dadata
                this.setValue()

        }


    }

}
</script>

<style scoped>

</style>
