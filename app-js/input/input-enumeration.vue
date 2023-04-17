<template>
    <div>

        <div  v-show="false">
            <span v-for="item in items" :key="item.id">
                {{item.id}} - {{item.label}}
            </span>
        </div>

        <el-select
            v-if="isEdit"
            :class="size ? `form-control-${size}` : ''"
            :disabled="disabled"
            :value="value"
            @input="$emit('input',arguments[0])"
            filterable
            clearable
        >
            <el-option
                v-for="item in items"
                v-if="item.show !== false || item.id === value"
                :value="item.id"
                :key="item.id"
                :label="item.label"
                :disabled="item.disabled"

            >
                <div class="el-select-dropdown__item__br" v-html="item.label.replace(/\n/g, '<br>')"></div>
            </el-option>
        </el-select>
        <span v-else @click="edit" :class="{'click-edit': isClickEdit}">
            <template v-if="items && items.find(i => i.id === value)">
                <template v-if="items.find(i => i.id === value).url">
                    <div class="d-flex align-items-center">
                        <a :href="items.find(i => i.id === value).url">{{items.find(i => i.id === value).label}}</a>
                        <small v-if="getAdditionalValue" class="ml-2">
                           {{ getAdditionalValue }}
                        </small>
                    </div>
                </template>
                <template v-else>
                    <div class="d-flex align-items-center">
                        <div v-html="items.find(i => i.id === value).label.replace(/\n/g, '<br>')"></div>
                        <small v-if="getAdditionalValue" class="ml-2">
                           {{ getAdditionalValue }}
                        </small>
                    </div>
                </template>
            </template>
            <template v-else>
                <div class="d-flex align-items-center">
                    <div>
                      {{getLabel}}
                    </div>
                    <small v-if="getAdditionalValue" class="ml-2">
                       {{ getAdditionalValue }}
                    </small>
                </div>
            </template>
        </span>
    </div>
</template>

<script>
import { Select, Option } from 'element-ui';

export default {
    inheritAttrs: false,
    name: 'input-enumeration',
    components: {
        'el-select': Select,
        'el-option': Option,
    },
    props: {
        value: [String, Number],
        attribute: {
            type: Object,
            default: () => ({})
        },
        options: Array,
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
        additionalValue: String
    },
    watch: {
        /*options: {
            attribute: {
                handler: function (value){
                    console.log(value);
                },
                deep: true
            }
        }*/
    },
    data(){
        return {

            inValue: this.value,
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
    },
    computed: {
        getLabel() {
            let options = this.options ? this.options : this.attribute.items;

            if (options) {
                let option = options.find(item => item.id === +this.value);

                if (option && option.label) {
                  return option.label;
                }
            }

            return '';
        },
        items() { return this.options ? this.options : this.attribute.items },
        getAdditionalValue() {
            if (this.additionalValue) {
              return this.$store.getters['form/GET_VALUE'](this.additionalValue);
            }

          return false;
        }
    },
    mounted() {
      console.log(this.attribute.field, this.value);
    }
}
</script>
