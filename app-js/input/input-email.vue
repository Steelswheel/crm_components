<template>
    <div>
        <div v-if="isEdit">


            <div v-for="item in inValue" v-if="!deletedIds.includes(item.ID)" :key="item.ID">

                <div class="d-flex mb-2">
                    <div class="flex-grow-1 ">
                        <input v-model="item.VALUE" type="text" class="form-control" :disabled="disabled">
                    </div>
                    <div v-if="!disabled">
                        <div @click="remove(item.ID)" class="btn btn-sm ">
                            <BIconX/>
                        </div>

                    </div>
                </div>



            </div>

            <span @click="add" v-if="!disabled" class="add-dotted">Добавить</span>

        </div>
        <div v-else >

            <div v-if="!disabled">
                <div v-for="item in inValue" v-if="item.VALUE !== ''" :key="item.ID">
                    <span @click="email(item.VALUE)"
                          :class="{'click-edit': contactId}">{{item.VALUE}}</span>
                </div>

            </div>



        </div>
    </div>
</template>

<script>
/*eslint no-undef: "off"*/
import {cloneDeep, isEqual} from 'lodash'
export default {
    inheritAttrs: false,
    name: "input-email",
    props: {
        value: {
            type: Array,
            default: () => ([])
        },
        contactId: {},
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
    },
    data(){
        return {
            inValue: cloneDeep(this.value),
            typePhone: [

            ],
            addObject: { "VALUE_TYPE": "WORK", "VALUE": "", "TYPE_ID": "PHONE" },
            deletedIds: [],
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
        isEdit() {

        }
    },
    mounted() {
        // this.$emit('edit')
    },
    methods: {
        add(){

            let addObject = { ...this.addObject }
            addObject.ID = `n${this.inValue.length}`
            this.inValue.push(addObject)
        },
        focus(){

        },
        edit(){
            if (this.isClickEdit){
                this.$emit('edit')
            }
        },
        remove(id){
            this.deletedIds.push(id)
            this.inValue.find(i => i.ID === id).VALUE = ''
        },
        email(email){
            console.log(email,this.contactId);
            if (this.contactId){
                let url = `/bitrix/components/bitrix/crm.activity.planner/slider.php?site_id=s1&sessid=${BX.bitrix_sessid()}&context=contact-${this.contactId}&ajax_action=ACTIVITY_EDIT&activity_id=0&TYPE_ID=4&OWNER_ID=${this.contactId}&OWNER_TYPE=CONTACT&OWNER_PSID=0&FROM_ACTIVITY_ID=2&MESSAGE_TYPE=&__post_data_hash=${new Date().getTime()}&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER`
                BX.SidePanel.Instance.open(url,{
                    requestMethod: 'post',
                    requestParams: {
                        COMMUNICATIONS: [{
                            OWNER_TYPE: 'CONTACT',
                            OWNER_ID: this.contactId,
                            TYPE: 'EMAIL',
                            VALUE: email
                        }]
                    }
                })
            }


        },

    }
}
</script>
