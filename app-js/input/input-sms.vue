<template>
    <div class="d-flex justify-content-center">
        <div v-if="getPhone">
            <div v-if="getState || status" class="mb-2">
                <small v-if="getState || date">
                    Отправлено: {{ getState ? getState : date }}
                </small>
                <small class="text-danger" v-if="status">
                    {{ status }}
                </small>
            </div>
            <el-button
                size="mini"
                type="primary"
                @click="send"
                :loading="loading"
                :disabled="disableBtn"
            >
                <template v-if="getState || status">
                    Отправить повторно
                </template>
                <template v-else>
                    Отправить SMS
                </template>
            </el-button>
        </div>
        <div v-else>
            <div class="text-danger">
                Не заполнен номер телефона!
            </div>
        </div>
    </div>
</template>

<script>
import { Button } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    props: {
        disableBtn: Boolean,
        dealId: {},
        alias: String
    },
    name: 'input-sms',
    components: {
        'el-button': Button
    },
    data() {
        return {
            status: '',
            loading: false,
            date: ''
        }
    },
    methods: {
        send() {
            this.loading = true;

            if (this.getPhone) {
                BX_POST('vaganov:eds.show', 'sendSms', {
                    dealId: this.dealId,
                    field: this.alias,
                    phone: this.getPhone
                }).then((response) => {
                    if (!response.ERROR) {
                        this.date = response.VALUE;
                    } else {
                        this.status = 'Не удалось отправить СМС: ' + response.VALUE;

                        console.log(response.ERROR_TEXT);
                    }
                }, (error) => {
                    console.log(error);
                }).finally(() => this.loading = false);
            }
        }
    },
    computed: {
        getState() {
            if (!this.date) {
                return this.$store.getters['form/GET_VALUE'](this.alias);
            } else {
                return this.date;
            }
        },
        getPhone() {
            let phones = this.$store.getters['form/GET_VALUE']('PHONE');

            if (phones.length > 0) {
                console.log(phones);

                return phones[0].VALUE;
            } else {
                return false;
            }
        }
    }
}
</script>

<style>

</style>