<template>
    <div>

        <select
            v-if="isEdit"
            ref="input"
            class="form-control"
            :class="size ? `form-control-${size}` : ''"
            :disabled="disabled"
            :value="value"
            @input="e => $emit('input',e.target.value)"
        >
            <option v-for="item in attribute.items" :value="item.id" :key="item.id">{{item.label}}</option>
        </select>
        <span v-else @click="edit" :class="{'click-edit': isClickEdit}">

            {{ attribute.items.find(i => i.id === value) &&
            attribute.items.find(i => i.id === value).label}}
        </span>
    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-stage",
    props: {
        value: [String, Number],
        attribute: {
            type: Object,
            default: () => ({})
        },
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
    },
    watch: {
        isEdit() {
            if (this.isEdit) {
                this.focus()
            }

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
        }
    }
}
</script>
