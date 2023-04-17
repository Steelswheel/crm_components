<template>
    <div>

        <div v-if="isValidate">
            <div>{{requiredTextError}}</div>
            <div v-for="(field, key) in isValidate" class="text-danger" :key="key">
                {{GET_ATTRIBUTES[field].label}}
            </div>
        </div>
        <div v-else>

                <div v-for="(name, typeDoc) in documents" :key="typeDoc">
                    <a :href="`/b/d/d.php?id=${GET_DEFAULT_VALUE.DEAL_ID}&document=${typeDoc}`">
                        {{ name }} <i class="el-icon-download"/></a>
                </div>

        </div>






    </div>
</template>

<script>

import { isEmpty } from "lodash";
import { mapGetters } from "vuex";
export default {
    name: "send-email-send",
    props: {

        requiredTextError: {
            type: String,
            default: 'Для формирования документов не заполнены обязательные поля:'
        },
        btnName: {
            type: String,
            default: 'Отправить'
        },
        documents: {
            type: Object,
            default: () => ({})
        },
        required: {
            type: Array,
            default: () => ([])
        },
    },
    data(){
        return {

        }
    },
    computed: {
        ...mapGetters('form',[
            'GET_DEFAULT_VALUE',
            'GET_ATTRIBUTES',
            'IS_ADMIN',
        ]),

        isValidate(){
            let fieldError = []
            this.required.forEach(i => {
                if (isEmpty(this.GET_DEFAULT_VALUE[i])){
                    fieldError.push(i)
                }
            })
            return fieldError.length > 0
                ? fieldError
                : false
        },

    },
    methods: {

    }
}
</script>

<style scoped>

</style>