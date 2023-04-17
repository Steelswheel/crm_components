<template>
    <div :class="attribute.className || ''" :data-alias="alias" :data-field="attribute.field" :data-type="type" :data-crm="attribute.crm">
        <div
            :data-label-alias="alias"
            :class="attribute.className ? `${attribute.className}-label` : ``"
            v-if="attribute.label && !attribute.noLabel"
        >
            <template v-if="attribute.label && !attribute.noLabel">
                <label >{{attribute.label}}<span v-if="attribute.required" class="ml-1 text-danger mr-1 fz22">*</span></label>

                <portal-target v-if="type === 'file'" :name="`control${alias}`" class="display-inline-block"></portal-target>
                <portal-target v-if="type === 'file'" :name="`control${alias}block`" ></portal-target>
                <div v-if="attribute.des" class="small" v-html="attribute.des"></div>
            </template>

        </div>
        <div :class="attribute.className ? `${attribute.className}-input` : ``" :data-input-alias="alias">
            <component
                v-bind:is="buildComponent"
                v-bind="attribute.settings"
                :attribute="attribute"
                :attributes="attributes"
                :alias="alias"
                :contactId="contactId"
                ref="component"
                :disabled="methodUpdate === 'disabled'"
                :isEdit="methodUpdate === 'follow' || methodUpdate === 'disabled' || isEdit"
                :value="value === null ? undefined : value"
                :size="size"
                @input="$emit('input',arguments[0])"
                :isClickEdit="methodUpdate === 'click'"
                @edit="edit"
                @save="save"
            />
        </div>
    </div>
</template>

<script>
// import {BIconCheck, /*BIconPencil,*/ BIconX, BSpinner} from "bootstrap-vue";
export default {
    inheritAttrs: false,
    name: "wrap-input",
    components: {
        // BIconPencil,
        //BIconCheck,
        //BIconX,
        // BSpinner,
    },
    props: {
        attributes: Object,
        // Стиль вывода
        styleGroup: {
            type: String,
        },
        contactId: {},
        title: String,
        alias: String,
        attribute: {
            type: Object,
            default: () => ({})
        },
        type: String,
        size: String,
        value: {},
        methodUpdate: {
            type: String,
            validator: function (value) {
                return ['follow', 'click', 'no', 'disabled'].indexOf(value) !== -1
            },
            default: 'follow'
        },

    },
    data() {
        return {
            buildComponent: undefined,
            demoValue: 'DEMO data',
            inValue: this.value,
            isEdit: this.methodUpdate === 'follow',
            isLoading: false,
        }
    },

    watch: {
        methodUpdate(){
            // console.log(this.isEdit, );
            this.isEdit = this.methodUpdate === 'follow'
        },
        value() {
            this.inValue = this.value
        },
        inValue() {
            if (this.methodUpdate === 'follow') {
                this.$emit('input', this.inValue)
            }
        },
    },
    mounted() {
        this.setInput()
    },
    methods: {
        onControl(){

        },
        edit() {
            this.isEdit = true
        },

        endLoading() {
            this.isEdit = false
            this.isLoading = false;
        },
        save() {
            if (this.methodUpdate === 'click'){
                this.isLoading = true;
                this.$emit('save', {value: this.inValue, final: this.endLoading})
            }

            // this.isEdit = false
        },
        cancel() {
            this.inValue = this.value
            this.isEdit = false
        },
        setInput() {
            let input = () => import(`./input-${this.type}`)
            return input()
                .then(() => this.buildComponent = input)
                .catch(() => {
                    console.error(`НЕТ ТИПА input-undefined `,this.attribute);
                })

        },
        reset(){
            this.inValue = this.value
            this.isEdit = false

            if (this.$refs.component.reset){
                this.$refs.component.reset()
            }
        }
    }
}
</script>

<style scoped>

</style>