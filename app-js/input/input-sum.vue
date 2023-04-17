<template>
  <div>
    {{ getSum }}
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    inheritAttrs: false,
    name: 'input-show-value',
    props: {
        alias: String,
        values: Array
    },
    computed: {
      ...mapGetters('form', [
        'GET_VALUES'
      ]),
      getSum() {
        let sum = 0;

        if (this.values.length > 0) {
          this.values.forEach(value => {
            let field = this.GET_VALUES[value];

            if (field) {
              sum += Number(field);
            }
          });
        }

        let sumBefore = this.GET_VALUES[this.alias];

        console.log(sumBefore);

        if (Number(sumBefore) !== sum) {
          this.$store.commit('form/SET_VALUE', {
            attribute: this.alias,
            value: sum
          });
        }

        return sum > 0 ? sum : '';
      }
    }
}
</script>
