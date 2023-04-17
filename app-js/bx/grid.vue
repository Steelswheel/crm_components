<template>
    <div class="myBxGrid">

<!--        is-loading-block -->

        <div ref="content"  class="myBxGrid__content">

            <loading :is-loading="isLoading || isFetch"/>

            <div v-if="!isLoading">
                <div class="myBxGrid__doubleHeader">
                    <table class="myBxGrid__table" id="myBxGrid__header">
                        <thead>
                        <tr>
                            <th v-if="checkbox">
                                <el-checkbox :indeterminate="indeterminate" :value="isAll" @change="clickCheckboxAll"/>
                            </th>
                            <th v-for="column in columns"
                                :key="`slave${column.attribute}`"
                                :data-attribute="column.attribute"
                                :class="column.class"
                            >
                                <span v-if="!column.sort" v-html="column.label"></span>
                                <a v-else href="#" @click.prevent="onSort(column.sort)">
                                    <span v-html="column.label" class="mr-1"></span>
                                    <i v-if="sort.attribute === column.sort && sort.sort === 'ASC'" class="el-icon-caret-top"></i>
                                    <i v-else-if="sort.attribute === column.sort && sort.sort === 'DESC'" class="el-icon-caret-bottom"></i>
                                    <i v-else class="el-icon-d-caret"></i>
                                </a>
                            </th>
                        </tr>
                        </thead>
                    </table>
                </div>


                <div class="myBxGrid__mainTable" ref="tableWrap">


                    <table class="myBxGrid__table" ref="tableContent" id="myBxGrid__content">
                        <thead>
                        <tr>
                            <th v-if="checkbox">
                                <el-checkbox :indeterminate="indeterminate" :value="isAll" @change="clickCheckboxAll"/>
                            </th>
                            <th v-for="column in columns"
                                :key="column.attribute"
                                :data-attribute="column.attribute"
                                :class="column.class"
                            >
                                <span v-if="!column.sort" v-html="column.label"></span>
                                <a v-else href="#" @click.prevent="onSort(column.sort)">
                                    <span v-html="column.label" class="mr-1"></span>
                                    <i v-if="sort.attribute === column.sort && sort.sort === 'ASC'" class="el-icon-caret-top"></i>
                                    <i v-else-if="sort.attribute === column.sort && sort.sort === 'DESC'" class="el-icon-caret-bottom"></i>
                                    <i v-else class="el-icon-d-caret"></i>
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        <tr v-if="rows.length === 0">
                            <td :colspan="columns.length" >
                              <div style="height: 210px" class="d-flex align-center justify-content-center">
                                <div>
                                  <div style="font-size: 44px; text-align: center;"> <i class="el-icon-service"></i></div>
                                  Нет данных
                                </div>
                              </div>
                            </td>
                        </tr>
                        <template v-for="row in rows">
                            <tr  :key="row.ID" >

                                <td v-if="checkbox">

                                    <el-checkbox v-model="checkboxValues"  :label="row.ID" >  <span></span></el-checkbox>

                                </td>

                                <td v-for="column in columns" :key="`data-${column.attribute}`" @click="checkboxValuesPush(row.ID)">

                                    <slot :name="column.attribute" :value="row[column.attribute]" :row="row">
                                        <span v-html="row[column.attribute]"></span>
                                    </slot>

                                </td>

                            </tr>

                            <slot :name="`row${row.ID}`" :row="row" :columns="columns"></slot>
                        </template>

                        </tbody>
                    </table>

                </div>
                <div ref="bottomPanel" class="myBxGrid__bottomPanel">

                    <div class="myBxGrid__doubleScroll">

                        <div class="myBxGrid__doubleScroll__scroll">

                        </div>

                    </div>

                    <div ref="controllPanellDubleWrap">

                    </div>
                </div>


                <div v-if="checkbox" ref="controllPanellOriginWrap" >

                    <div ref="controllPanell" v-show="checkboxValues.length > 0"  >
                        <div class="myBxGrid__controll-panell d-flex justify-content-between align-items-center">
                            <div class="mx-3 ">
                                <slot name="checkbox" :checkboxValues="checkboxValues" :rows="rows"></slot>
                            </div>

                            <div class="mx-3 my-3">
                                {{ checkboxValues.length }} ИЗ {{rows.length}}
                            </div>
                        </div>


                    </div>

                </div>


                <div v-if="rows.length > 0" class="myBxGrid__pagination">
                    <el-pagination

                        background
                        :current-page="currentPage"
                        :page-size="pageSize"
                        :page-sizes="pageSizes"

                        :pager-count="11"
                        layout="total, prev, pager, next, sizes"
                        :total="total"
                        @size-change="onSizeChange"
                        @current-change="onChangePage"
                        @prev-click="onChangePage"
                        @next-click="onChangePage"
                    >
                    </el-pagination>
                </div>
            </div>



        </div>








    </div>
</template>

<script>
/*global BX*/
import { Pagination ,Checkbox} from 'element-ui'
import { BX_POST } from '@app/API';
// import {debounce} from 'lodash'
import loading from './loading'


export default {
    name: "grid",
    components: {
        'el-pagination': Pagination,
        'el-checkbox': Checkbox,
        loading
    },
    props:{
        updateRowFn: String,
        getRowsFn: String,
        controller: String,
        pageSizes: {
            type: Array,
            default: () => ([10, 20, 50, 100])
        },
        checkbox: Boolean,
        fieldProcessingAr: Array,
    },
    watch:{
        isLoading() {
            if(this.isLoading === false){
                this.endLoading()
            }
        },
        isFetch() {
            if(this.isFetch === false){
                this.endFetch()
            }
        },
        fieldProcessingAr(ar){
            this.onFieldProcessingAr(ar)
        }
    },
    data() {
        return {

            isLoading: true,
            isFetch: true,

            currentPage: 1,
            rows: [],
            total: 0,
            pageSize: undefined,
            columns: [],
            checkboxValues: [],

            isLoadingRow: [],
            sort: {attribute: null, sort: null},
        }
    },
    created() {
        this.$emit('onUpdateRow', this.onUpdateRow);
        this.$emit('onRefreshTable', this.load);
    },
    mounted() {



        const url = new URL(window.location);
        const currentPage = url.searchParams.get('page');

        if (parseInt(currentPage)) {
            this.currentPage = parseInt(currentPage)
        } else {
            this.currentPage = 1;
        }

        this.load()



        BX.addCustomEvent('BX.Main.Filter:apply', () => {
            this.currentPage = 1
            this.load()
        })
    },
    computed:{
        isAll(){
            return this.checkboxValues.length > 0 && this.checkboxValues.length  === this.rows.length
        },
        indeterminate(){
            return this.checkboxValues.length > 0 && this.checkboxValues.length  !== this.rows.length
        }
    },
    methods: {
        onSort(attribute){
            if(!this.sort.attribute || this.sort.attribute !== attribute){
                this.sort = {attribute, sort: 'ASC'}

            }else if(this.sort.attribute === attribute && this.sort.sort === 'ASC'){
                this.sort = {attribute, sort: 'DESC'}
            }else if(this.sort.attribute === attribute && this.sort.sort === 'DESC'){
                this.sort = {attribute: null, sort: null}
            }
            const s = this.sort.attribute
                ? this.sort.attribute+':'+this.sort.sort
                : ''
            this.load(this.pageSize, s)
            console.log(this.sort.attribute, this.sort.sort);
        },
        onFieldProcessingAr(ar){

            console.log(ar);
            ar.forEach(i => {
                let row = this.rows.find(rowsItem => rowsItem.ID === i.id)
                this.$set(row,i.field,i.value)

            })

        },
        checkboxValuesPush(id){
            if(this.checkboxValues.includes(id)){
                const index = this.checkboxValues.indexOf(id)
                this.checkboxValues.splice(index,1)
            }else{
                this.checkboxValues.push(id)
            }
        },

        clickCheckboxAll(value){

            if(this.checkboxValues.length !==0 && this.checkboxValues.length < this.rows.length){
                this.checkboxValues = []
                return true
            }
            if(value){
                this.checkboxValues = this.rows.map(i => i.ID)
            }else{
                this.checkboxValues = []
            }

        },
        endLoading(){
            this.$nextTick(() => {
                this.scroll()
                this.updateScrollBar()
            })
        },
        endFetch(){
            this.$nextTick(() => {
                this.updateScrollBar()
                this.updateHeader()
            })
        },
        onSizeChange(pageSize){
            this.pageSize = pageSize
            this.currentPage = 1
            this.load(pageSize)
        },
        onChangePage(currentPage){
           this.currentPage = currentPage
           this.load()
        },

        scroll(){

            const elWrap = this.$refs.tableWrap
            const doubleWrap = this.$el.querySelector('.myBxGrid__doubleScroll')

            const doubleHeaderWrap = this.$el.querySelector('.myBxGrid__doubleHeader')
            const bottomPanel = this.$refs['bottomPanel']


            const eventScroll = () => {
                const cor = elWrap.getBoundingClientRect();
                const h = elWrap.offsetHeight;
                const isShowHeader = (cor.y < 0) && ((cor.y + cor.height - 20) > 0);
                const isShowScroll = (cor.top + h - window.innerHeight > 0) && (cor.y - h + 200 < 0);

                if (isShowHeader) {
                    if (doubleHeaderWrap.style.display !== 'block') {
                        doubleHeaderWrap.style.display = 'block';
                        this.updateHeader();
                        doubleHeaderWrap.scrollLeft = doubleWrap.scrollLeft;
                    }
                } else {
                    if (doubleHeaderWrap.style.display !== 'none') {
                        doubleHeaderWrap.style.display = 'none';
                    }
                }

                if (isShowScroll) {
                    if (doubleWrap.style.display !== 'block') {
                        bottomPanel.style.display = 'block';
                        this.moveControllPanel('show');
                    }
                } else {
                    if (doubleWrap.style.display !== 'none') {
                        bottomPanel.style.display = 'none';
                        this.moveControllPanel('hide');
                    }
                }

                this.updateScrollBar();
            }

            eventScroll();

            window.addEventListener('scroll', eventScroll);

            window.addEventListener('resize', () => {
                this.updateHeader()
                this.updateScrollBar()
            });

            doubleWrap.addEventListener('scroll', function() {
                elWrap.scrollLeft = doubleWrap.scrollLeft;
                doubleHeaderWrap.scrollLeft = doubleWrap.scrollLeft;
            });

            elWrap.addEventListener('scroll', function() {
                doubleHeaderWrap.scrollLeft = elWrap.scrollLeft;
            });
        },
        updateScrollBar() {
            const elWrap = this.$refs.tableWrap;
            const elScroll = this.$refs.tableContent;
            const doubleWrap = this.$el.querySelector('.myBxGrid__doubleScroll');
            const doubleScroll = this.$el.querySelector('.myBxGrid__doubleScroll__scroll');

            const doubleHeaderWrap = this.$el.querySelector('.myBxGrid__doubleHeader');
            const doubleHeaderScroll = this.$el.querySelector('.myBxGrid__doubleHeader .myBxGrid__table');

            if (elWrap) {
                let corElWrap = elWrap.getBoundingClientRect();

                if (corElWrap && doubleWrap) {
                    doubleWrap.style.left = `${corElWrap.x}px`;
                }

                if (corElWrap && doubleHeaderWrap) {
                    doubleHeaderWrap.style.left = `${corElWrap.x}px`;
                }

                if (doubleWrap) {
                    doubleWrap.style.width = `${elWrap.clientWidth}px`;
                    doubleWrap.scrollLeft = elWrap.scrollLeft;
                }

                if (doubleHeaderWrap) {
                    doubleHeaderWrap.style.width = `${elWrap.clientWidth}px`;
                }
            }

            if (doubleScroll && elScroll) {
                doubleScroll.style.width = `${elScroll.clientWidth}px`;
            }

            if (doubleHeaderScroll && elScroll) {
                doubleHeaderScroll.style.width = `${elScroll.clientWidth}px`;
            }
        },
        moveControllPanel(showOrHide) {
            const panel = this.$refs['controllPanell'];
            const duble = this.$refs['controllPanellDubleWrap'];
            const origin = this.$refs['controllPanellOriginWrap'];

            if (panel) {
                if (showOrHide === 'hide') {
                    if (origin) {
                        origin.appendChild(panel);
                    }
                } else {
                    if (duble) {
                        duble.appendChild(panel);
                    }
                }
            }
        },
        updateHeader() {
            this.columns.forEach(column => {
                let el = this.$refs.tableContent.querySelector(`[data-attribute="${column.attribute}"]`);
                let elDouble = this.$el.querySelector(`.myBxGrid__doubleHeader [data-attribute="${column.attribute}"]`);

                elDouble.style.width = `${el.offsetWidth}px`;
            })
        },
        load(pageSize = '', sort) {
            this.isFetch = true;
            this.checkboxValues = [];

            let func = this.getRowsFn ? this.getRowsFn : 'rows';

            BX_POST(this.controller, func, {
                currentPage: this.currentPage,
                pageSize,
                sort
            }).then(r => {
                this.rows = r.rows;
                this.total = r.total;
                this.pageSize = r.pageSize;
                this.columns = r.columns;
                this.currentPage = r.currentPage
                this.addOrUpdateUrlParam('page',r.currentPage);

                if (r.sort) {
                    this.sort.attribute = Object.keys(r.sort)[0];
                    this.sort.sort = Object.values(r.sort)[0];
                    console.log(this.sort);
                }

            }).finally(() => {
                this.isLoading = false;
                this.isFetch = false;
            });
        },
        onUpdateRow(id, value) {
            if (value) {
                let row = { ...this.rows.find(i => i.ID === id) };
                let key = this.rows.findIndex(i => i.ID === id);

                console.log({ ...row , ...value });

                this.rows.splice(key,1, { ...row , ...value });
            } else {
                this.isLoadingRow.push(id);
                this.$emit('isLoadingRow', this.isLoadingRow);

                let func = this.updateRowFn ? this.updateRowFn : 'updateRow';

                BX_POST(this.controller, func, {
                    id
                }).then(r => {
                    let key = this.rows.findIndex(i => i.ID === id);
                    this.rows.splice(key,1, r);
                })
            }
        },
        addOrUpdateUrlParam(name, value) {
            const url = new URL(window.location);
            url.searchParams.set(name, value);
            history.pushState({}, null,url.href);
        }
    }

}
</script>

<style scoped>

</style>