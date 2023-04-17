<template>
    <div class="epd-list-modal">
        <button
            class="edp-list-report m-2"
            @click="isVisible = !isVisible"
        >
            Создать ЭДА
        </button>
        <el-dialog
            title="Создать ЭДА"
            :visible.sync="isVisible"
            width="30%"
            :append-to-body="true"
            :close-on-click-modal="false"
            @close="name = ''"
        >
            <div class="mb-2">
                <b>ФИО агента:</b>
            </div>
            <el-input v-model="name" />
            <el-button
                type="primary"
                @click="create"
                class="mt-4"
                :loading="isLoading"
            >
                Создать
            </el-button>
        </el-dialog>
    </div>
</template>

<script>
import { Dialog, Input, Button } from 'element-ui';
import { BX_POST, REFRESH_TABLE } from '@app/API';

export default {
    name: 'modal',
    components: {
        'el-dialog': Dialog,
        'el-input': Input,
        'el-button': Button
    },
    data() {
        return {
            isVisible: false,
            isLoading: false,
            name: ''
        }
    },
    methods: {
        create() {
            this.isLoading = true;

            BX_POST('vaganov:edp.show', 'createEda', {name: this.name})
            .then(() => {
                this.isLoading = false;
                this.isVisible = false;
                REFRESH_TABLE('edp-list');
            })
            .catch((error) => console.log(error));
        }
    }
}
</script>

<style scoped>

</style>