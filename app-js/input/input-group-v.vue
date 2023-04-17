<template>
    <div :class="attribute.classNameGroup || ''" :style="attribute.style || ''">

        <wrapInputV
            v-for="(item, alias) in attribute.fields"
            :key="item.field"
            :alias="alias"
            :className="className"
        />

    </div>
</template>

<script>
import wrapInputV from './wrap-input-v'


export default {
    inheritAttrs: false,
    name: "input-group-v",
    components: {
        wrapInputV
    },
    props: {
        alias: String,
        className: String,
    },

    computed: {
        attribute: {
            get: function () {
                return this.$store.getters['form/GET_ATTRIBUTE'](this.alias)
            },
            set: function(value){
                this.$store.commit('form/SET_ATTRIBUTE', {attribute: this.alias, value})
            }
        },
    },
    methods: {

        edit(){
            if (this.isClickEdit){
                this.$emit('edit')
            }
        }
    }
}
</script>
