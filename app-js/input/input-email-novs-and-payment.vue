<template>
    <div class="">


        <div v-if="emails.length > 0">

            <div v-for="(item, key) in emails" :key="key">
                <a :href="`/mail/message/${item.ID}`">{{item.SUBJECT}} <small>{{item.date}}</small></a>
            </div>

            <span @click="isReSend = !isReSend" class="add-dotted">Отправить повторно</span>
        </div>


        <div v-if="emails.length === 0 || isReSend">

            <div v-if="successSendId">
                <a :href="`/mail/message/${successSendId}`">Сообщение отправленно</a>
            </div>
            <div v-else>
                <div v-if="isValidateNovs">
                    Для формирования заявления  не заполнены обязательные поля:

                    <div v-for="(field, key) in isValidateNovs" class="text-danger" :key="key">
                        {{GET_ATTRIBUTES[field].label}}
                    </div>
                </div>
                <div v-else>

                    <div>От: {{emailManager ? emailManager : 'НЕТ ПОЧТЫ' }}</div>
                    <div>Кому: {{emailSendPartner ? emailSendPartner : 'НЕТ ПОЧТЫ'}}</div>

                    <div class="mb-1">
                        <span class="add-dotted" @click="isShowDoc = !isShowDoc">Документы</span>
                        <div v-if="isShowDoc">
                            <a class="" :href="`/local/ajax/makePaymentEntranceFees.php?dealId=${GET_DEFAULT_VALUE.DEAL_ID}`">
                                
                            </a>
                            <br>
                            <a class="" :href="`/local/php_interface/makePDF/makeNovsDocs.php?deal_id=${GET_DEFAULT_VALUE.DEAL_ID}&show`">
                                
                            </a>
                        </div>
                    </div>

                    <el-button @click="sendDoc" :loading="isLoadingSendDoc" type="success" :disabled="emailSendPartner === false || emailManager === false">
                        Отправить ПАРНЕРУ <br>
                        заявление 5Ш, квитанцию <br>

                    </el-button>

                </div>
            </div>


        </div>




    </div>
</template>

<script>
import { BX_POST } from '@app/API'
import { Button } from 'element-ui'
import { isEmpty } from 'lodash'
import { mapGetters } from 'vuex'
export default {
    name: "input-gen-novs",
    components: {
        'el-button': Button,
    },
    data(){
        return {
            isShowDoc: false,
            isLoadingSendDoc: false,
            requiredFieldNovs: [
                'LAST_NAME', 'NAME', 'SECOND_NAME', 'BIRTHDATE', 'UF_BORROWER_BIRTH_PLACE', 'REGISTRATION_PLACE',
                'CONTACT_INN_NUMBER', 'CONTACT_SNILS_NUMBER', 'UF_BORROWER_PASSPORT_SER', 'UF_BORROWER_PASSPORT_NUMBER',
                'UF_BORROWER_KEM_VIDAN', 'UF_BORROWER_DATE', 'UF_BORROWER_KOD', 'PHONE', 'EMAIL',
            ],
            emailSend: '',
            successSendId: false,
            isReSend: false,
        }
    },
    watch:{
        'this.GET_DEFAULT_VALUE'(){
            console.log('+++++----------');
        }
    },
    computed: {

        ...mapGetters('form',[
            'GET_DEFAULT_VALUE',
            'GET_ATTRIBUTES',
            'IS_ADMIN',
            'GET_ALL_EMAILS'
        ]),
        emails(){
            return this.GET_ALL_EMAILS.filter(i => i.type === 'enter_kpk')
        },
        isValidateNovs(){
            let fieldError = []
            this.requiredFieldNovs.forEach(i => {
                if (isEmpty(this.GET_DEFAULT_VALUE[i])){
                    fieldError.push(i)
                }
            })

            return fieldError.length > 0
                ? fieldError
                : false
        },
        emailSendPartner(){

            if (this.GET_DEFAULT_VALUE.PART_ZAIM && this.GET_DEFAULT_VALUE.PART_ZAIM.email[0]){
                return this.GET_DEFAULT_VALUE.PART_ZAIM.email[0].VALUE
            }
            return false
        },
        emailManager(){
            if (this.GET_DEFAULT_VALUE.USER_EMAIL && this.GET_DEFAULT_VALUE.USER_EMAIL[0]){
                return this.GET_DEFAULT_VALUE.USER_EMAIL[0]
            }
            return false;
        }

    },
    methods:{
        sendDoc(){
            this.isLoadingSendDoc = true
            BX_POST('vaganov:edz.show', 'sendEnterKpk', {
                id: this.GET_DEFAULT_VALUE.DEAL_ID,
                emailManager: this.emailManager,
                emailPartner: this.emailSendPartner,

            })
            .then(r => {
                this.isLoadingSendDoc = false
                this.successSendId = r.id
                console.log(r)
            }).catch(e => {
                console.log(e);
                this.isLoadingSendDoc = false
            })
        }
    }
}
</script>

<style scoped>

</style>