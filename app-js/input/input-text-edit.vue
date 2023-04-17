<template>
    <div>
        <div   v-if="isEdit" :style="minWidthComponent ? `min-width:${minWidthComponent}px` : ''">

            <div
                v-if="!isEditInValue">
                <span class="click-edit" @click="onEditInValue">{{value}} </span>
                <i @click="onEditInValue" class="ml-1 cursor-pointer el-icon-edit"></i>
            </div>


            <div  v-else class="d-flex">
                <input

                    class="form-control mr-2"
                    :style="widthInput ? `width: ${widthInput}px` : ''"
                    v-model="inValue"
                    @keydown.enter="onEditInValueSave"
                >

                <div @click="onEditInValueSave" class="icon-btn icon-btn--success mr-1"><i class="el-icon-circle-check"></i></div>
                <div @click="onEditInValueClose" class="icon-btn icon-btn--danger"><i class="el-icon-circle-close"></i></div>

            </div>

        </div>


        <div v-else @click="edit"
              :style="widthInput ? `width: ${widthInput}px` : ''"
              class="text-break"
              :class="{'click-edit': isClickEdit}">{{ value }}</div>
    </div>
</template>

<script>

export default {
    inheritAttrs: false,
    name: "input-text-edit",
    components: {

    },
    props: {
        value: [String, Number],
        disabled: {
            type: Boolean,
            default: false
        },
        widthInput: Number,
        minWidthComponent: Number,
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
        size: String
    },
    data(){
        return {
            inValue: this.value,
            isEditInValue: false,
        }
    },
    watch: {
        value(){
            this.inValue = this.value
        }
    },
    methods: {
        onEditInValue() {
            this.isEditInValue = true
        },
        onEditInValueClose() {
            this.inValue = this.value
            this.isEditInValue = false
        },
        onEditInValueSave() {
            this.isEditInValue = false
            this.$emit('input',this.inValue)
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
