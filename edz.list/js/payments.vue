<template>
  <div>
    <el-tooltip
        class="item"
        effect="dark"
        placement="left"
    >
      <div slot="content">
        <div v-for="(payment, index) in getPayments" :key="index" class="mt-1 mb-1" style="white-space: nowrap">
          {{ payment.DESCRIPTION }} {{ payment.SUM }} {{ payment.DATE }}
        </div>
      </div>
      <div>
        {{ sum }}
      </div>
    </el-tooltip>
  </div>
</template>

<script>
import { Tooltip } from 'element-ui';

export default {
  name: 'payments',
  components: {
    'el-tooltip': Tooltip
  },
  props: {
    payments: String,
    sum: String
  },
  data() {
    return {}
  },
  methods: {},
  computed: {
    getPayments() {
      return JSON.parse(this.payments.split("'").join('"'));
    }
  }
}
</script>

<style scoped>

</style>