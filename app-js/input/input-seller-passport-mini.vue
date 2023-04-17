<template>
    <div class="mb-4">


        <div
            v-for="(item,key) in inValue"
            :key="key"
            class="bf-table"
        >
            <div class="bf-table-label">
                Продавец {{key + 1}} <br>
                <small v-if="isEdit" @click="remove(key)" class="cursor-remove">удалить</small>
            </div>
            <div class="bf-table-input">


                <input-group
                    v-if="isEdit"
                    :value="item"
                    @input="onInput(arguments[0],key)"
                    :attribute="attribute"
                    :attributes="attribute.fields"
                />
                <div v-else>

                    <wrap-input
                        type="file"
                        :value="item.UF_SKAN_d"
                        methodUpdate="no"
                    />
                </div>
            </div>
        </div>


        <el-button v-if="isEdit" class="mt-2" type="primary" plain @click="add">Добавить продавца</el-button>

    </div>

</template>

<script>


import { Button } from 'element-ui'
import { cloneDeep, isEqual } from 'lodash'
import inputGroup from './input-group'
import wrapInput from './wrap-input'
export default {
    inheritAttrs: false,
    name: "input-seller-passport-mini",
    components: {
        'el-button': Button,
        inputGroup,
        wrapInput,
    },
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
    watch: {
        /*inValue: {
            deep: true,
            handler() {

                console.log(1);
                this.$emit('input',this.deConvertValue())
            }
        },*/
        value(){

            let value = this.deConvertValue()
            if (!isEqual(this.value,value)){

                this.inValue = this.convertValue()

            }
        },
    },

    mounted(){
        /*if (this.inValue.length === 0){
            this.add()
        }*/
    },
    methods:{
        convertValue(){
            let valClone = cloneDeep(this.value)
            return valClone.map(i => {

                return i
            })
        },
        deConvertValue(){
            let inValClone = cloneDeep(this.inValue)
            return inValClone.map(i => {

                return i
            })
        },

        remove(key){
            this.inValue.splice(key,1)
            this.$emit('input',this.deConvertValue())
        },
        onInput(val,key){


            this.$set(this.inValue, key, val)

            this.$emit('input',this.deConvertValue())
        },
        add(){
            this.inValue.push({

            })
        },

    }
}
</script>

<style scoped>

</style>