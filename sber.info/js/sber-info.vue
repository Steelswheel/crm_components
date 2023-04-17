<template>
    <div class="b-block b-block__content">
        <a href="#" @click.prevent="onUrlParse">onUrlParse</a>
        <br>
        <pre>{{params}}</pre>
        <br>
        <b></b>
        <div style="word-wrap: break-word;">
            {{urlToCode}}
        </div>
    </div>
</template>

<script>
import qs from 'qs';

export default {
    name: 'sber-info',
    data() {
        return {
            params: {},
        }
    },
    computed: {
        urlToCode() {
            return this.urlProm + '?' + qs.stringify(this.urlParams);
        }
    },
    mounted() {
        this.onUrlParse();
    },
    methods: {
        onUrlParse() {
            let url = window.location.search.slice(1);
            this.params = qs.parse(url);
        },
        strGen(len) {
            let str = '';

            for (let i = 0; i < len; i++) {
                let pos = Math.floor(Math.random() * chrs.length);

                str += chrs.substring(pos, pos + 1);
            }

            return str;
        }
    }
}
</script>

<style scoped>

</style>