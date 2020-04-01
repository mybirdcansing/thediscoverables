<template>
    <svg 
        viewBox="0 0 24 24"
        preserveAspectRatio="xMidYMid meet" 
        focusable="false"
        class="play-button-arrow">
            <path d="M8 5v14l11-7z" ></path>
    </svg>
</template>
<script>
    export default {
        name: "PlayArrow",
    }
</script>

<style scoped>
.play-button-arrow {
    pointer-events: none; display: block; width: 24px; height: 24px;
}   
</style>
