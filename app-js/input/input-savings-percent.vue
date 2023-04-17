<template>
  <div class="mt-2">
    {{ value }}
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { getForm } from '@app/../store/helpStore';

export default {
    inheritAttrs: false,
    name: 'input-savings-percent',
    props: {
      dealId: {},
      alias: String,
      value: [String, Number]
    },
    watch: {
      getPercent(value) {
        if (value !== '-') {
          this.$store.commit('form/SET_VALUE', { attribute: this.alias, value });
        } else {
          value = null;
          this.$store.commit('form/SET_VALUE', { attribute: this.alias, value });
        }
      }
    },
    data() {
        return {
          stages: ['C14:NEW', 'C14:ENTRY_INTO_KPK', 'C14:SIGNING_DS']
        }
    },
    computed: {
        ...mapGetters('form', [
          'GET_PROGRAMS',
          'GET_STAGES_HIS'
        ]),
        getOtherStages() {
            let hisStages = Object.keys(this.GET_STAGES_HIS);

            if (hisStages.length > 0) {
              let arr = hisStages.filter(stage => {
                if (!this.stages.includes(stage)) {
                  return stage;
                }
              });

              return arr.length > 0;
            }

            return false;
        },
        STAGE_ID: getForm('STAGE_ID'),
        getPercent() {
          if (!this.dealId || !this.getOtherStages) {
              let program = this.$store.getters['form/GET_VALUE']('UF_SAVINGS_PROGRAM');
              let period = this.$store.getters['form/GET_VALUE']('UF_CONTRACT_PERIOD');

              if (program && period && period !== 'no') {
                return this.GET_PROGRAMS[program]['VALUES']['RATES'][period];
              }
          } else {
              return this.$store.getters['form/GET_VALUE']('UF_CONTRACTUAL_INTEREST_RATE');
          }

          return '-';
        }
    }
}
</script>
