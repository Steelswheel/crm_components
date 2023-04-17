<template>
    <div class="add-image">
        <div v-show="file && file.url">
            <a :href="file.url">
                {{ file.name }}
            </a>
            <div class="d-flex justify-content-center m-2">
                <el-button
                    @click="addFile"
                    size="mini"

                    type="primary"
                >
                    Изменить
                </el-button>
                <el-button
                    @click="deleteImage"
                    size="mini"
                    type="primary"
                >
                    Удалить
                </el-button>
            </div>
        </div>
        <div
            class="add-image-wrap"
            v-show="file && !file.url"
        >
            <el-button
                @click="addFile"
                size="mini"
            >
                Добавить
            </el-button>
            <input
                v-show="false"
                type="file"
                ref="fileInput"
                name="add-image-input"
                accept="image/*"
                @change="handleChange"
            />
        </div>
    </div>
</template>

<script>
import { Button } from 'element-ui';
import { BX_POST } from '@app/API';

export default {
    name: 'promotions-add-image',
    props: {
        id: String,
        file: {}
    },
    components: {
        'el-button': Button
    },
    data() {
        return {
            fileName: '',
            fileData: false,
            uploadedFile: this.file
        }
    },
    methods: {
        addFile() {
            this.$refs.fileInput.click();
        },
        handleChange(event) {
            if (event.target.files.length > 0) {
                this.fileData = event.target.files[0];
            }

            this.upload();
        },
        upload() {
            const data = new FormData();
            data.append('promotions-add-image', this.fileData, this.fileData.name);

            BX_POST('vaganov:lk', 'addImagePromotions', {file: this.fileData, id: this.id}).then(r => {
                if (r) {
                    this.$emit('addImage', this.id, r);
                }
            }).catch(e => {
                console.log(e);
            });
        },
        deleteImage() {
            BX_POST('vaganov:lk', 'deletePromotionsImage', {id: this.id, fileId: this.file.id}).then(() => {
                this.$emit('deleteImage', this.id);
            }).catch(e => {
                console.log(e);
            });
        }
    }
}
</script>

<style>

</style>