<template>
    <div class="lk">
        <el-tabs v-model="activeName">
            <el-tab-pane
                label="Регламент работы с партнером"
                name="instructions"
            >
                <instructions
                    :is-admin="!!isAdmin"
                    :userId="+userId"
                />
            </el-tab-pane>
            <el-tab-pane
                label="Новости"
                name="news"
                v-if="!!isAdmin || userId === '538'"
            >
                <news/>
            </el-tab-pane>
            <el-tab-pane
                label="Акции (приложение)"
                name="promotions"
                v-if="!!isAdmin || userId === '538'"
            >
                <promotions/>
            </el-tab-pane>
            <el-tab-pane
                label="Новости (приложение)"
                name="sber-news"
                v-if="!!isAdmin || userId === '538'"
            >
              <sberNews/>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import { Tabs, TabPane } from 'element-ui';
import news from './news/news';
import promotions from './promotions/promotions';
import instructions from './instructions/instructions';
import sberNews from './sberNews/sber-news';

export default {
    name: 'lk',
    props: {
        isAdmin: String,
        userId: String
    },
    components: {
        'el-tabs': Tabs,
        'el-tab-pane': TabPane,
        news,
        instructions,
        promotions,
        sberNews
    },
    data() {
        return {
            activeName: this.userId === '538' ? 'news' : 'instructions'
        }
    }
}
</script>

<style>
    .lk {
        border: unset;
        padding: 0;
    }
</style>