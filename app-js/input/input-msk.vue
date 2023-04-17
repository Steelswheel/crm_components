<template>
    <div>
        <div v-if="isEdit" class="input-msk__input">

            <input

                ref="input"
                class="form-control"
                :class="{[`form-control-${size}`]:size}"
                :disabled="disabled"
                :value="value"
                @input="e => $emit('input',e.target.value)"
                type="text"
                @keydown.enter="$emit('save')"
            >

            <span @click="setMskMini()" class="input-msk__input__icon mr-1">М</span>
            <span @click="setMskLong()" class="input-msk__input__icon mr-1">Б</span>

        </div>

        <span v-else @click="edit" class="text-break" :class="{'click-edit': isClickEdit}">{{ value }}</span>
    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-msk",
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
        size: String
    },
    watch: {
        isEdit() {

        }
    },
    methods: {
        focus(){
            this.$nextTick(() => {
                this.$refs.input.focus()
            })
        },
        edit(){
            if (this.isClickEdit){
                this.$emit('edit')
            }
        },
        setMskLong(){
            this.$emit('input','775628.25')
        },
        setMskMini(){
            this.$emit('input','586946.72')
        }
    }
}
</script>
