<template>
    <div>
        <div v-if="emails.length > 0">
            <div v-for="(item, key) in emails" :key="key">
                <a :href="`/mail/message/${item.ID}`">
                  {{ item.SUBJECT }} <small>{{ item.date }}</small>
                </a>
            </div>
            <span @click="isReSend = !isReSend" class="add-dotted">
              Отправить повторно
            </span>
        </div>
        <div v-if="emails.length === 0 || isReSend">
            <div v-if="successSendId">
                <a :href="`/mail/message/${ successSendId }`">
                  Сообщение отправлено
                </a>
            </div>
            <div v-else>
                <div v-if="isValidate">
                    <div>
                      {{ requiredTextError }}
                    </div>
                    <div v-for="(field, key) in isValidate" class="text-danger" :key="key">
                        {{ GET_ATTRIBUTES[field].label }}
                    </div>
                </div>
                <div v-else>
                    <div>
                      От: {{ emailFrom ? emailFrom : 'НЕТ ПОЧТЫ' }}
                    </div>
                    <div>
                      Кому: {{ emailTo ? emailTo : 'НЕТ ПОЧТЫ' }}
                    </div>
                    <div class="mb-1 text-right" v-if="!hideDocs">
                        <span class="add-dotted">
                          Документы
                        </span>
                        <div>
                            <div v-for="(name, typeDoc) in documents" :key="typeDoc">
                                <a :href="`/b/d/d.php?id=${GET_DEFAULT_VALUE.DEAL_ID}&document=${typeDoc}`">
                                    {{ name }} <i class="el-icon-download"/>
                                </a>
                            </div>
                        </div>
                    </div>
                    <el-button
                      @click="sendEmail"
                      :loading="isLoadingSendEmail"
                      type="success"
                      :disabled="emailFrom === false || emailTo === false"
                      :size="btnSize"
                      class="mt-1"
                    >
                      {{ btnName }}
                    </el-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { BX_POST } from '@app/API';
import { isEmpty } from "lodash";
import { mapGetters } from 'vuex';
import { Button } from 'element-ui';

export default {
    name: 'send-email-send',
    components: {
        'el-button': Button,
    },
    props: {
        hideDocs: Boolean,
        btnSize: String,
        dealType: String,
        emailType: String,
        subject: String,
        body: String,
        to: String,
        from: String,
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
    data() {
        return {
            isLoadingSendEmail: false,
            isReSend: false,
            successSendId: false
        }
    },
    computed: {
        ...mapGetters('form', [
            'GET_DEFAULT_VALUE',
            'GET_ATTRIBUTES',
            'IS_ADMIN',
            'GET_ALL_EMAILS'
        ]),
        emails() {
            if (!this.GET_ALL_EMAILS) {
                return [];
            }

            return this.GET_ALL_EMAILS.filter(i => i.type === this.emailType);
        },
        isValidate() {
            let fieldError = [];

            this.required.forEach(i => {
                if (isEmpty(this.GET_DEFAULT_VALUE[i])) {
                    fieldError.push(i);
                }
            });

            return fieldError.length > 0 ? fieldError : false;
        },
        emailTo() {
            if (this.to === 'client') {
                return this.GET_DEFAULT_VALUE.EMAIL[0].VALUE;
            }

            if (this.to) {
                return this.to;
            }

            if (this.dealType === 'eds') {
              if (this.GET_DEFAULT_VALUE.PART_ZAIM && this.GET_DEFAULT_VALUE.PART_ZAIM.email[0]) {
                return this.GET_DEFAULT_VALUE.PART_ZAIM.email[0].VALUE;
              } else {
                  if (this.GET_DEFAULT_VALUE.EMAIL.length > 0) {
                      return this.GET_DEFAULT_VALUE.EMAIL[0].VALUE;
                  } else {
                      return false;
                  }
              }
            } else {
              if (this.GET_DEFAULT_VALUE.PART_ZAIM && this.GET_DEFAULT_VALUE.PART_ZAIM.email[0]) {
                  if (this.GET_DEFAULT_VALUE.PART_ZAIM.email.length > 0) {
                      return this.GET_DEFAULT_VALUE.PART_ZAIM.email[0].VALUE;
                  } else {
                      return false;
                  }
              }
            }

            return false;
        },
        emailFrom() {
            if (this.from) {
                return this.from;
            }

            if (this.GET_DEFAULT_VALUE.USER_EMAIL && this.GET_DEFAULT_VALUE.USER_EMAIL[0]) {
                return this.GET_DEFAULT_VALUE.USER_EMAIL[0];
            }

            return false;
        }
    },
    methods: {
        sendEmail() {
            this.isLoadingSendEmail = true;

            let component = '';

            if (this.dealType === 'eds') {
              component = 'vaganov:eds.show';
            } else {
              component = 'vaganov:edz.show';
            }

            BX_POST(component, 'sendEmail', {
                id: this.GET_DEFAULT_VALUE.DEAL_ID,
                emailTo: this.emailTo,
                emailFrom: this.emailFrom,
                emailType: this.emailType,
                subject: this.subject,
                body: this.body,
                documents: Object.keys(this.documents).join(),
            }).then(r => {
                this.isLoadingSendEmail = false;
                this.successSendId = r.id;
                console.log(r);
            }).catch(e => {
              console.log(e);
              this.isLoadingSendEmail = false;
            });
        }
    }
}
</script>

<style scoped>

</style>