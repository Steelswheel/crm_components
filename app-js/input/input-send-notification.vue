<template>
  <div class="d-flex align-items-center">
    <el-button
        size="mini"
        @click="send"
        :loading="isLoad"
    >
      <template v-if="!value && !message">
        отправить на проверку
      </template>
      <template v-else>
        отправить повторно
      </template>
    </el-button>
    <small v-if="value || message" class="ml-2">
      Отправлено на проверку: {{ value ? value : message }}
    </small>
  </div>
</template>
<script>

import { Button } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    inheritAttrs: false,
    name: 'input-send-notification',
    components: {
        'el-button': Button
    },
    props: {
        alias: {},
        isEdit: {
            type: Boolean,
            default: true
        },
        text: String,
        value: String,
        dealId: [String, Number],
        id: [String, Number, Array]
    },
    data() {
        return {
            isLoad: false,
            message: ''
        }
    },
    methods: {
        send() {
          this.isLoad = true;

          BX_POST('vaganov:edz.show', 'sendNotification', {
            dealId: this.dealId,
            id: JSON.stringify(this.id),
            text: this.text,
            alias: this.alias
          }).then(response => {
            this.message = response;
            console.log(response);
          }).catch(error => {
            console.log(error);
          }).finally(() => {
            this.isLoad = false;
          });
        }
    }
}
</script>

<style scoped>

</style>
