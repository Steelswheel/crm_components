<template>
    <div>
        <template v-if="fact">

            <template v-if="isClick">
                <a  href="#" @click.prevent="$emit('click')">{{fact}}</a>
            </template>
            <template v-else>{{fact}}</template>

            <i class="el-icon-circle-check text-primary"></i> <small class="text-secondary">{{value}}</small>
        </template>
        <template v-else>

            <template v-if="isClick">
                <a  href="#" @click.prevent="$emit('click')">{{value}}</a>
            </template>
            <template v-else>{{value}}</template>

        </template>
    </div>
</template>

<script>
export default {
    name: "cell-two-numbers",
    props: {
        value: {},
        fact: {},
        isClick: Boolean
    }
}
</script>

<style scoped>

</style>