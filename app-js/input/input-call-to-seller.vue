<template>
    <div>




        <div class="d-flex">
            <input-phone
                v-if="toWhom === 'seller'"
                v-model="SELLER_PHONE"
                :isEdit="editPhone"
            />
            <input-phone
                v-else
                v-model="PHONE"
                :isEdit="editPhone"
            />

            <a href="#"  @click.prevent="editPhone = !editPhone" class="ml-3 "> <i class="el-icon-edit"></i> </a>

        </div>




        <div>

        </div>


        <div class="mt-2">
            <b @click="isOpen = !isOpen" class="add-dotted">СКРИПТ ДИАЛОГА С {{toWhom === 'seller' ? 'ПРОДАВЦОМ' : 'ЗАЕМЩИКОМ'}} </b>
            <div v-if="isOpen">
                <template v-if="toWhom === 'seller'">
                </template>
                <template v-else>
                    <p>— Здравствуйте, могу услышать ФИО заемщика ?</p>
                    <p>— вы подавали заявку на займ для  покупку жилья ?</p>
                    <p>— уточните адрес приобретаемого жилья ?</p>
                    <p>— уточните точную сумму займа ? </p>
                </template>

            </div>
        </div>


        <pre style="display: none">{{value}}</pre>

            <div v-if="value">
                <div v-for="item in value"
                     :key="item.ID"
                     class="mt-2">
                    {{item["CALL_START_DATE"] | datetime}} -
                    <template >

                        {{new Date(item.CALL_DURATION * 1000).getMinutes()}}:{{new Date(item.CALL_DURATION * 1000).getSeconds()}}

                        <span class="ml-2">{{item.PHONE_NUMBER}}</span>


                        <template v-if="item.CALL_RECORD_URL">
                            <a @click.prevent="isShowId = item.ID" href="#" >Прослушать</a>
                            <div>
                                <audio
                                    style="width:100%; height: 30px;"
                                    v-if="isShowId === item.ID"
                                    controls autoplay
                                    :src="item.CALL_RECORD_URL">
                                </audio>
                            </div>
                        </template>
                        <template v-else>
                            Нет записи
                        </template>

                    </template>


                </div>
            </div>






    </div>
</template>

<script>
import inputPhone from './input-phone'
import { getForm } from '@app/../store/helpStore'
import { mapGetters } from "vuex";
export default {
    inheritAttrs: false,
    components: {
        inputPhone,
    },
    name: "input-call-to-seller",
    props: {

        value: [Array,String],
        tranche: Number,
        toWhom: String,
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
            isShowId: false,
            isOpen: false,
            editPhone: false,
        }
    },
    computed: {
        ...mapGetters('form',[
            'IS_LOADING_UPDATE'
        ]),
        SELLER_PHONE: getForm('SELLER_PHONE'),
        PHONE: getForm('PHONE'),
        DEAL_ID: getForm('DEAL_ID'),

        getPhone(){
            if(this.toWhom === 'seller') {
                return this.SELLER_PHONE
            }

            return this.PHONE
        }
    },
    watch: {
        IS_LOADING_UPDATE(isLoading){
            if(!isLoading){
                this.editPhone = false
            }
        },

    },
    methods: {


    }
}
</script>
