<template>
    <div>
        <div  v-if="isEdit"
              class="d-flex"
        >

            <div class="b-input">

<!--                <input type="text" class="form-control">
                <span class="b-input__label">Номер</span>-->

                <wrap-input

                    :attribute="{label:'Серия',miniLabel: true}"
                    type="string"
                />

            </div>
            <div class="b-input ml-1">
                <wrap-input

                    :attribute="{label:'Скан',miniLabel: true}"
                    type="file"
                />
            </div>



        </div>
<!--        <input

            ref="input"
            class="form-control"
            :disabled="disabled"
            :value="value"
            @input="e => $emit('input',e.target.value)"
            type="text"
            @keydown.enter="$emit('save')"
        >-->
        <span v-else @click="edit" :class="{'click-edit': isClickEdit}">{{ value }}</span>
    </div>
</template>

<script>
import wrapInput from './wrap-input'
export default {
    inheritAttrs: false,
    components: {
        wrapInput
    },
    name: "input-input-file",
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
        focus(){

        },
        edit(){
            if (this.isClickEdit){
                this.$emit('edit')
            }
        }
    }
}
</script>
