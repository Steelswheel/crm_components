<template>
    <div>
        <input
            v-if="isEdit"
            ref="input"
            class="form-control"
            :class="{[`form-control-${size}`]:size}"
            :disabled="disabled"
            :value="value"
            @input="e => $emit('input',e.target.value)"
            type="number"
            @keydown.enter="$emit('save')"
        >
        <div v-else >{{ value }}</div>
    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-double",
    props: {
        value: [ String, Number ],
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        size: String
    },
    watch: {
        isEdit() {


        }
    },
    methods: {
        focus(){

        }
    }
}
</script>
