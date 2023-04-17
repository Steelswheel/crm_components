<template>
    <div class="design-list-upload">
        <div
            v-if="files.length > 0"
            class="mb-2"
        >
            <div
                v-for="(file, index) in files"
                :key="file.name"
                class="d-flex align-items-center"
            >
                <a :href="file.url" download>
                    {{file.name}}
                </a>
                <span
                    class="ml-2 cursor-pointer"
                    @click="files.splice(index, 1)"
                    style="font-size: 18px; line-height: 100%"
                >
                    &times;
                </span>
            </div>
        </div>
        <el-button
            class="m-2"
            type="primary"
            @click="$refs.fileInput.click()"
        >
            Добавить
        </el-button>
        <input
            @change="handleChange"
            ref="fileInput"
            type="file"
            multiple
            name="designListFiles[]"
            v-show="false"
        />
    </div>
</template>

<script>
import { Button } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    name: 'upload',
    components: {
        'el-button': Button
    },
    data() {
        return {
            files: []
        }
    },
    methods: {
        async handleChange(event) {
            if (event.target.files.length > 0) {
                for (let file of event.target.files) {
                    let res = await this.upload(file);

                    if (res) {
                        this.files.push(res);
                    }
                }

                this.$emit('getFiles', this.files);
            }
        },
        async upload(file) {
            return await BX_POST('vaganov:design.list', 'upload', {file: file}).then(r => {
                return r;
            }).catch(e => {
                console.log(e);
            });
        }
    }
}
</script>

<style scoped>

</style>