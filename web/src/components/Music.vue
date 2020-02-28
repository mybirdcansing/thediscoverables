<template>
    <div>
         <navbar/>
        <h2>Music</h2>
        <router-view></router-view>
    </div>
</template>

<script>
    import Navbar from './Navbar.vue';
    export default {
        name: "Music",
        components: {
            Navbar
        },
        methods: {
          
        },
        data: function() {
            return {

            }
        }
    }
</script>

<style scoped></style>
