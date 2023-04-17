<template>
    <div>

        <el-tiptap
            v-if="isEdit"
            class="mt-2"
            v-model="inValue"
            :extensions="extensions"
            lang="ru"
        />
        <div v-else
             v-html="inValue"
        ></div>

    </div>
</template>

<script>
import {
    ElementTiptap,
    Doc,
    Text,
    Paragraph,
    Heading,
    Bold,
    Underline,
    Italic,
    Strike,
    ListItem,
    BulletList,
    OrderedList,
    Link,
    CodeBlock,
    Blockquote,
    TextAlign,
    FontSize,
    TextHighlight,
    TextColor,
    FormatClear,
    Table as EditorTable,
    TableHeader,
    TableCell,
    TableRow,
    History,
    TrailingNode,
    HardBreak,
    HorizontalRule,
    LineHeight,
    Indent,
    Image,
} from 'element-tiptap';
import { Message } from 'element-ui';
import { BX_POST } from '@app/API'
export default {
    inheritAttrs: false,
    components: {
        'el-tiptap': ElementTiptap,
    },
    name: "input-tiptap",
    props: {
        value: String,
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
    },
    data(){
        return{
            inValue: this.value,
            extensions: [
                new Doc(), // must be item
                new Text(), // must be item
                new Paragraph(), // must be item
                new Heading({ level: 6 }), // Title
                new Bold({ bubble: true }), // Bold BUBBLE: True Rendering menu button in the bubble menu
                new Underline({ bubble: true }), // Underline Bubble: True, MenuBar: False In the Bubble Menu and Rendering Menu Button in the Menu Bar
                new Italic({ bubble: true }), // slope
                new Strike({ bubble: true }), // Delete line
                new ListItem(), // Use the list must be item
                new BulletList({ bubble: true }), // Unordered list
                new OrderedList({ bubble: true }), // Ordered list
                new Link({ bubble: true }), // Link
                new CodeBlock({ bubble: true }), // code block
                new Blockquote(), // Reference
                new TextAlign({ bubble: true }), // Text alignment
                new FontSize({ bubble: true }), // font size
                new TextHighlight({ bubble: true }), // Text highlight
                new TextColor({ bubble: true }), // Text color
                new FormatClear({ bubble: true }), // Clear format
                new EditorTable({ resizable: true }), // sheet
                new TableHeader(),
                new TableCell(),
                new TableRow(),
                new History(), // revoked
                new TrailingNode(), // Reduce
                new HardBreak(), // split line
                new HorizontalRule(), // line spacing
                new LineHeight(),
                new Indent(),
                new Image({
                    uploadRequest:(file) => {
                        return BX_POST('vaganov:edp.show','upload', {
                            file: file,
                            id: 0,
                            patchSave: 'elTiptapImg'
                        })
                            .then(r => r.url)
                            .catch(() => {
                                Message({
                                    showClose: true,
                                    dangerouslyUseHTMLString: true,
                                    message: 'Ошибка загрузки картинки',
                                    type: 'warning',
                                    duration: 2000
                                });
                                return Promise.reject("Ошибка загрузки картинки")
                            })
                    }
                })
            ]
        }
    },
    watch: {
        inValue(){
            this.$emit('input', this.inValue)
        },
        value(){
            if(this.value !== this.inValue){
                this.inValue = this.value
            }
        }
    },
    methods: {

    }
}
</script>
