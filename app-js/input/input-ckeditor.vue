<template>
    <div>
        <div  v-if="isEdit">
            <ckeditor :config="editorConfig"  v-model="inValue"></ckeditor>
        </div>

        <span v-else @click="edit" class="text-break" :class="{'click-edit': isClickEdit}" v-html="inValue"></span>
    </div>
</template>

<script>
import CKEditor from 'ckeditor4-vue';


export default {
    inheritAttrs: false,
    name: "input-ckeditor",
    components: {
        ckeditor: CKEditor.component
    },
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
    },
    watch: {
        value(){
            if(this.value !== this.inValue){
                this.inValue = this.value
            }
        },
        inValue(){
            this.$emit('input',this.inValue)
        }
    },
    data() {
        return {
            editorConfig: {
                toolbarGroups: [
                    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
                    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                    { name: 'insert', groups: [ 'insert' ]},
                    { name: 'styles', groups: [ 'styles' ]},
                ],
                 removeButtons: 'Underline,JustifyCenter,Specialchar,HorizontalRule,SpecialChar,Superscript,Outdent,Indent'
            },
            inValue: this.value
        };
    }
}
</script>
