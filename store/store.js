import Vue from 'vue'
import Vuex from 'vuex'
import { getField, updateField } from 'vuex-map-fields';

import form from './form';

Vue.use(Vuex);
const store = new Vuex.Store({
    state: {
        count: 1
    },
    getters: {
        getField
    },
    mutations: {
        updateField
    },
    actions: {},
    modules: {
        form,
    }
});

export default store

