<template>
  <div>
    <div class="mt-1 mb-1" v-if="Array.isArray(value)">
      <div v-for="(item, index) in value" :key="index">
        {{ item }}
      </div>
    </div>
    <div class="mt-1 mb-1" v-else>
      {{ getValue }}
    </div>
  </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: 'input-show-value',
    props: {
        alias: String,
        value: [String, Number, Array]
    },
    computed: {
        getValue() {
            let val = this.value;

            return val;
        }
    }
}
</script>
