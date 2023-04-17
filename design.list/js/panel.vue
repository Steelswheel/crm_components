<template>
    <div class="add-task d-flex justify-content-center align-items-center">
        <a href="/docs/shared/path/МАКЕТЫ/ШАБЛОНЫ МАКЕТОВ/" target="_blank" class="design-list-button design-list-button-success">
            ШАБЛОНЫ МАКЕТОВ
        </a>
        <button class="design-list-button" data-role="add-task" @click="addTask">
            ЗАКАЗАТЬ МАКЕТ
        </button>

        <el-dialog
            title="Заказать макет"
            :visible.sync="task.popupVisible"
            :modal-append-to-body="false"
            @closed="task.added = false"
        >
            <div v-if="task.added" class="task-add-status">
                {{ task.addText }}
            </div>
            <el-form
                v-else
                id="task-form"
                :model="task.data"
                :rules="task.rules"
                ref="task"
            >
                <el-form-item label="Назначение макета" prop="type">
                    <el-select multiple v-model="task.data.type">
                        <el-option
                            v-for="item in task.options"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value"
                        >
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="Размеры макета (если есть)" prop="size">
                    <el-input v-model="task.data.size"></el-input>
                </el-form-item>
                <el-form-item label="Контакты для макета (название, адрес, телефон)" prop="contacts">
                    <el-input type="textarea" v-model="task.data.contacts" :autosize="{minRows: 2, maxRows: 4}"></el-input>
                </el-form-item>
                <el-form-item label="Партнер">
                    <el-select
                        v-model="task.partnerName"
                        placeholder="Партнер"
                        filterable
                        default-first-option
                        @change="setTitleEmails"
                    >
                        <el-option
                            v-for="(item, index) in partners.names"
                            :key="index"
                            :label="item.value"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="Email партнера (для отправки макета)" prop="email" v-if="task.emailSelectVisible">
                    <el-select
                        v-model="task.data.email"
                        placeholder="Email"
                        filterable
                        allow-create
                        default-first-option
                    >
                        <el-option
                            v-for="item in task.emails"
                            :key="item.value"
                            :label="item.value"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="Email для отправки макетов" v-else>
                    <el-input v-model="task.data.email"></el-input>
                </el-form-item>
                <el-form-item label="Файлы от партнера">
                    <upload @getFiles="getFiles" />
                </el-form-item>
                <el-form-item label="Дополнительная информация">
                    <el-input type="textarea" v-model="task.data.note" :autosize="{minRows: 2, maxRows: 4}"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click.prevent="onSubmit('task')">
                        ДОБАВИТЬ ЗАДАЧУ
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
import { BX_POST, REFRESH_TABLE } from '@app/API';
import { Dialog, Loading, Form, FormItem, Input, Button, Select, Option } from 'element-ui';
import upload from './upload';

export default {
    name: 'panel',
    components: {
        'el-dialog': Dialog,
        'el-form': Form,
        'el-input': Input,
        'el-button': Button,
        'el-form-item': FormItem,
        'el-select': Select,
        'el-option': Option,
        upload
    },
    data() {
        return {
            uploadedFiles: [],
            partners: [],
            task: {
                emailSelectVisible: false,
                partnerName: '',
                popupVisible: false,
                addText: '',
                added: false,
                data: {
                    title: 'Разработка макета',
                    type: [],
                    size: '',
                    contacts: '',
                    email: '',
                    note: '',
                    id: ''
                },
                emails: [],
                options: [
                    {
                        value: 'Для соцсети',
                        label: 'Для соцсети'
                    },
                    {
                        value: 'Для газеты',
                        label: 'Для газеты'
                    },
                    {
                        value: 'Визитка',
                        label: 'Визитка'
                    },
                    {
                        value: 'Вывеска',
                        label: 'Вывеска'
                    },
                    {
                        value: 'Баннер',
                        label: 'Баннер'
                    },
                    {
                        value: 'Листовка',
                        label: 'Листовка'
                    },
                    {
                        value: 'Буклет',
                        label: 'Буклет'
                    }
                ],
                rules: {
                    type: [
                        {
                            type: 'array',
                            required: true,
                            message: 'Выберите назначение',
                            trigger: 'change'
                        }
                    ],
                    email: [
                        {
                            required: true,
                            message: 'Поле не заполнено',
                            trigger: 'change'
                        }
                    ],
                    contacts: [
                        {
                            required: true,
                            message: 'Поле не заполнено',
                            trigger: 'blur'
                        }
                    ]
                },
            }
        }
    },
    methods: {
        getFiles(files) {
            this.uploadedFiles = files;
        },
        setTitleEmails() {
            this.task.data.title = 'Макет для ' + this.task.partnerName;

            this.task.emailSelectVisible = true;

            let elem = this.partners.emails.find(i => i.NAME === this.task.partnerName);

            elem['EMAIL'].forEach(e => {
                this.task.emails.push({'value': e});
            });

            this.task.data.id = elem['ID'];
        },
        addTask() {
            this.task.popupVisible = true;
        },
        onSubmit(formName) {
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    let loadInstance = Loading.service({
                        target: '#task-form',
                        fullscreen: false
                    });

                    let data = {
                        title: this.task.data.title,
                        type: '',
                        size: this.task.data.size,
                        contacts: this.task.data.contacts,
                        email: this.task.data.email,
                        note: this.task.data.note,
                        id: this.task.data.id
                    };

                    this.task.data.type.forEach(item => {
                        if (data.type.length > 0) {
                            data.type += ', ' + item;
                        } else {
                            data.type += item;
                        }
                    });

                    if (this.uploadedFiles.length > 0) {
                        data.files = JSON.stringify(this.uploadedFiles);
                    }

                    BX_POST('vaganov:design.list', 'addNewTask', data).then((response) => {
                        loadInstance.close();
                        this.task.added = true;
                        this.task.addText = response;
                        this.$refs['task'].resetFields();
                        REFRESH_TABLE('design-list');
                    }, (error) => {
                        loadInstance.close();
                        this.task.added = true;
                        this.task.addText = error;
                    });
                } else {
                    return false;
                }
            });
        }
    },
    mounted() {
        BX_POST('vaganov:design.list', 'getPartnersData').then((response) => {
            this.partners = response;
        }, (error) => {
            console.log(error);
        });
    }
}
</script>

<style scoped>
    .add-task .el-form-item__label {
        display: block;
        text-align: left;
        float: unset;
    }
    .add-task .el-checkbox-group, .el-radio-group {
        display: flex;
        flex-direction: column;
    }
    .add-task .el-form-item__label {
        line-height: 20px;
    }

    .add-task .el-dialog {
        max-width: 405px;
    }

    .add-task .task-add-status {
        text-align: center;
        font-size: 14px;
        line-height: 100%;
        font-weight: bold;
    }

    .add-task .el-dialog .el-button {
        display: flex;
        margin: 0 auto;
    }
</style>