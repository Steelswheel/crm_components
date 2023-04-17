<template>
    <div class="form-update">




        <div v-if="IS_LOADING ">
            <div v-if="isLoadingError" class="bg-white py-3 px-3 text-danger" v-html="loadingErrorText"></div>
            <div v-else>
                LOADING
            </div>
        </div>
        <div v-else>
            <slot name="layout"></slot>
        </div>


        <template v-if="!IS_LOADING">
            <template
                v-for="(attribute, alias) in GET_ATTRIBUTES"
                v-if="attribute.type && attribute.render !== false"
            >
                <div :ref="alias" v-show="attribute.show !== false" :key="alias">

                    <wrapInputV

                        :alias="alias"
                        :attribute="attribute"

                        :size="attribute.size"
                        :type="attribute.type"
                        :styleGroup="attribute.styleGroup"
                        :methodUpdate="attribute.methodUpdate"
                        ref="wrapInput"
                    />
                </div>
            </template>
        </template>

        <div v-if="IS_SHOW_BTN" class="mt-3  form-update__panel-btn text-center">
            <div v-if="errorText" class="text-center text-danger mb-4" v-html="errorText"></div>
            <button @click="SAVE" class="ui-btn ui-btn-success" :class="{'ui-btn-clock': IS_LOADING_UPDATE}">Сохранить</button>
<!--            <button
                @click="nextProcess" v-if="isNextStage && nextStageId"
                class="ui-btn ui-btn-success"
                :class="{'ui-btn-clock': IS_LOADING_UPDATE_NEXT_STATE}"
            >
                <span class="mr-2">На стадию</span>
                <span class="uiBtnBlack">{{ stages.find(i => i.id === nextStageId).label }}</span>
            </button>-->


            <button @click="RESET_VALUES" class="ui-btn ui-btn-link">Отмена</button>
        </div>



    </div>
</template>

<script>
// import {BX_POST/*, BX_REST*/} from '@app/API'
// import {map} from 'lodash'
// import { aliasFormat, titleAndTypeHelper } from '@app/helper'
import wrapInputV from '@app/input/wrap-input-v'
import { mapState, mapGetters, mapActions, mapMutations } from "vuex";
// import { getForm } from '@app/../store/helpStore'
import { /*isEqual, cloneDeep,*/ isEmpty } from "lodash";
export default {
    inheritAttrs: false,
    name: "form-vuex-v",
    components: {
        wrapInputV
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


            defaultValues: {},

            errorText: false,
            stages: {},
            isNextStage: false,
            entities: {}
        }
    },
    computed: {

        ...mapGetters('form',[
            'IS_LOADING',
            'IS_LOADING_UPDATE',
            'IS_LOADING_UPDATE_NEXT_STATE',
            'GET_VALUES',
            'GET_ATTRIBUTES',
            'IS_SHOW_BTN',
            'IS_LOADING_UPDATE',
        ]),
        ...mapState('form',[
            'count',
        ]),

    },
    watch: {

    },
    mounted() {
        this.SET_MODEL('vaganov:edz.show')
        this.FETCH(this.id).then(() => {
          this.$nextTick(() => {
            this.setField()
          })
        })
    },
    methods: {
        ...mapActions('form', [
            'FETCH',
            'SAVE',
            'RESET_VALUES'
        ]),
        ...mapMutations('form', [
            'INIT_DATA',
            'SET_VALUE',
        ]),
        /*loadAttributes(){
            BX_POST(this.model, this.methodGetAttributes, {id: this.id}).then(data => {
                this.setDataForm(data.value,data.attributes, {}, {})
            })
        },
        setDataForm(values,attributes,stages,entities){

            this.setAttributes(attributes)
            this.setValues(values)
            this.defaultValues = cloneDeep(values)
            this.entities = cloneDeep(entities)
            this.stages = cloneDeep(stages)

            this.$emit('attributes',attributes) // Старое значение поправить в эдп
            this.$emit('data',values) // Старое значение поправить в эдп
            this.$emit("value",this.values)

            this.loading = false;
            this.$nextTick(() => {
                this.setField()
            })
        },
        async load(){
            try {
                let { attributes, values, stages, entities, error, message } = await BX_POST(this.model, this.methodGetValues, {id: this.id})

                if (error){
                    this.isLoadingError = true;
                    this.loadingErrorText = message
                    return false
                }
                this.setDataForm(values,attributes,stages,entities)

            }catch (e){
                this.isLoadingError = true;
                this.loadingErrorText = e.status
            }

        },*/
        setField(){
            let attributes = Object.keys(this.GET_ATTRIBUTES)
            attributes.forEach(attribute => {
                let el = this.$el.querySelector(`.attribute--${attribute}`)
                if (el) {
                    if (this.$refs[attribute]){
                        el.appendChild(this.$refs[attribute][0])
                    }else{
                        console.log(attribute);
                    }

                }
            })

            attributes.forEach(attribute => {
                let el = this.$el.querySelector(`[data-old-value="${attribute}"] [data-input-alias]`)
                if (el ){

                    let val = this.GET_VALUES[el.dataset.inputAlias]

                    if (!isEmpty(val)){
                        this.$refs[attribute][0].style.display = "none";
                    }else{
                        el.appendChild(this.$refs[attribute][0])
                    }

                }
            })

            this.loading = false;
        },
        /*
        nextProcess(){
            this.isLoadingUpdateNextStage = true
            this.values.STAGE_ID = this.nextStageId
            this.save()
        },
        save(){
            this.isLoadingUpdate = true
            this.errorText = false
            BX_POST(this.model, this.methodUpdate, {id: this.id, JSON: JSON.stringify(this.diff())})
                .then(data => {
                    this.isLoadingUpdate = false
                    this.showBtn = false;

                    this.setAttributes(data.attributes)
                    this.setValues(data.values)
                    this.entities = cloneDeep(data.entities)
                    this.stages = cloneDeep(data.stages)
                    this.reset()
                    this.$emit('save', { attributes: data.attributes, values: data.values })
                    this.defaultValues = cloneDeep(data.values)
                    this.defaultValues = cloneDeep(data.values)


                }).catch(error => {
                    this.isLoadingUpdate = false
                    this.isLoadingUpdateNextStage = false
                    this.errorText = error
                })

        },
        setValues(values){
            this.values = values
            this.$emit('update:updateValues', values)
        },
        setAttributes(attributes){
            this.attributes = attributes
            this.$emit('update:updateAttributes', attributes)
        },
        reset(){
            this.$emit('reset',this.values)
            for(let input of this.$refs.wrapInput)
                if(input.reset)
                    input.reset()
        },
        setDefaultValues(){
            this.values = cloneDeep(this.defaultValues)
            this.showBtn = false
            this.errorText = false
            this.reset()
        }*/

    }
}
</script>

