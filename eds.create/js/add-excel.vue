<template>
  <div class="eds-create-component">
    <el-button
        @click="openInputFile"
        :loading="isLoading"
        class="mr-1"
        type="success"
        plain
        size="mini"
    >
      Добавить файл
    </el-button>
    <input v-show="false" @change="onLoadFiles" type="file" ref="fileUpload">
  </div>
</template>

<script>
import { BX_POST } from '@app/API'
import { Button } from 'element-ui';

export default {
  name: 'add-excel',
  components: {
    'el-button': Button
  },
  data() {
    return {
      isLoading: false,
      docs: [],
      isLoadingTranche: false,
      stages: {}
    }
  },
  methods: {
    loadFile(file) {
      this.isLoading = true;

      BX_POST('vaganov:eds.create', 'upload', {file: file})
      .then(fileTmp => {
        console.log(fileTmp);
      })
      .catch((e) => {
        console.log(e)
      });
    },
    onLoadFiles() {
      let filesObject = this.$refs.fileUpload.files

      if (filesObject && filesObject[0]) {
        this.loadFile(filesObject[0]);
      }
    },
    openInputFile() {
      this.$refs.fileUpload.click();
    }
  }
}
</script>