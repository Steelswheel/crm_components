<template>
    <div>


        <v-select
            :placeholder="placeholder"
            :class="className"
            :filterable="false"
            :options="options"
            @search="onSearch"
            :value="inValue && inValue.label === '' ? null : inValue "
            @input="setInValue"
            @search:focus="onFocus"
            ref="vSelect"
        >

            <template slot="no-options">
                пусто
            </template>
            <template #option="{label, data}" >
                <div style="margin: 0">{{ label }}
                    <template v-if="data">
                        <span class="badge badge-danger" v-if="data.state.status !== 'ACTIVE'">{{data.state.status}}</span>
                    </template>

                </div>
                <template v-if="data">
                    <div class="small"> {{ data.address.value }} </div>
                </template>

            </template>


        </v-select>

        <!--       {{item.company.data.state.status}}  -->


        <div class="statusRow" v-if="errorText.length > 0">
            <div class="statusRow__title colorRed">
                укажите: {{errorText.join(', ')}}
            </div>
        </div>

    </div>
</template>

<script>

import API from '@app/API'
import vSelect from 'vue-select'
import { debounce, cloneDeep } from 'lodash'

export default {
    inheritAttrs: false,
    name: "dadata-companyDadata",
    components: {vSelect},
    props: {

        placeholder: {
            type: String,
            default: ""
        },
        isFullAddress: {
            type: Boolean,
            default: true
        },
        locations: {
            type: Object,
            default: () => ({})
        },
        className: {
            type: String,
            default: '',
        },
        value: {
            type: Object,
            default: () => ({label: '', name: ''})
        }
    },
    data() {
        return {
            inValue: cloneDeep(this.value),
            options: [this.inValue],
            searchText: '',
            errorText: [],
        }
    },


    methods: {

        setInValue(val) {
            this.inValue = val
            this.$emit('input', val);

            this.searchText = (val === null) ? '' : val.label;


        },

        onFocus() {
            this.errorText = [];
            this.$refs.vSelect.search = this.searchText;
        },
        onSearch(search, loading) {
            loading(true);
            this.search(loading, search);
        },
        search: debounce(async function (loading, search) {
            if (search) {
                const r = await API.company(search)


                this.options = r.data.suggestions.map(({value: label, data}) => ({label, data}));
            }
            loading(false);
        }, 350),
        isCompleted() {
            if (this.inValue.label.length > 0 && this.errorText.length === 0) {
                return true;
            }
            return false;
        }
    }

}
</script>

<style scoped>

</style>
