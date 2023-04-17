<template>
    <div>
        <portal :to="`control${alias}`" >


            <button
                v-if="isMini"
                @click.prevent="isShowFull = !isShowFull"
                class="btn btn-xs btn-default" ><b-icon icon="arrows-fullscreen" scale="1" variant="success"/>
            </button>

        </portal>
         <textarea
             v-if="isEdit"
             :value="value"
             @input="e => $emit('input',e.target.value)"
             :rows="rows"
             class="form-control"
             :disabled="disabled"
         ></textarea>

        <div  v-else>
            <div
                @click="showFull"
                style="white-space: pre-line"
                class="text-break"
                :class="{
                'click-edit': isClickEdit,
                'textViewMini': isMini ,
                'textViewMini--open':  isShowFull
            }">{{ value }}</div>
            <div v-if="isMini" @click="isShowFull = !isShowFull" class="add-dotted">Весь текст</div>
        </div>

    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-text",
    props: {
        alias: String,
        rows: {
            type: Number,
            default: 5
        },
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
        size: String,
        isMini: {
            type: Boolean,
            default: false
        }
    },
    data(){
        return{
            isShowFull: false
        }
    },
    watch: {

    },
    methods: {
        focus(){

        },
        showFull(){},
        edit(){
            if (this.isClickEdit){
                this.$emit('edit')
            }
        }
    }
}
</script>
