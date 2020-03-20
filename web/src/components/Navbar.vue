<template>
    <div>
        <nav>
            <a href="/">The Discoverables</a>
            <div style="float:right;padding-right:24px" v-if='isDevelopment'>
                <router-link to="/manager">Manager</router-link>
            </div>
        </nav>
        <div style="height:29px;"></div>
    </div>
</template>

<script>
    export default {
        name: "Navbar",
        props: [],
        data: function() {
            return {
            }
        },
        computed: {
            isDevelopment() {
                return (process.env.NODE_ENV === "development");
            }
        }
    }
</script>

<style scoped></style>
