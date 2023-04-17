<template>
    <div>


        <div v-if="isEdit" class="company-list mt-2">


            <div class="company-list__label">
                <label>Имя компании <span @click="addCompany" class="add-dotted">Добавить компанию <b-icon icon="plus"></b-icon></span></label>
                <label>Тариф </label>
                <label>Коммисия</label>
            </div>

            <div class="company-list__item__form" v-for="(item, key) in inValue" :key="key" >
                <div>

<!--                    <input-company class="company-list__item__company v-select-sm"  v-model="item.company"/>-->
                    <input v-model="item.company.label" class="company-list__item__company form-control form-control-sm" type="text">
                    <span @click="remove(key)" class="add-dotted add-dotted--remove">Удалить компанию <b-icon icon="trash"></b-icon></span>
                </div>
                <div>

                    <div>
                        <div class="company-list__item__form__rate" v-for="(rate, keyRate) in item.rates" :key="keyRate">
                            <div><input v-model="rate.name" class="form-control form-control-sm" type="text"></div>
                            <div><input v-model="rate.price" class="form-control form-control-sm" type="text"></div>
                            <div @click="removeRate(key,keyRate)" class="crm-btn-close crm-btn-close-sm"></div>

                        </div>

                    </div>
                    <span  @click="addRate(key)" class="add-dotted">Добавить тариф</span>
                </div>
            </div>

<!--            <span v-if="!inValue.length" @click="addCompany" class="add-dotted">Добавить компанию</span>-->
        </div>
        <div v-else :class="{'click-edit': isClickEdit}">

            <table class="table table-sm mb-0">

                <thead v-if="inValue.length">
                    <tr>
                        <th>Имя компании</th>
                        <th>Тариф</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, key) in inValue"  v-if="item.company.label" :key="key" >
                        <td>
                            <div>{{item.company.label}}</div>
                            <span class="small" v-if="item.company.data">{{item.company.data.address.value}}</span>
                        </td>
                        <td>
                            <div v-for="(rate, key) in item.rates" v-if="rate.name" :key="key">{{rate.name}}
                                <b>{{rate.price}} </b><span v-if="rate.price">₽</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
<!--            <div class="row" v-if="inValue[0].company.label">
                <div class="col-md-6"><label for="">Имя компании</label></div>
                <div class="col-md-6"><label for="">Тариф</label></div>
            </div>
            <div v-for="(item, key) in inValue"  v-if="item.company.label" :key="key">
                <div class="row">
                    <div class="col-md-6">

                        <div>{{item.company.label}}</div>
                        <span class="small">{{item.company.data.address.value}}</span>
                    </div>
                    <div class="col-md-6">
                        <div v-for="(rate, key) in item.rates" v-if="rate.name" :key="key">{{rate.name}}
                            <b>{{rate.price}} </b><span v-if="rate.price">₽</span>
                        </div>
                    </div>
                </div>
            </div>-->

        </div>
    </div>
</template>

<script>
import {cloneDeep, isEqual} from 'lodash'
// import inputCompany from './input-company'
export default {
    inheritAttrs: false,
    name: "input-company-list",
    components: {
       // inputCompany
    },
    props: {
        value: {
            type: Array,
            default: () => ([])
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
    },
    data(){
        return {
            //inValue: [],
            inValue: cloneDeep(this.value),
            // inValue: [{company:{}, rates: [{name: 'Деньги сразу', price: ''}]}],
            objectAdd: {company:{}, rates: [{name: 'Деньги сразу', price: ''}]},
            objectAddRate: {name: '', price: ''}
        }
    },
    watch: {
        inValue: {
            deep: true,
            handler() {
                this.$emit('input', this.inValue)
            }
        },
        value(){
            if (!isEqual(this.value, this.inValue)){
                this.inValue = cloneDeep(this.value)
            }
        },
    },
    mounted(){

    },
    methods: {
        addRate(key){
            this.inValue[key].rates.push({...this.objectAddRate})

        },
        addCompany(){
            let id = this.inValue.length;
            this.inValue.push({ID:`n${id}`, ...cloneDeep(this.objectAdd)})
        },
        remove(key){
            this.inValue.splice(key,1)
        },
        removeRate(key,keyRate){
            this.inValue[key].rates.splice(keyRate,1)
        },
        edit(){
            if (this.isClickEdit){
                this.$emit('edit')
            }
        }
    }
}
</script>
