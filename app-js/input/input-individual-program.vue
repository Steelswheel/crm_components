<template>
    <div>


        <table class="ordersummarytbl table table-sm mt-0 mb-0">
            <thead>
                <tr>
                    <th>
                        <span
                              class="font-weight-light">

                            <span v-if="!value['isAgent']">Категория </span>
                            <span v-else >Условия работы с <span class="font-weight-bold">агентом</span></span>
                            <span
                                v-if="isEdit && !value['isAgent']"
                                @click="isOpenEdit = !isOpenEdit"
                                class="ml-1 add-dotted"
                                :class="{'text-primary': isOpenEdit}">ред.

                            </span>
                        </span>
                    </th>
                    <template>
                        <th style="width: 100px"><b>586&nbsp;946,72</b></th>
                        <th style="width: 100px"><b>775&nbsp;628,25</b></th>
                    </template>


                </tr>
            </thead>
            <tbody>

                <!-- '6','6D','7','7D' -->
                <tr >
                    <td><small>{{attributes[`UF_RICE_PROGRAM_K1_1`].label}}</small></td>

                    <template >
                        <td data-vslue="PROGRAM_K1_1">
                            <inputInteger
                                v-model="value[`PROGRAM_K1_1`]"
                                size="sm"
                                :isEdit="isOpenEdit"
                            />
                        </td>
                        <td data-vslue="PROGRAM_K1_1_2">
                            <inputInteger
                                v-model="value[`PROGRAM_K1_1_2`]"
                                size="sm"
                                :isEdit="isOpenEdit"
                            />
                        </td>
                    </template>
                </tr>

                <tr >
                    <td><small>{{attributes[`UF_RICE_PROGRAM_1`].label}}</small></td>

                    <template >
                        <td data-vslue="PROGRAM_1">
                            <inputInteger
                                v-if="isOpenEdit"
                                v-model="value[`PROGRAM_1`]"
                                size="sm"
                                :placeholder="baseTariff['m']"
                                :isEdit="isOpenEdit"
                            />
                            <span v-else> {{value[`PROGRAM_1`] ? value[`PROGRAM_1`] : baseTariff['m']}} </span>
                        </td>
                        <td data-vslue="PROGRAM_1_2">
                            <inputInteger
                                v-if="isOpenEdit"
                                v-model="value[`PROGRAM_1_2`]"
                                size="sm"
                                :placeholder="baseTariff['b']"
                                :isEdit="isOpenEdit"
                            />
                            <span v-else> {{value[`PROGRAM_1_2`] ? value[`PROGRAM_1_2`] : baseTariff['b']}} </span>
                        </td>
                    </template>
                </tr>
            </tbody>
        </table>



    </div>
</template>

<script>
import tariff from '@app/calc/tariff';
import inputInteger from './input-integer'
export default {
    inheritAttrs: false,
    name: "input-individual-program",
    components: {
        inputInteger
    },
    props: {
        attributes: Object,
        value: Object,
        disabled: {
            type: Boolean,
            default: false
        },
        isEdit: {
            type: Boolean,
            default: true
        },
        isClickEdit: Boolean,
        size: String
    },
    data(){
        return {
            isOpenEdit: false,
            labelsTariff: [
                'ЗАЙМ ИПОТЕЧНЫЙ С ЗАЛОГОМ',
                'ЗАЙМ ИПОТЕЧНЫЙ РСК',
            ],
        }
    },
    watch: {
        isEdit() {


        }
    },
    computed:{
        baseTariff(){
            return tariff[1]['spec']
        }
    },
    methods: {
        reset(){
            this.isOpenEdit = false
        }
    }
}
</script>
