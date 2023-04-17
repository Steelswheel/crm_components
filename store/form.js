/*eslint-disable*/
/*import API from 'js/API'
import qs from 'qs';
import modals from 'js/modals'
import helpDom from 'js/helpDom';
import { difference } from 'lodash';*/

import { BX_POST } from '@app/API'
import { cloneDeep, isEqual, isEmpty } from "lodash";
import vue from 'vue'

const diff = function(a,b){
    let diff = {}
    for (let i in a)
        if (!isEqual(a[i], b[i]))
            diff[i] = a[i]
    return diff
}

const form = {
    namespaced: true,
    state: {
        model: '',
        methodGetValues: 'values',
        methodGetAttributes: 'attributes',
        methodUpdate: 'update',
        count: 888,
        id: undefined,
        isShowBtn: false,

        attributes: {},
        values: {},
        defaultValues: {},
        isLoading: true,
        isPost: false,
        isLoadingUpdate: false,
        isLoadingUpdateNextStage: false,
        isLoadingError: false,
        loadingErrorText: "",
        errorText: false,
        errorRequired: {},
        isError: false,
        stages: false,
        isNextStage: false,
        entities: {},
        stageHis: undefined,
        tasks: undefined,
        finance: undefined,
        programs: undefined,
        isEconomist: undefined,
        interestPaymentResponsible: undefined,
        nextStageId: undefined,
        isLoadingNextStage: false,
        tranche: undefined,
        isOpenStages:[],
        stagesNext: [],
        rulesStage: [],
        isBtnLucked: false,
        isAdmin: false,
        userId: undefined,
        rule: [],
        allEmails: [],
        nextError: {},
        stagesSettings: [],
        history:[],
    },

    getters: {
        GET_HISTORY: state => state.history,
        GET_VALUE: state => attribute => state.values[attribute] === null
            ? undefined
            : state.values[attribute],
        GET_ALL_EMAILS: state => state.allEmails,
        GET_RULE: state => state.rule,
        GET_ATTRIBUTE: state => attribute => state.attributes[attribute],
        IS_LOADING: state => state.isLoading,
        IS_POST: state => state.isPost,
        IS_ADMIN: state => state.isAdmin,
        USER_ID: state => state.userId,
        IS_LOADING_UPDATE: state => state.isLoadingUpdate,
        IS_LOADING_UPDATE_NEXT_STATE: state => state.isLoadingUpdateNextStage,
        GET_ATTRIBUTES: state => state.attributes,
        GET_VALUES: state => state.values,
        GET_DEFAULT_VALUE: state => state.defaultValues,
        // GET_ENTITIES: state => state.entities,
        GET_STAGES: state => state.stages,
        IS_SHOW_BTN: state => state.isShowBtn,
        GET_STAGES_HIS: state => state.stageHis,
        GET_TASKS: state => state.tasks,
        GET_FINANCE: state => state.finance,
        GET_PROGRAMS: state => state.programs,
        GET_IS_ECONOMIST: state => state.isEconomist,
        GET_INTEREST_PAYMENT_RESPONSIBLE: state => state.interestPaymentResponsible,
        GET_NEXT_STAGE_ID: state => state.nextStageId,
        GET_TRANCHE: state => state.tranche,
        GET_STAGES_NEXT: state => state.stagesNext,
        GET_RULES_STAGE: state => state.rulesStage,
        IS_OPEN_STAGE: state => stage => state.isOpenStages.includes(stage),
        GET_IS_BTN_LUCKED: state => state.isBtnLucked,
        GET_ERROR_TEXT: state => state.errorText,
        GET_ERROR_REQUIRED: state => state.errorRequired,
        IS_ERROR: state => state.isError,
        GET_NEXT_ERROR: state => state.nextError,
        GET_STAGES_SETTINGS: state => state.stagesSettings,
    },
    mutations: {
        SET_TASKS(state,tasks){

            state.tasks.list = tasks
        },

        SET_IS_BTN_LUCKED(state,isBtnLucked){
            state.isBtnLucked = isBtnLucked
        },
        OPEN_STAGE_ALL(state){
            state.isOpenStages = state.stages.map(i => i.id)
        },
        CLOSE_STAGE_ALL(state){
            state.isOpenStages = []
        },
        OPEN_STAGE(state,stage){
            if (state.isOpenStages.indexOf(stage) === -1)
                state.isOpenStages.push(stage)
        },
        CLOSE_STAGE(state,stage){
            let key = state.isOpenStages.indexOf(stage)
            if (key >= 0 ) state.isOpenStages.splice(key,1)

        },
        SET_VALUES_OBJ(state,values){

            for (let key in values){
                state.values[key] = values[key] === null || values[key] === undefined
                    ? ''
                    : values[key].toString()
            }

            // state.values = { ...state.values, ...values}
            let diffValues = diff(state.values, state.defaultValues)
            state.isShowBtn = !isEmpty(diffValues)
        },
        SET_VALUE(state,{attribute, value}) {
            state.values[attribute] = value
            let diffValues = diff(state.values, state.defaultValues)
            state.isShowBtn = !isEmpty(diffValues)
        },
        SET_ATTRIBUTES(state, attributes){
            state.attributes = attributes
        },
        SET_ATTRIBUTE(state,{attribute, value}){
            vue.set(state.attributes,attribute,value)
            // state.attributes[attribute] = value
        },
        SET_ATTRIBUTE_CLASS(state,{ attribute, className }){
            state.attributes[attribute]['className'] = className
        },
        SET_ATTRIBUTE_SETTINGS(state,{ attribute, settings }){
            // console.log(attribute, settings);
            vue.set(state.attributes[attribute],'settings',settings)
            // state.attributes[attribute]['settings'] = settings
        },
        SET_ATTRIBUTE_EDIT(state,{ attribute, isEdit }){
            if (state.attributes[attribute]['rule']){
                if (typeof isEdit === 'boolean'){
                    state.attributes[attribute]['methodUpdate'] = isEdit ? 'follow' : 'no'
                }else {
                    state.attributes[attribute]['methodUpdate'] = isEdit
                }
            }
        },
        SET_ATTRIBUTE_EDIT_OBJ(state,attributesEdit){
            // console.log(attributesEdit,'SET_ATTRIBUTE_EDIT_OBJ');
            for (let attribute in attributesEdit){
                if (state.attributes[attribute]['rule']){
                    if (typeof attributesEdit[attribute] === 'boolean'){
                        state.attributes[attribute]['methodUpdate'] = attributesEdit[attribute] ? 'follow' : 'no'
                    }else {
                        state.attributes[attribute]['methodUpdate'] = attributesEdit[attribute]
                    }
                }else {
                    console.log('НЕТ ПРАВ',attribute);
                }
            }

        },
        SET_DATA(state, { user_id, is_admin,rule, values, attributes, stages, programs, isEconomist, interestPaymentResponsible, entities, error, message, details }){

            state.errorRequired = {}
            state.isError = false
            state.errorText = ''
            if (error){
                state.isError = true
                state.errorText = message
                state.isLoading = false
                /// state.isShowBtn = false
                state.isLoadingUpdate = false
                state.isLoadingUpdateNextStage = false
                state.errorRequired = details

                return false
            }

            state.userId = user_id
            state.isAdmin = is_admin
            state.rule = rule ? rule : {}
            state.values = cloneDeep(values)
            state.defaultValues = cloneDeep(values)
            state.attributes = cloneDeep(attributes)
            state.stages = cloneDeep(stages)
            state.programs = cloneDeep(programs)
            state.isEconomist = cloneDeep(isEconomist)
            state.interestPaymentResponsible = cloneDeep(interestPaymentResponsible)

            if (entities) {
                state.stageHis = entities.stageHis
                state.tasks = entities.tasks
                state.finance = entities.finance
                state.tranche = entities.tranche
                state.allEmails = entities.allEmails
                state.history = entities.history


                if (!['C8:LOSE', 'C14:LOSE'].includes(state.defaultValues.STAGE_ID)){

                    let key = state.stages.findIndex(i => i.id === state.defaultValues.STAGE_ID)
                    let nextStageId = state.stages[key+1].id
                    if (state.attributes.STAGE_ID.items.map(i => i.id).includes(nextStageId)){
                        state.nextStageId = nextStageId
                    }else{
                        state.nextStageId = undefined
                    }
                }

                state.stagesNext = entities.stagesNext
                state.rulesStage = entities.rulesStage
                if (entities.nextError && entities.nextError.error && entities.nextError.details){
                    state.nextError = entities.nextError.details
                }else{
                    state.nextError = {}
                }

                if(entities.stagesSettings){
                    state.stagesSettings = entities.stagesSettings
                }

            }

            state.isLoading = false
            state.isShowBtn = false
            state.isLoadingUpdate = false
            state.isLoadingUpdateNextStage = false
        },
        SET_MODEL(state,model){
            state.model = model
        },
        INIT_DATA(state, { model, methodGetValues, methodGetAttributes, methodUpdate }){
            state.model = model
            state.methodGetValues = methodGetValues
            state.methodGetAttributes = methodGetAttributes
            state.methodUpdate = methodUpdate
        },


    },
    actions: {

        async FETCH({ state, commit },id){
            state.isLoading = true
            state.id = id

            let data = id
                ? await BX_POST(state.model, state.methodGetValues, { id })
                : await BX_POST(state.model, state.methodGetAttributes)

            commit('SET_DATA', data)
        },
        FETCH_ATTRIBUTES({state}){

        },

        post_save({ state, commit },diffValue){

            state.isPost = true

            BX_POST(state.model, state.methodUpdate, {id: state.id || '', JSON: JSON.stringify(diffValue)})
                .then(data => {
                    if (!state.id){ // если страница новая то добавляем ID
                        history.pushState({}, "", `?deal_id=${data.id}&show`)
                        state.id = data.id
                    }
                    commit('SET_DATA', data)
                    state.isPost = false
                })
        },
        SAVE({ state, dispatch }){

            state.isLoadingUpdate = true
            let diffValue = diff(state.values, state.defaultValues);
            dispatch('post_save',diffValue)
        },

        NEXT_STAGE({ state, dispatch, commit }, nextStageId){

            state.isLoadingUpdateNextStage = true
            let diffValue = diff(state.values, state.defaultValues);
            diffValue['STAGE_ID'] = nextStageId  !== 'NEXT' ? nextStageId : state.nextStageId
            dispatch('post_save',diffValue)
        },

        RESET_VALUES({ state }){
            state.values = cloneDeep(state.defaultValues)
            state.isShowBtn = false
            state.errorRequired = {}
            state.errorText = ''
        },

    },

};

export default form


