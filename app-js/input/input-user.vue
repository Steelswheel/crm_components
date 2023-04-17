<template>
    <div>


        <div class="b-block-title mb-3">
            <div class="b-block-title__name">
                {{title}}
            </div>
            <div
                v-if="isEdit"

                class="b-block-title__edit"
                :class="{'text-primary':isShowSelectBlock}"
                :style="!isShowSelectBlock || `display: block !important;`"
                role="button"
            >

                <slot name="btn"></slot>
                <span @click="isShowSelectBlock = !isShowSelectBlock">Сменить <b-icon icon="pencil"/></span>

            </div>

            <template v-if="!isShowSelectBlock">
                <div v-if="userSelected" class="b-block-user">
                    <div class="b-block-user__img">
                        <img v-if="userSelected.src" :src="userSelected.src" alt="">
                    </div>
                    <div class="b-block-user__content">
                        <a :href="`/company/personal/user/${userSelected.id}/`" class="b-block-user__name">
                            {{userSelected.label}}
                        </a>
                        <div class="b-block-user__position">
                            {{userSelected.position}}
                        </div>
                    </div>
                </div>
                <div v-else>
                    -
                </div>
            </template>


            <v-select
                v-else
                :reduce="i => i.id"
                :options="attribute.items"
                label="label"
                index="id"
                v-model="inValue"

            >
                <template slot="no-options">
                    пусто
                </template>
                <template #selected-option="{id, label, position, src}">
                    <div class="b-block-user py-2" >
                        <div class="b-block-user__img">
                            <img v-if="src" :src="src" alt="">
                        </div>
                        <div class="b-block-user__content">
                            <div class="b-block-user__name">
                                {{label}}
                            </div>
                            <div class="b-block-user__position">
                                {{position}}
                            </div>
                        </div>
                    </div>
                </template>
                <template #option="{id, label, position, src}" >
                    <div class="b-block-user b-block-user-sm" >
                        <div class="b-block-user__img">
                            <img v-if="src" :src="src" alt="">
                        </div>
                        <div class="b-block-user__content">
                            <div class="b-block-user__name">
                                {{label}}
                            </div>
                            <div class="b-block-user__position">
                                {{position}}
                            </div>
                        </div>
                    </div>
                </template>
            </v-select>

        </div>




    </div>
</template>

<script>
import vSelect from 'vue-select'
export default {
    inheritAttrs: false,
    name: "input-user",
    components: {
        vSelect
    },
    props: {
        value: [String, Number],
        attribute: {
            type: Object,
            default: () => ({})
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
        className: String,
        placeholder: String,
        title: {
            type: String,
            default: 'Менеджер',
        }
    },
    data(){
        return {
            inValue: this.value,
            isShowSelectBlock: false,
        }
    },
    watch: {
        value() {
          if (this.value !== this.inValue){
              this.inValue = this.value
          }
        },
        inValue() {
          this.$emit("input",this.inValue)
        }
    },
    computed: {
        userSelected(){
            return this.attribute.items.find(i => i.id === this.value)
        }
    },
    mounted() {
        if (!this.value){
            this.isShowSelectBlock = true
        }
    },
    methods: {
        reset(){
            this.isShowSelectBlock = false
        }
    }
}
</script>
