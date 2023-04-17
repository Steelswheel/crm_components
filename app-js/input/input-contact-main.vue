<template>
    <div>

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

            <template v-if="inValue.ID">
                <div v-if="!isShowSelectBlock" class="b-block-title__content d-flex justify-content-between">
                    <div class="">
                        <a :href="`/crm/contact/details/${inValue.ID}/`">
                            {{inValue.LAST_NAME}} {{inValue.NAME}} {{inValue.SECOND_NAME}}
                        </a>


                        <div class="d-flex justify-content-between">
                            <div>
                                <div v-for="itemPhone in inValue.PHONE" :key="itemPhone.ID"
                                     @click="call(itemPhone.VALUE)"
                                     class="small_text-secondary iconHide">
                                    {{itemPhone.VALUE}}
                                    <b-icon icon="telephone-fill"/>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div v-for="itemEmail in inValue.EMAIL"  :key="itemEmail.ID"
                                     class="small_text-secondary iconHide"
                                    @click="email(itemEmail.VALUE)"
                                >
                                    {{itemEmail.VALUE}}
                                    <b-icon icon="envelope-fill"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="small text-secondary">
                            Дата рождения
                        </div>
                        {{inValue.UF_DATE_OF_BIRTH}}
                    </div>
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
                                type="email"
                            />
                        </div>
                    </div>

                </div>
            </template>
            <div v-else>
                НЕТ КОНТАКТА
            </div>


        </div>





    </div>
</template>

<script>
/*eslint no-undef: "off"*/
import wrapInput from './wrap-input'
import {cloneDeep, isEqual} from "lodash";
export default {
    inheritAttrs: false,
    name: "input-string",
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
        call(number){
            number = number.replace(/\D+/g,"")
            top.BXIM.phoneTo(number, {"ENTITY_TYPE_NAME":"CONTACT","ENTITY_ID":this.inValue.ID,"AUTO_FOLD":true})
        },
        email(email){
            let url = `/bitrix/components/bitrix/crm.activity.planner/slider.php?site_id=s1&sessid=${BX.bitrix_sessid()}&context=contact-${this.inValue.ID}&ajax_action=ACTIVITY_EDIT&activity_id=0&TYPE_ID=4&OWNER_ID=${this.inValue.ID}&OWNER_TYPE=CONTACT&OWNER_PSID=0&FROM_ACTIVITY_ID=2&MESSAGE_TYPE=&__post_data_hash=${new Date().getTime()}&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER`
            BX.SidePanel.Instance.open(url,{
                requestMethod: 'post',
                requestParams: {
                    COMMUNICATIONS: [{
                        OWNER_TYPE: 'CONTACT',
                        OWNER_ID: this.inValue.ID,
                        TYPE: 'EMAIL',
                        VALUE: email
                    }]
                }
            })

        },
        reset(){
            this.isShowSelectBlock = false
        }
    }
}
</script>
