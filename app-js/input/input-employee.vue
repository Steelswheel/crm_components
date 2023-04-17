<template>
    <div>
        <input
            v-if="isEdit"
            ref="input"
            class="form-control"
            :disabled="disabled"
            :value="value"
            @input="e => $emit('input',e.target.value)"
            type="text"
            @keydown.enter="$emit('save')"
        >
        <span v-else @click="edit" :class="{'click-edit': isClickEdit}">{{ value }}</span>
    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-employee",
    props: {
        value: [String, Number],
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
    },
    watch: {
        isEdit() {


        }
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
