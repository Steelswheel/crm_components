<template>
    <div class="work-schedule">
        <el-menu :default-active="activeIndex" @select="handleSelect" mode="horizontal" class="mb-3">
            <el-menu-item index="1">
              РЕГЛАМЕНТ РАБОТЫ
            </el-menu-item>
            <el-menu-item index="2">
              КРЕДИТНЫЕ ПРОГРАММЫ
            </el-menu-item>
            <el-menu-item index="3">
              СБЕРЕГАТЕЛЬНЫЕ ПРОГРАММЫ
            </el-menu-item>
            <el-menu-item index="4">
              ПРЕЗЕНТАЦИЯ (ЗАЙМЫ)
            </el-menu-item>
            <el-menu-item index="5">
              ПРЕЗЕНТАЦИЯ №2 (ЗАЙМЫ)
            </el-menu-item>
            <el-menu-item index="6">
              ПРЕЗЕНТАЦИЯ (СБЕРЕЖЕНИЯ)
            </el-menu-item>
        </el-menu>

        <workScheduleEdit v-if="activeIndex === '1'"/>
        <loanPrograms v-if="activeIndex === '2'"/>
        <savingsPrograms v-if="activeIndex === '3'"/>
        <zaym-presentation v-if="activeIndex === '4'"/>
        <zaymPresentation2 v-if="activeIndex === '5'"/>
        <savings-presentation v-if="activeIndex === '6'"/>
    </div>
</template>

<script>
    import zaymPresentation from './zaym-presentation';
    import zaymPresentation2 from './zaym-presentation-2';
    import savingsPresentation from './savings-presentation';
    import loanPrograms from './loan-programs';
    import savingsPrograms from './savings-programs';
    import workScheduleEdit from './work-schedule-edit';
    import { Menu, MenuItem } from 'element-ui';

    export default {
        name: 'work-schedule',
        components: {
            'el-menu': Menu,
            'el-menu-item': MenuItem,
            workScheduleEdit,
            loanPrograms,
            savingsPrograms,
            zaymPresentation,
            zaymPresentation2,
            savingsPresentation
        },
        data() {
            return {
                activeIndex: '1',
                isLoading: true,
                isLoadingSave: false,
                isEdit: false,
            }
        },
        methods: {
            handleSelect(index) {
                this.activeIndex = index
            }
        }
    }
</script>

<style>
  .ql-align-center {
      text-align: center;
  }
  .ql-align-right {
      text-align: right;
  }
  .work-schedule-text p {
      margin-bottom: 0;
  }
  .work-schedule-btn-block {
      z-index: 9999999;
      position: fixed;
      bottom: 0;
      right: 0;
      left: 0;
      text-align: center;
      background: #ffffffd4;
      padding: 10px;
  }

  .work-schedule .b-block__content {
      padding: 40px;
      width: 1024px;
      margin: 0 auto;
  }
</style>