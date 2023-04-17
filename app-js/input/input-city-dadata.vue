<template>

    <div class="dadata-options-wrapper">
        <div   v-if="isEdit">
            <input
                v-model="inValue"
                type="text"
                class="form-control"
                autocomplete="off"
                @input="onInputChange"
                @focus="onInputFocus"
                @blur="onInputBlur"
                @keydown="onKeyPress"
            >

            <div
                v-if="inputFocused && options && options.length"
                class="dadata-options"
                @mouseout="onMouseout"
            >
                <Highlighter
                    v-for="(item, key) in options"
                    :key="key"
                    :class="[
                    'dadata-options__item',
                    {'dadata-options__item__hover': key === itemsKey}
                ]"
                    highlightClassName="highlight"
                    :search-words="inValue.split('')"
                    :auto-escape="true"
                    :text-to-highlight="item.value"
                    @mousedown="onClickHighlighter(key)"
                    @mouseover="onMouseover(key)"

                />
            </div>
        </div>
        <div v-else>
            {{inValue}}
        </div>

    </div>

</template>
<script>

import API from '@app/API'
import Highlighter from 'vue-highlight-words'
import {  debounce } from 'lodash'

export default {
    inheritAttrs: false,
    name: "input-address-dadata",
    components: {
        Highlighter,
    },
    props: {
        isEdit: {
            type: Boolean,
            default: true
        },
        className: {
            type: String,
            default: '',
        },
        value: String
    },
    data() {
        return {
            inputFocused: false,
            inValue: this.value,
            options:[],
            itemsKey: false,
        }
    },
    watch: {
        value(){
            if (this.value !== this.inValue){
                this.inValue = this.value
            }
        },
        inValue(){
            this.$emit('input', this.inValue)
            this.search(this.inValue)
        },

    },
    methods: {
        onInputChange(){
            this.isVisible = true
        },
        setValue(dadata){
            this.inValue = dadata.value

            this.$emit('dadata', dadata.data)
        },
        search: debounce(async function (search) {

            const r = await API.addressCity(search)
            this.options = r.data.suggestions;
            this.itemsKey = false

        }, 350),

        onClickHighlighter(key){
            this.setValue(this.options[key])
        },
        onInputFocus() {
            this.inputFocused = true;
        },
        onInputBlur() {
            this.inputFocused = false;

            if (this.itemsKey !== false ){
                this.setValue(this.options[this.itemsKey])

            }


        },
        onMouseover(key){
            this.itemsKey = key;
        },
        onMouseout(){
            this.itemsKey = false;
        },
        onKeyPress(event) {
            const ARROW_DOWN = 40;
            const ARROW_UP = 38;
            const ENTER = 13;

            this.inputFocused = true

            if (!(this.options && this.options)) return false

            if (event.which === ARROW_DOWN ) {

                event.preventDefault();
                this.itemsKey = this.itemsKey === false ? 0 : this.itemsKey
                this.itemsKey++
                if (this.itemsKey > this.options.length - 1) this.itemsKey = 0

            } else if (event.which === ARROW_UP ) {
                event.preventDefault();
                this.itemsKey = this.itemsKey === false ? 0 : this.itemsKey
                this.itemsKey--

                if (this.itemsKey < 0) this.itemsKey = this.options.length - 1

            } else if (event.which === ENTER) {
                this.onInputBlur()
            }

        }

    }

}
</script>

<style scoped>

</style>
