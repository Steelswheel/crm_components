<template>
    <div>
        <inputGroupV v-if="isEdit" :alias="this.alias" />
        <div v-else >{{LAST_NAME}} {{NAME}} {{SECOND_NAME}}</div>


        <template v-if="findType === 'edz'">
            <span class="add-dotted" @click="find">Проверить дубли  </span>
            <span class="small">
                <i v-if="isLoading" class="el-icon-loading"></i>
                <span v-if="!isLoading && isFind">{{dealList.length}}</span>
            </span>



            <div v-if="textError" class="text-danger">{{textError}}</div>
            <div v-for="item in dealList" :key="item.ID">
               <a :href="`/b/edz/?deal_id=${item.ID}&show`" target="_blank" class="small">
                   {{item.LAST_NAME}} {{item.NAME}} {{item.SECOND_NAME}}
                   <span v-if="GET_STAGES">({{GET_STAGES.find(i => i.id === item.STAGE_ID).label}})</span>
               </a>
            </div>
        </template>


    </div>
</template>

<script>
import { BX_POST } from '@app/API'
import inputGroupV from '@app/input/input-group-v'
import { debounce } from "lodash";
import { mapGetters } from "vuex";
export default {
    inheritAttrs: false,
    components: {
        inputGroupV
    },
    name: "input-fio",
    props: {
        alias: String,
        isEdit: {
            type: Boolean,
            default: true
        },
        fields: Object,
        findType: String,
    },
    data(){
        return {
            isFind: false,
            dealList: [],
            textError: '',
            isLoading: false,
        }
    },
    watch: {
        LAST_NAME(){
            this.search()
        },
        NAME(){
            this.search()
        },
        SECOND_NAME(){
            this.search()
        },
    },
    computed:{
        DEAL_ID(){return this.$store.getters['form/GET_VALUE']('DEAL_ID')},
        LAST_NAME(){return this.$store.getters['form/GET_VALUE'](this.fields['LAST_NAME'])},
        NAME(){return this.$store.getters['form/GET_VALUE'](this.fields['NAME'])},
        SECOND_NAME(){return this.$store.getters['form/GET_VALUE'](this.fields['SECOND_NAME'])},
        ...mapGetters('form',[
            'GET_STAGES',
        ]),
        fio(){
            return {
                LAST_NAME: this.LAST_NAME ? this.LAST_NAME.trim() : '',
                NAME: this.NAME ? this.NAME.trim() : '',
                SECOND_NAME: this.SECOND_NAME ? this.SECOND_NAME.trim() : '',
            }
        }
    },
    methods: {
        search: debounce(async function() {
            this.find()
        }, 350),
        find(){
            this.isFind = true
            if (this.fio.LAST_NAME && this.fio.LAST_NAME.length > 3 && this.fio.NAME && this.fio.NAME.length > 3){
                this.isLoading = true
                this.textError = ''
                this.dealList = []
                BX_POST('vaganov:edz.show', 'fioFind', {
                    ...this.fio,
                    DEAL_ID: this.DEAL_ID,
                })
                    .then(r => {
                        this.dealList = r
                        this.isLoading = false
                    })
            }else {
                this.dealList = []
                this.textError = 'Введите минимум 3 буквы имени и фамилии'
            }


        }
    },


}
</script>
