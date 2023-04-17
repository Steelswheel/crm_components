<template>
    <div>
        <el-select
            v-model="selectedDoc"
            class="mb-2"
        >
            <el-option
                v-for="item in attribute.settings.options"
                :key="item.id"
                :label="item.label"
                :value="item.id"
            >
                {{ item.label }}
            </el-option>
        </el-select>

        <a v-show="selectedDoc" :href="selectedDoc" target="_blank">
            СКАЧАТЬ <i class="el-icon-download"></i>
        </a>
    </div>
</template>

<script>

import { Select, Option } from 'element-ui';

export default {
    name: 'input-select-docs',
    components: {
        'el-select': Select,
        'el-option': Option
    },
    props: {
        attribute: {
            type: Object,
            default: () => ({})
        }
    },
    data() {
        return {
            selectedDoc: ''
        }
    }
}
</script>

<style scoped>

</style>