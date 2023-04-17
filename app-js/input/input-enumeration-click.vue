<template>
    <div>
        <div
            v-if="isClickEdit"
            class="d-flex align-items-center"
        >
            <select

                ref="input"
                class="form-control"
                :class="size ? `form-control-${size}` : ''"
                :disabled="disabled"
                :value="value"
                @input="e => $emit('input',e.target.value)"
            >

                <option v-for="item in items" :value="item.id" :key="item.id">{{item.label}}</option>
            </select>

            <b-icon
                class="cursor-pointer"
                @click="isClickEdit = false"
                icon="x"/>
        </div>

        <span v-else @click="edit" class="click-edit">
            <template v-if="items && items.find(i => i.id === value)">
                <template v-if="items.find(i => i.id === value).url">
                    <a :href="items.find(i => i.id === value).url">{{items.find(i => i.id === value).label}}</a>
                </template>
                <template v-else>
                    {{items.find(i => i.id === value).label}}
                </template>
            </template>
        </span>



    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-enumeration",
    props: {
        value: [String, Number],
        attribute: {
            type: Object,
            default: () => ({})
        },
        options: Array,
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },

        size: String,
    },
    watch: {
        isEdit() {


        }
    },
    data(){
        return {
            isClickEdit: false,
            items: this.options ? this.options : this.attribute.items
        }
    },
    methods: {
        focus(){

        },
        edit(){
            this.isClickEdit = true
        }
    }
}
</script>
