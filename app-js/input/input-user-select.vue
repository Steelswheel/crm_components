<template>
    <div>

            <v-select
                :reduce="i => i.id"
                :options="users"
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
        value: [String],
        users: {
            type: Array,
            default: () => ([])
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
            return this.users.find(i => i.id === this.value)
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
