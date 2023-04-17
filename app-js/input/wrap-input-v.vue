<template>
    <div
        v-if="attribute.show !== false"
        :class="className ? '' : attribute.className || ''" :data-alias="alias"
        :data-field="attribute.field"
        :data-type="attribute.type"
        :data-crm="attribute.crm"
    >
        <div
            :data-label-alias="alias"
            :class="className ? `${className}-label` : attribute.className ? `${attribute.className}-label` : ``"
            v-if="attribute.label && !attribute.noLabel && !noLabel"
        >
            <template v-if="attribute.label && !attribute.noLabel ">
                <label >{{attribute.label}}
                    <span class="text-danger mr-1 fz22" v-if="GET_NEXT_ERROR[alias]">*</span>

                </label>

                <div v-if="attribute.des" class="small" v-html="attribute.des"></div>
            </template>

        </div>
        <div :class="className ? `${className}-input` : attribute.className ? `${attribute.className}-input` : ``" :data-input-alias="alias">

            <div v-if="attribute.type === 'label'" ></div>
            <component
                v-if="attribute.type === 'group-v'"
                v-bind:is="buildComponent"
                :alias="alias"
                :className="className"

            />

            <div v-else>
                <div>
                    <el-tooltip
                        effect="dark"
                        placement="right-start"
                        v-if="getContent.length > 0"
                        style="width: 100%"
                    >
                        <div slot="content">
                            <div
                                v-for="(item, index) in getContent"
                                :key="`field-${alias}-history-${index}`"
                            >
                                {{ item }}
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <component
                                :class="[{'form-control-is-invalid': GET_ERROR_REQUIRED[alias] && GET_ERROR_REQUIRED[alias].length},'w-100']"
                                v-bind:is="buildComponent"
                                v-bind="attribute.settings"
                                :alias="alias"
                                :size="attribute.size"
                                :attribute="attribute"
                                :className="className"
                                :contactId="CONTACT_ID"
                                :dealId="DEAL_ID"
                                v-model="value"
                                ref="component"
                                :disabled="attribute.methodUpdate === 'disabled' || attribute.disabled"
                                :isEdit="attribute.methodUpdate === 'follow' || attribute.methodUpdate === 'disabled' || isEdit"
                                @save="save"
                                @lucked="onLucked"
                            />
                        </div>
                    </el-tooltip>
                    <div class="d-flex justify-content-between" v-else>
                        <component
                            :class="[{'form-control-is-invalid': GET_ERROR_REQUIRED[alias] && GET_ERROR_REQUIRED[alias].length},'w-100']"
                            v-bind:is="buildComponent"
                            v-bind="attribute.settings"
                            :alias="alias"
                            :size="attribute.size"
                            :attribute="attribute"
                            :className="className"
                            :contactId="CONTACT_ID"
                            :dealId="DEAL_ID"
                            v-model="value"
                            ref="component"
                            :disabled="attribute.methodUpdate === 'disabled' || attribute.disabled"
                            :isEdit="attribute.methodUpdate === 'follow' || attribute.methodUpdate === 'disabled' || isEdit"
                            @save="save"
                            @lucked="onLucked"
                        />
                    </div>

                    <div  v-if="UF_SETTINGS_FIELDS " style="flex-shrink:0" >

                        <wrap-input-check
                            :settings="UF_SETTINGS_FIELDS"
                            :alias="alias"
                            :attribute="attribute"
                            :dealId="DEAL_ID"
                            :assigned="ASSIGNED_BY_ID"
                            :fio="FIO"
                        />

                    </div>

                    <wrapInputTask
                        :alias="alias"
                    />
                </div>





            </div>


            <div v-for="(item, key) in GET_ERROR_REQUIRED[alias]" :key="key" class="text-danger">
                {{item}}
            </div>



        </div>
    </div>
</template>

<script>
import { Tooltip } from 'element-ui';
import {BIconCheck, /*BIconPencil,*/ BIconX, BSpinner} from 'bootstrap-vue';
import { mapMutations, mapGetters } from 'vuex';
import wrapInputCheck from './wrap-input-check';
import wrapInputTask from './wrap-input-task';
import moment from 'moment';

export default {
    inheritAttrs: false,
    name: 'wrap-input-v',
    components: {
        BIconCheck,
        BIconX,
        BSpinner,
        wrapInputCheck,
        wrapInputTask,
        'el-tooltip': Tooltip
    },
    props: {
        alias: String,
        noLabel: Boolean,
        className: String,
        isEdit: Boolean
    },
    data() {
        return {
            buildComponent: undefined,
            isLoading: false
        }
    },
    computed: {
        ...mapGetters('form', [
            'GET_ERROR_REQUIRED',
            'GET_NEXT_ERROR',
            'GET_HISTORY',
            'GET_ATTRIBUTES'
        ]),
        getContent() {
            if (this.GET_HISTORY) {
                let items = this.GET_HISTORY.filter(i => i.UF_ALIAS === this.alias);

                let content = [];

                items.forEach(item => content.push(`${moment(item.UF_DATE).format('DD.MM.YYYY')} - ${this.getUser(item.UF_USER)}`));

                return content;
            }

            return [];
        },
        value: {
            get: function () {
                return this.$store.getters['form/GET_VALUE'](this.alias)
            },
            set: function(value){
                this.$store.commit('form/SET_VALUE', {attribute: this.alias, value})
            }
        },
        attribute: {
            get: function () {
                return this.$store.getters['form/GET_ATTRIBUTE'](this.alias)
            },
            set: function(value){
                this.$store.commit('form/SET_ATTRIBUTE', {attribute: this.alias, value})
            }
        },
        CONTACT_ID(){return this.$store.getters['form/GET_VALUE']('CONTACT_ID')},
        DEAL_ID(){return this.$store.getters['form/GET_VALUE']('DEAL_ID')},
        UF_SETTINGS_FIELDS(){
            let UF_SETTINGS_FIELDS = this.$store.getters['form/GET_VALUE']('UF_SETTINGS_FIELDS')
            if(UF_SETTINGS_FIELDS){
                UF_SETTINGS_FIELDS = UF_SETTINGS_FIELDS.filter(i => i.field === this.alias)
                if(UF_SETTINGS_FIELDS.length > 0){
                    return UF_SETTINGS_FIELDS[0]
                }
            }

            return false
        },
        ASSIGNED_BY_ID() {
          return this.$store.getters['form/GET_VALUE']('ASSIGNED_BY_ID');
        },
        FIO() {
          let name = this.$store.getters['form/GET_VALUE']('NAME');
          let lastName = this.$store.getters['form/GET_VALUE']('LAST_NAME');
          let secondName = this.$store.getters['form/GET_VALUE']('SECOND_NAME');

          return lastName + ' ' + name[0] + '. ' + secondName[0] + '.';
        }
    },
    mounted() {
        this.setInput()
    },
    methods: {
        ...mapMutations('form',[
            'SET_IS_BTN_LUCKED'
        ]),
        getUser(userId) {
            let user = this.GET_ATTRIBUTES['ASSIGNED_BY_ID']['items'].find(i => i.id === userId);

            return user ? user.label : userId;
        },
        onLucked(isLucked){
            this.SET_IS_BTN_LUCKED(isLucked)
        },
        onControl(){

        },
        endLoading() {
            this.isLoading = false;
        },
        save() {
            if (this.methodUpdate === 'click'){
                this.isLoading = true;
                this.$emit('save', {value: this.inValue, final: this.endLoading})
            }
        },
        cancel() {
            this.inValue = this.value
        },
        setInput() {
            if (this.attribute.type === 'label') return false

            let input = () => import(`./input-${this.attribute.type}`)
            return input()
                .then(() => this.buildComponent = input)
                .catch(() => {
                    console.error(`НЕТ ТИПА input-undefined `,this.attribute);
                })
        }
    }
}
</script>

<style scoped>

</style>