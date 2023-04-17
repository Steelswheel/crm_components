<template>
    <div>
        <div
            style="white-space: pre-line"
            :class="{
                'textViewMini': isMini ,
                'textViewMini--open':  isShowFull
            }"
            v-html="inValue[field]"
        ></div>

        <textarea
            v-if="isAddComment"
            v-model="inValue[field+'_ADD']"
            :rows="rows"
            class="form-control mt-1"
        ></textarea>
        <span v-if="attribute ? attribute.rule : true" @click="isAddComment = !isAddComment" class="add-dotted" :class="{'text-primary': isAddComment}">Добавить комментарий</span>
        <span v-if="isMini" @click="isShowFull = !isShowFull" class="add-dotted ml-2" :class="{'text-primary': isShowFull}">Весь текст</span>
    </div>
</template>

<script>
import {cloneDeep, isEqual} from "lodash";

export default {
    inheritAttrs: false,
    name: "input-comments",
    props: {
        field: String,
        alias: String,
        rows: {
            type: Number,
            default: 5
        },
        value: {
            type: Object,
            default: () => ({})
        },
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
        isMini: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            isShowFull: false,
            inValue: cloneDeep(this.value),
            isAddComment: false,
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input', this.inValue)
            }
        },
        value() {
            if (!isEqual(this.value, this.inValue)) {
                this.inValue = cloneDeep(this.value)
            }
        },
    },
    methods: {
        reset(){

            // this.isShowFull = false
            this.isAddComment = false
        }
    }
}
</script>
