<template>
    <div>
        <input
            v-if="isEdit"
            ref="input"
            class="form-control"
            :class="{[`form-control-${size}`]:size}"
            :disabled="disabled"
            :value="value"
            @input="setValue"
            type="text"
            @keydown.enter="$emit('save')"
        >
        <span v-else @click="edit" class="text-break" :class="{'click-edit': isClickEdit}">{{ value }}</span>
    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-string",
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
        size: String,
        replaceComma: Boolean,
        removeSpaces: Boolean
    },
    watch: {
        isEdit() {

        }
    },
    methods: {
        setValue(e) {
            if (this.replaceComma) {
                e.target.value = e.target.value.replace(',', '.');
            }

            if (this.removeSpaces) {
                e.target.value = e.target.value.replace(' ', '');
            }

            this.$emit('input', e.target.value);
        },
        focus(){
            this.$nextTick(() => {
                this.$refs.input.focus()
            })
        },
        edit(){
            if (this.isClickEdit){
                this.$emit('edit')
            }
        }
    }
}
</script>
