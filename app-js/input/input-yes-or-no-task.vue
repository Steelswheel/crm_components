<template>
    <div  v-if="isEdit">
        <el-radio v-model="inValue" label="Y" border size="small">
            {{ yes }}
        </el-radio>
<!--        <el-radio v-model="inValue" label="N" border size="small">
            {{ no }}
        </el-radio>-->

        <a href="#" @click.prevent="createTask"> {{ no }} </a>  <i v-if="isLoading" class="el-icon-loading"></i>


    </div>
    <div v-else>
        {{ getState }}
    </div>
</template>

<script>
import { getForm } from '@app/../store/helpStore'
import { BX_POST } from '@app/API'
import { Radio } from 'element-ui';
import {mapMutations} from "vuex";

export default {
    components: {
        'el-radio': Radio
    },
    inheritAttrs: false,
    name: 'input-yes-or-no-task',
    props: {
        value: [String, Number, Array],
        alias: String,
        title: String,
        stage: String,
        createBy: String,
        responsibleId: String,
        description: String,
        isEdit: {
            type: Boolean,
            default: true
        },
        yes: {
            type: String,
            default: 'Да'
        },
        no: {
            type: String,
            default: 'Нет'
        },
        isClickEdit: Boolean,
        view: {
            type: String,
            default: 'default'
        }
    },
    data() {
        return {
            inValue: this.value,
            isLoading: false,
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input', this.inValue)
            }
        }
    },
    computed: {
        DEAL_ID: getForm('DEAL_ID'),
        getState() {
            let state = '';
            switch (this.value) {
                case 'Y':
                    state = this.yes;
                    break;
                case 'N':
                    state = this.no;
                    break;
                default:
                    state = 'Не выбрано';
                    break;
            }

            return state;
        }
    },
    methods:{
        ...mapMutations('form', [
            'SET_TASKS',
        ]),
        createTask(){
            this.isLoading = true // $createBy,$responsibleId,$auditors,$dealId,$field,$title,$description
            BX_POST('vaganov:edz.show', 'createTask', {
                dealId: this.DEAL_ID,
                field: this.alias,
                title: this.title,
                description: this.description,
            })
            .then(r => {
                this.SET_TASKS(r)
            })
            .finally(() => {
                this.isLoading = false
            })
        }
    }
}
</script>
