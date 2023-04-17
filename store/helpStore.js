import store from './store'

const numFormat = (sum,fixed) => {
    return sum
        ? fixed
            ? sum.toFixed(2).toString()
            : sum.toString().split('.')[1] && sum.toString().split('.')[1].length > 2
                ? sum.toFixed(2).toString()
                : sum.toString()
        : null
}

export const getForm = (attribute) => {

    return {
        get: function () {
            return store.getters['form/GET_VALUE'](attribute)
        },
        set: function(value){
            store.commit('form/SET_VALUE', {attribute,value})
        }
    }
}
export const getFormJson = (attribute) => {

    return {
        get: function () {
            let json = store.getters['form/GET_VALUE'](attribute)
            if (json){
                return JSON.parse(json)
            }
            return undefined
        },

    }
}
export const getFormAttribute = (attribute) => {

    return {
        get: function () {
            return store.getters['form/GET_ATTRIBUTE'](attribute)
        },
        set: function(value){
            store.commit('form/SET_ATTRIBUTE', {attribute,value})
        }
    }
}


export const getFormInt = (attribute) => {

    return {
        get: function () {

            let value = store.getters['form/GET_VALUE'](attribute)

            value = parseFloat(value) || 0;

            return value
        },
        set: function(value){
            value = numFormat(value)

            store.commit('form/SET_VALUE', {attribute,value})
        }
    }
}