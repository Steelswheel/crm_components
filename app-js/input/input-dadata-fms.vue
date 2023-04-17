<template>
    <div>

        <div class="dadata-options-wrapper">



            <input
                v-model="searchText"
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
                class="dadata-options">
                <div
                    v-for="(item, key) in options"
                    :key="key"
                    :class="[
                    'dadata-options__item',
                    {'dadata-options__item__hover': key === itemsKey}
                ]"


                    @mousedown="onClickHighlighter(key)"
                    @mouseover="onMouseover(key)"
                >
                    <small>{{item.data.code}}</small>

                    {{item.data.name}}

                </div>
            </div>

        </div>

    </div>
</template>

<script>

import API from '@app/API'
import { cloneDeep, debounce } from 'lodash'

export default {
    inheritAttrs: false,
    name: "input-dadata-fms",
    components: {

    },
    props: {
        className: {
            type: String,
            default: '',
        },
        value: {
            type: Object,
            default: () => ({value: '' })
        }
    },
    data() {
        return {
            inputFocused: false,
            searchText: this.value.value,
            inValue: cloneDeep(this.value),

            options:[],
            itemsKey: 0,
        }
    },
    watch: {
        value(){
            this.searchText = this.value.value
        },
        searchText(){
            this.search(this.searchText)
            if (this.searchText !== this.inValue.value){
                this.$emit('input', { value: this.searchText });
            }

        }
    },
    methods: {

        setValue(key){

            this.inValue = this.options[key]
            this.searchText = this.inValue.value
            this.$emit('input', this.inValue);
        },
        onInputChange(){
            this.isVisible = true
            // itemsKey
        },

        search: debounce(async function (search) {


            const r = await API.fms(search)

            this.options = r.data.suggestions.map(i => {
                i.value = i.data.code
                return i
            });

            this.itemsKey = 0

        }, 350),


        onClickHighlighter(key){
            this.inputFocused = false;
            this.setValue(key)
        },
        onInputFocus() {
            this.inputFocused = true;
        },
        onInputBlur() {
            this.inputFocused = false;

          /*  if (this.searchText.length > 3 && this.options[this.itemsKey]){
                this.inValue = this.options[this.itemsKey]

                this.setValue()
            }
            this.searchText = this.inValue.value*/

        },
        onMouseover(key){
            this.itemsKey = key;
        },
        onKeyPress(event) {
            const ARROW_DOWN = 40;
            const ARROW_UP = 38;
            const ENTER = 13;

            this.inputFocused = true

            if (!(this.options && this.options)) return false

            if (event.which === ARROW_DOWN ) {
                event.preventDefault();
                this.itemsKey++
                if (this.itemsKey > this.options.length - 1) this.itemsKey = 0

            } else if (event.which === ARROW_UP ) {
                event.preventDefault();
                this.itemsKey--

                if (this.itemsKey < 0) this.itemsKey = this.options.length - 1

            } else if (event.which === ENTER) {

                this.inputFocused = false;

                this.setValue(this.itemsKey)

            }

        }
    }

}
</script>

<style scoped>

</style>
