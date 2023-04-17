<template>
    <div class="input-file">


        <div
                v-for="(file, key) in inValue.filter(i => !i.l && !i.del)"
                :key="'1'+key"
                class="input-file__item"
        >

            <a target="_blank" :href="file.get_src" class="text-dark">{{file.FILE_NAME}}</a>

            <span class="ml-1">

                        ++++

                        <a v-if="isUpdate" @click.prevent="remove(file.ID)" href="#" class="input-file__r">
                            <small><b-icon icon="trash-fill" class="text-secondary"/></small>
                        </a>
                    </span>

        </div>

    </div>

</template>

<script>



import {cloneDeep, isEqual} from 'lodash'
export default {
    inheritAttrs: false,
    name: "input-file",
    components: {
        // 'font-awesome-icon': FontAwesomeIcon,
    },
    props: {
        value: {
            type: Array,
            default: () => ([])
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        alias: String,
        attribute: {},
        label: String,
        test: {},
        file: Array,
        img: Array
    },
    data() {
        console.log();
        return {
            inValue: cloneDeep(this.value),
            loadFiles: undefined,
            showPhoto: 1,
            files: [],
            objectFile: {
                tmp: '',
                error: false,
                loading: true,
                deleted: false,
            },
            isShowDeleted: false,
            sizeBlock:  0,
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input',this.inValue)
            }
        },
        value(){
            if (!isEqual(this.value,this.inValue)){
                this.inValue = cloneDeep(this.value)
            }
        },
        fileDeleted(){
            if (this.fileDeleted.length === 0){
                this.isShowDeleted = false
            }
        }
    },

    mounted(){

        if (localStorage.getItem('field' + this.alias)){
            this.onSizeBlock()
        }
    },
    computed: {
        isUpdate(){
            return this.isEdit
        },
        fileDeleted(){
            return this.inValue.filter(i => i.del)
        }
    },
    methods: {




    }
}
</script>

<style scoped>

</style>