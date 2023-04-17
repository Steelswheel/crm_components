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
                <div >
                    <span class="add-dotted" @click="isShowDoc = !isShowDoc">Документы</span>
                    <div v-if="isShowDoc">
                        <a class="" :href="`/local/ajax/makeResNovs.php?dealId=${this.GET_DEFAULT_VALUE.DEAL_ID}`">
                            Реестр НОВС <i class="el-icon-download"/>
                        </a>
                        <br>
                        <a class="" :href="`/local/ajax/makeResNovsStat.php?dealId=${this.GET_DEFAULT_VALUE.DEAL_ID}`">
                            Заявления НОВС <i class="el-icon-download"/>
                        </a>
                        <br>

                    </div>
                </div>

                <el-button @click="sendDoc" :loading="isLoadingSendDoc" type="success" >
                    Отправить В НОВС <br>
                    реестр и заявления 5Ш
                </el-button>
            </div>

        </div>


    </div>
</template>

<script>
import { BX_POST } from '@app/API'
import { Button } from 'element-ui'
import { mapGetters } from 'vuex'
export default {
    name: "input-gen-payment-enter",
    components: {
        'el-button': Button
    },
    data(){
        return {
            isLoadingSendDoc: false,
            isShowDoc: false,
            successSendId: false,
            isReSend: false,
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
            return this.GET_ALL_EMAILS.filter(i => i.type === 'send_novs')
        },

    },
    methods:{
        sendDoc(){
            this.isLoadingSendDoc = true
            BX_POST('vaganov:edz.show', 'sendEmailNOVS', {
                id: this.GET_DEFAULT_VALUE.DEAL_ID,
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