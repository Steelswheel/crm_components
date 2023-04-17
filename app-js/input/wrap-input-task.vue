<template>
    <div v-if="getTasks.length > 0" class="mt-2">

        <taskItem
            v-for="task in getTasks.filter(i => i.STATUS !== '5')"
            :task="task"
            :key="task.ID" />

    </div>
</template>

<script>
import { mapGetters } from 'vuex'
import taskItem from '/edz.show/js/task-list_item';
export default {
    name: "wrap-input-task",
    components: {
        taskItem
    },
    props: {
        alias: String,
    },
    computed: {
        ...mapGetters('form', [
            'GET_TASKS'
        ]),
        DEAL_ID() {
            return this.$store.getters['form/GET_VALUE']('DEAL_ID');
        },
        getTasks() {
            if (this.DEAL_ID) {
                return this.GET_TASKS.list.filter(i => i.taskByField === this.alias);
            } else {
                return false;
            }
        }
    }
}
</script>

<style scoped>

</style>