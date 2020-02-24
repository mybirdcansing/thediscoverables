<template>
    <div>
        <p v-text="item.title"></p>
        <button @click="toggle = !toggle">click me</button>
        <p v-if="toggle">{{item.description}}</p>
    </div>
</template>

<script>
    export default {
        name: "Accordion",
        props: ['item'],
        data: function() {
            return {
                toggle: false,
            }
        }
    }

</script>

<style scoped></style>
