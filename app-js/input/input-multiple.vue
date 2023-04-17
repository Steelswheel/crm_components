<template>
    <div class="input-multiple">
        <template v-if="isEdit">
            <div class="d-flex align-items-center mt-1 mb-1" v-for="(item, index) in inValue" :key="index">
                <el-input v-model="item.value" clearable></el-input>
                <template v-if="index > 0">
                    <div class="delete-btn add-dotted ml-2" @click="inValue.splice(index, 1)">
                        Удалить
                    </div>
                </template>
                <template v-else>
                    <div class="delete-btn ml-2"></div>
                </template>
            </div>
            <div class="add-dotted" @click="inValue.push({value: ''})">
                Добавить
            </div>
        </template>
        <template v-else>
            <div v-for="(item, index) in inValue" :key="index">
                {{ item.value }}
            </div>
        </template>
    </div>
</template>

<script>
import { Input } from 'element-ui';
export default {
    components: {
        'el-input': Input
    },
    inheritAttrs: false,
    name: 'input-multiple',
    props: {
        isEdit: {
            type: Boolean,
            default: true
        },
        value: {
            type: String,
            default: JSON.stringify([{value: ''}])
        },
    },
    data() {
        return {
            inValue: JSON.parse(this.value)
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input', JSON.stringify(this.inValue));
            }
        }
    }
}
</script>
<style>
    .input-multiple .delete-btn {
        width: 53px;
    }
</style>
