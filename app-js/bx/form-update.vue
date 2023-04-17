<template>
    <div class="form-update">
        <div v-if="loading">
            <div v-if="isLoadingError" class="bg-white py-3 px-3 text-danger" v-html="loadingErrorText"></div>
            <div v-else>
                LOADING
            </div>
        </div>
        <div v-else>
            <slot name="layout"
                :values="values"
                :defaultValues="defaultValues"
                :attributes="attributes"
                :loading="loading"
                :stages="stages"
                :entities="entities"
                :isAdmin="isAdmin"
                :isFin="isFin"
            ></slot>
        </div>
        <template v-if="!loading">
            <template
                v-for="(attribute, alias) in attributes"
                v-if="attribute.type && attribute.render !== false"
            >
                <div :ref="alias" v-show="attribute.show !== false" :key="alias">
                    <wrap-input
                        v-model="values[alias]"
                        :alias="alias"
                        :attribute="attribute"
                        :attributes="attributes"
                        :size="attribute.size"
                        :type="attribute.type"
                        :styleGroup="attribute.styleGroup"
                        :methodUpdate="attribute.methodUpdate"
                        ref="wrapInput"
                    />
                </div>
            </template>
        </template>

        <div v-if="showBtn" class="mt-3  form-update__panel-btn text-center">
            <div v-if="errorText" class="text-center text-danger mb-4" v-html="errorText"></div>

            <el-button @click="save" :loading="isLoadingUpdate" type="primary" >Сохранить</el-button>

            <el-button
                @click="nextProcess"
                v-if="isNextStage && nextStageId"
                :loading="isLoadingUpdateNextStage"
            >
                <span class="mr-2">На стадию</span>
                <span class="">{{ stages.find(i => i.id === nextStageId).label }}</span>
            </el-button>

            <el-button @click="setDefaultValues" type="default">Отмена</el-button>
        </div>
    </div>
</template>

<script>
import { BX_POST } from '@app/API';
import { Button } from 'element-ui';
import wrapInput from '@app/input/wrap-input';
import { isEqual, cloneDeep, isEmpty } from 'lodash';

export default {
    inheritAttrs: false,
    name: 'form-update',
    components: {
        wrapInput,
        'el-button': Button,
    },
    props: {
        id: Number,
        model: String,
        methodGetAttributes: {
            type: String,
            default: 'attributes'
        },
        methodGetValues: {
            type: String,
            default: 'values'
        },
        methodUpdate: {
            type: String,
            default: 'update'
        },
        updateAttributes: {},
        value: {},
    },
    data(){
        return {
            showBtn: false,
            isLoadingError: false,
            loadingErrorText: "",
            attributes: {},
            values: {},
            defaultValues: {},
            loading: true,
            isLoadingUpdate: false,
            isLoadingUpdateNextStage: false,
            errorText: false,
            stages: {},
            isNextStage: false,
            entities: {},
            isAdmin: false,
            isFin: false
        }
    },
    computed: {
        nextStageId() {
            if (isEmpty(this.stages)) {
                return false
            }

            let stageKey = this.stages.findIndex(i => i.id === this.defaultValues.STAGE_ID);

            if (!this.stages[stageKey + 1]) {
                return false
            }

            if (!this.attributes.STAGE_ID.items.map(i => i.id).includes(this.stages[stageKey + 1].id)) {
                return false
            }

            return  this.stages[stageKey + 1].id
        }
    },
    watch: {
        value() {
            if (!isEqual(this.values,this.value)) {
                this.values = this.value;
            }
        },
        updateAttributes:{
            deep: true,
            handler() {
              this.attributes = cloneDeep(this.updateAttributes);
            }
        },
        values: {
            deep: true,
            handler() {
                this.$emit('value', this.values);
                this.diff();
            }
        }
    },
    mounted() {
        if (this.id) {
            this.load();
        } else {
            this.loadAttributes();
        }
    },
    methods: {
        loadAttributes() {
            BX_POST(this.model, this.methodGetAttributes, {id: this.id}).then(data => {
                this.setDataForm(data.value,data.attributes, {}, {})
            })
        },
        setDataForm(values,attributes,stages,entities, isAdmin,  isFin) {
            this.setAttributes(attributes);
            this.setValues(values);
            this.defaultValues = cloneDeep(values);
            this.entities = cloneDeep(entities);
            this.stages = cloneDeep(stages);
            this.isAdmin = isAdmin;
            this.isFin = isFin;
            this.$emit('attributes',attributes); // Старое значение поправить в эдп
            this.$emit('data', values); // Старое значение поправить в эдп
            this.$emit('value',this.values);
            this.loading = false;
            this.$nextTick(() => {
                this.setField();
            })
        },
        async load(){
            try {
                let { attributes, values, stages, entities, error, message, isAdmin , isFin} = await BX_POST(this.model, this.methodGetValues, {id: this.id})

                if (error) {
                    this.isLoadingError = true;
                    this.loadingErrorText = message
                    return false
                }

                this.setDataForm(values,attributes,stages,entities, isAdmin, isFin)
            } catch (e) {
                this.isLoadingError = true;
                this.loadingErrorText = e.status
            }
        },
        setField() {
            let attributes = Object.keys(this.attributes);

            attributes.forEach(attribute => {
                let el = this.$el.querySelector(`.attribute--${attribute}`);

                if (el) {
                    if (this.$refs[attribute]) {
                        el.appendChild(this.$refs[attribute][0]);
                    } else {
                        console.log(attribute);
                    }
                }
            })

            attributes.forEach(attribute => {
                let el = this.$el.querySelector(`[data-old-value="${attribute}"] [data-input-alias]`);

                if (el) {
                    let val = this.values[el.dataset.inputAlias];

                    if (!isEmpty(val)) {
                        this.$refs[attribute][0].style.display = 'none';
                    } else {
                        el.appendChild(this.$refs[attribute][0]);
                    }
                }
            });

            this.loading = false;
        },
        diff() {
            let diff = {};

            for (let item in this.values) {
                if (!isEqual(this.values[item], this.defaultValues[item])) {
                    diff[item] = this.values[item];
                }
            }

            this.isNextStage = Object.keys(diff).length > 0;
            this.showBtn = Object.keys(diff).length > 0;

            return diff;
        },
        nextProcess() {
            this.isLoadingUpdateNextStage = true;
            this.values.STAGE_ID = this.nextStageId;
            this.save();
        },
        save() {
            this.isLoadingUpdate = true;
            this.errorText = false;

            BX_POST(this.model, this.methodUpdate, {id: this.id, JSON: JSON.stringify(this.diff())})
            .then(data => {
                this.isLoadingUpdate = false
                this.showBtn = false;
                this.setAttributes(data.attributes);
                this.setValues(data.values);
                this.entities = cloneDeep(data.entities);
                this.stages = cloneDeep(data.stages);
                this.reset();
                this.$emit('save', { attributes: data.attributes, values: data.values });
                this.defaultValues = cloneDeep(data.values);
                this.defaultValues = cloneDeep(data.values);
            }).catch(error => {
                this.isLoadingUpdate = false;
                this.isLoadingUpdateNextStage = false;
                this.errorText = error;
            });
        },
        setValues(values){
            this.values = values;
            this.$emit('update:updateValues', values);
        },
        setAttributes(attributes) {
            this.attributes = attributes;
            this.$emit('update:updateAttributes', attributes);
        },
        reset() {
            this.$emit('reset',this.values);
            for(let input of this.$refs.wrapInput)
                if(input.reset)
                    input.reset()
        },
        setDefaultValues() {
            this.values = cloneDeep(this.defaultValues);
            this.showBtn = false;
            this.errorText = false;
            this.reset();
        }
    }
}
</script>

<style scoped>
>>>.el-loading-mask {
  background-color: transparent!important;
}
</style>

