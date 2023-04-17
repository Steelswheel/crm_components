<template>
    <div>
        <div  v-if="isEdit">

            <the-mask :mask="'###,##'" />
            <input

                ref="input"
                class="form-control"
                :disabled="disabled"
                :value="value"
                @input="e => $emit('input',e.target.value)"
                type="number"
                @keydown.enter="$emit('save')"
            >
        </div>

        <div v-else >{{ value }}</div>
    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-integer",
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

        mask: {}
    },
    watch: {

    },
    methods: {

    }
}
</script>
