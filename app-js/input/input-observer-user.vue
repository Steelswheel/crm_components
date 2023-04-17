<template>
    <div class="mb-3">


        <div class="small">
            <span class="">Совместная работа</span>
            <span class="ml-2 add-dotted" @click="add">Добавить <b-icon icon="plus-circle-fill"/></span>
        </div>


        <div v-for="(userId, key) in inValue" :key="userId">

            <input-user
                v-model="inValue[key]"
                :attribute="attribute"
                title=""
            >
                <template v-slot:btn>
                    <span @click="remove(key)" class="mr-3">Удалить <b-icon icon="trash"/></span>
                </template>
            </input-user>
        </div>



    </div>
</template>

<script>
import inputUser from './input-user'
export default {
    inheritAttrs: false,
    name: "input-observer-user",
    components: {
        inputUser
    },
    props: {
        value: {
            type: Array,
            default: () => ([])
        },
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
    },
    data(){
        return {
            inValue: this.value,

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

    },

    methods: {
        add(){
            this.inValue.push(null)
        },
        remove(key){
            this.inValue.splice(key,1)
        }

    }
}
</script>
