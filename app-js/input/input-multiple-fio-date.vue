<template>
    <div class="input-multiple">
        <template v-if="isEdit && !onlyValues">
            <div class="d-flex flex-wrap align-items-center" v-for="(item, index) in inValue" :key="index">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="d-flex align-items-center mr-2 mb-1 mt-1">
                        <small>
                            ФИО:
                        </small>
                        <el-input class="ml-2" v-model="item.fio" clearable></el-input>
                    </div>
                    <div :class="index > 0 ? 'd-flex align-items-center mb-1 mt-1 mr-2' : 'd-flex align-items-center mb-1 mt-1'">
                        <small>
                            Дата:
                        </small>
                        <el-date-picker
                            v-if="isEdit"
                            class="ml-2"
                            v-model="item.date"
                            value-format="dd.MM.yyyy"
                            format="dd.MM.yyyy"
                            :picker-options="{
                                firstDayOfWeek: 1
                            }"
                        />
                    </div>
                </div>
                <template v-if="index > 0">
                    <div class="delete-btn add-dotted mt-1 mb-1" @click="inValue.splice(index, 1)">
                        Удалить
                    </div>
                </template>
                <template v-else>
                    <div class="delete-btn mt-1 mb-1"></div>
                </template>
            </div>
            <div class="add-dotted" @click="inValue.push({fio: '', date: ''})">
                Добавить
            </div>
        </template>
        <template v-else>
            <div v-for="(item, index) in inValue" :key="index">
                <div class="d-flex align-items-center">
                    <div class="ml-1 mr-1" v-for="(i, k) in item" :key="`multiple-data-${k}`">
                        {{ i }}
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { Input, DatePicker } from 'element-ui';
export default {
    components: {
        'el-input': Input,
        'el-date-picker': DatePicker
    },
    inheritAttrs: false,
    name: 'input-multiple-fio-date',
    props: {
        onlyValues: {
          type: Boolean,
          default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        value: {
            type: String,
            default: JSON.stringify([{fio: '', date: ''}])
        }
    },
    data() {
        return {
            inValue: JSON.parse(this.value)
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler(value) {
                this.$emit('input', JSON.stringify(value));
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
