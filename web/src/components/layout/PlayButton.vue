<template>
    <button class="play-button" @click="$emit('play')">
        <div class="play-button-text">Play</div>
        <play-arrow/>
    </button>
</template>

<script>
    import PlayArrow from './PlayArrow.vue';
    export default {
        name: "PlayButton",
        components: {
            PlayArrow
        }
    }
</script>

<style scoped>
.play-button {
    cursor: pointer;
    background-color: #eee;
    margin: 8px 0 12px 0;
}
 
.play-button-text {
    float:right;
    font-weight: 600;
    font-size: 13px;
    line-height: 24px;
    margin-right: 14px;
    margin-left: 6px;
}
</style>
