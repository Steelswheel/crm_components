<template>
    <div>
        <div  v-if="isEdit"
              class="d-flex"
        >
            <div class="b-input">
                <input type="text" class="form-control">
                <span class="b-input__label">ФИО</span>
            </div>

        </div>

        <span v-else @click="edit" :class="{'click-edit': isClickEdit}">{{ value }}</span>
    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-fio",
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
