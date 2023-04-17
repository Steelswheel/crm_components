<template>
    <div v-if="isLoading" class="is-loading-wrap">
        <div class="is-loading-wrap__loader"></div>
    </div>
</template>

<script>

export default {
    name: "loading",
    props: {
        isLoading: {
            type: Boolean,
            default: false,
        }
    },
    watch:{
        isLoading(){
            this.startEvent()
        }
    },
    mounted() {

        this.startEvent()
    },
    methods:{
        startEvent(){
            if(this.isLoading){

                this.$nextTick(() => {
                    this.addEvent()
                    this.onEvent()
                })

            }else {
                this.removeEvent()
            }
        },
        onEvent(){
            const elWrap = this.$el
            const elLoader = this.$el.querySelector('.is-loading-wrap__loader')

            const cor = elWrap.getBoundingClientRect()
            const corBlock = cor.y * -1 + 100
            let corTop = corBlock;
            if(corBlock < 100 ){
                corTop = 100
            }else if(corBlock > cor.height - 100){
                corTop = cor.height - 100
            }
            elLoader.style.top = `${corTop}px`

        },
        addEvent(){
            window.addEventListener('scroll', this.onEvent);
        },
        removeEvent(){
            window.removeEventListener('scroll', this.onEvent)
        },

    }
}
</script>

<style scoped>

</style>