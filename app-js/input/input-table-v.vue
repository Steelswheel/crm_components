<template>
  <div class="table-v">
    <div class="row" style="margin: 0;">
      <div style="padding: 0;" :class="`column col-xl-${Math.round(12 / attribute.options.length)} col-lg-${Math.round(12 / (attribute.options.length / 2))} col-md-1`" v-for="(item, alias, index) in attribute.fields" :key="alias">
        <div
            class="table-v-wrap d-flex flex-column"
        >
          <div class="table-v-header">
            {{ labels[attribute.options[index]] }}
          </div>
          <div class="table-v-body d-flex justify-content-center align-items-center">
            <wrapInputV
                :alias="alias"
                :className="className"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import wrapInputV from './wrap-input-v';


export default {
  inheritAttrs: false,
  name: 'input-table-v',
  components: {
    wrapInputV
  },
  props: {
    dealId: String,
    alias: String,
    className: String,
  },
  data() {
    return {
      value: {},
      labels: {
        download: 'скачать',
        email: 'отправить по EMAIL',
        sms: 'отправить по СМС',
        attach: 'прикрепить',
        send: 'ОТПРАВИТЬ',
        date: 'ДАТА',
        pd: 'дата',
        ps: 'сумма'
      }
    }
  },
  methods: {
    getAttribute(attr) {
      return this.$store.getters['form/GET_ATTRIBUTE'](attr);
    },
    edit() {
      if (this.isClickEdit){
        this.$emit('edit');
      }
    }
  },
  computed: {
    attribute: {
      get: function () {
        return this.$store.getters['form/GET_ATTRIBUTE'](this.alias);
      },
      set: function(value){
        this.$store.commit('form/SET_ATTRIBUTE', {attribute: this.alias, value});
      }
    }
  }
}
</script>
<style>
  .table-v table {
    overflow-x: auto;
  }

  .table-v .column {
    border-right: 1px solid #ddd;
  }

  .table-v .column:last-child {
    border-right: none;
  }

  .table-v-header {
    text-align: center;
    padding: 5px 0;
    font-weight: bold;
    border-bottom: 1px solid #ddd;
    font-size: 13px;
    line-height: 100%;
  }

  .table-v-body {
    padding: 5px;
    font-size: 12px;
    line-height: 100%;
    flex: 0 1 100%;
    word-break: break-all;
  }
</style>