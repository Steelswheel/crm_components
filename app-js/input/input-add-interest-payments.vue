<template>
    <div v-if="Array.isArray(value)">
        <table>
            <thead>
                <th style="font-size: 11px">
                    Дата
                </th>
                <th style="font-size: 11px">
                    Сумма
                </th>
            </thead>
            <tbody>
                <tr v-for="(item, index) in value" :key="index">
                    <td style="padding: 5px 15px 5px 0">
                        {{ item.date }}
                    </td>
                    <td>
                        <span v-if="item.sum > 0">
                            {{ item.sum }}
                        </span>
                        <span v-else>
                            <input type="number" @input="setSum($event, item.id)" disabled="true" v-if="getDateDiff(item.date)"/>
                            <input type="number" @input="setSum($event, item.id)" :disabled="success" v-else />
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="mt-2" v-if="this.obj.sum && +this.obj.id > 0 && !success">
            <el-button size="mini" @click="setTranche" :loading="isLoading">
                Отправить платеж
            </el-button>
        </div>
        <div class="mt-2" v-if="GET_INTEREST_PAYMENT_RESPONSIBLE">
          Ответственный за выплаты: {{ GET_INTEREST_PAYMENT_RESPONSIBLE }}
        </div>
    </div>
    <div v-else>
      {{ value }}
    </div>
</template>

<script>
import { Button } from 'element-ui';
import { BX_POST } from '@app/API';
import { mapGetters } from 'vuex';

export default {
    components: {
      'el-button': Button
    },
    inheritAttrs: false,
    name: 'input-add-interest-payments',
    props: {
        value: [String, Number, Array],
        dealId: [String, Number],
    },
    data() {
      return {
        isLoading: false,
        obj: {
            sum: {},
            id: 0
        },
        success: false
      }
    },
    methods: {
        getDateDiff(date) {
          let str = date.split('.');
          let day = str[0];
          let month = str[1];
          let year = str[2];

          let s = year + '-' + month + '-' + day;

          let paymentDate = new Date(s);
          let today = new Date();

          return  paymentDate >= today;
        },
        setSum(event, id) {
            this.obj.sum = event.target.value;
            this.obj.id = id;
        },
        setTranche() {
            if (+this.obj.sum > 0 && +this.obj.id > 0) {
                this.isLoading = true;

                BX_POST('vaganov:eds.show', 'addInterestPayment', {
                    dealId: this.dealId,
                    id: this.obj.id,
                    sum: this.obj.sum
                }).then(data => {
                    this.success = true;
                    this.isLoading = false;
                    console.log(data);
                });
            }
        }
    },
    computed: {
        ...mapGetters('form', [
            'GET_INTEREST_PAYMENT_RESPONSIBLE'
        ]),
        getToday() {
            let now = new Date();
            let day = now.getDate();
            let month = (now.getMonth() + 1);

            if (day < 10) {
                day = '0' + day;
            }

            if (month < 10) {
                month = '0' + month;
            }

            return day + '.' + month + '.' + now.getFullYear();
        }
    }
}
</script>
