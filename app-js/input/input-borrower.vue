<template>
    <div class="b-block-title mb-3">
        <div class="b-block-title__name">
            {{attribute.label}}
        </div>

        <div
            v-if="isEdit"
            @click="isShowSelectBlock = !isShowSelectBlock"
            class="b-block-title__edit"
            :class="{'text-primary':isShowSelectBlock}"
            :style="!isShowSelectBlock || `display: block !important;`"
            role="button"
        >
            Редактировать <b-icon icon="pencil"/>
        </div>

        <div v-if="!isShowSelectBlock" class="b-block-title__content d-flex justify-content-between">

            view !!!!!!!!
        </div>
        <div v-else>
            <div class="row row-sm">
                <div class="col-md-4">
                    <wrap-input
                        v-model="inValue.LAST_NAME"
                        :attribute="{label:'Фамилия'}"
                        type="string"
                    />
                </div>
                <div class="col-md-4">
                    <wrap-input
                        v-model="inValue.NAME"
                        :attribute="{label:'Имя'}"
                        type="string"
                    />
                </div>
                <div class="col-md-4">
                    <wrap-input
                        v-model="inValue.SECOND_NAME"
                        :attribute="{label:'Отчество'}"
                        type="string"
                    />
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-md-6">
                    <wrap-input
                        v-model="inValue.UF_DATE_OF_BIRTH"
                        :attribute="{label:'Дата рождения'}"
                        type="date"
                    />
                </div>
            </div>
            <div class="row row-sm">
                <div class="col-md-6">
                    <wrap-input
                        v-model="inValue.PHONE"
                        :attribute="{label:'Телефон'}"
                        type="phone"
                    />
                </div>
                <div class="col-md-6">
                    <wrap-input
                        v-model="inValue.EMAIL"
                        :attribute="{label:'Почта'}"
                        type="phone"
                    />
                </div>
            </div>

        </div>


    </div>
</template>

<script>
import wrapInput from "./wrap-input";
import {cloneDeep, isEqual} from "lodash";

export default {
    name: "input-borrower",
    components: {
        wrapInput
    },
    props: {
        value: Object,
        attribute: {
            type: Object,
            default: () => ({})
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
        size: String
    },
    data(){
        return {
            inValue: cloneDeep(this.value),
            isShowSelectBlock: false,
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {

                this.$emit('input',this.inValue)
            }
        },
        value(){
            if (!isEqual(this.value,this.inValue)){
                this.inValue = cloneDeep(this.value)
            }
        },
    },
    methods: {


        reset(){
            this.isShowSelectBlock = false
        }
    }
}
</script>

<style scoped>

</style>