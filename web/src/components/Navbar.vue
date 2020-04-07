<template>
    <div>
        <nav>
            <router-link to="/">
                <img src="../assets/logo.jpeg" alt="The Discoverables" class="band-logo" />
                <!-- Music -->
            </router-link>
            <div v-if='isDevelopment' style="float:right;">
                <router-link to="/manager">Manager</router-link>
            </div>
        </nav>
        <div id="nav-spacer"></div>
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

<style scoped>
    #nav-spacer {
        height:29px;
    }
    .band-logo {
        position: relative;

        width:34px;
        border-radius: 50%;
    }

    nav {
        background-color: rgb(60, 64, 65);
        
        padding:7px 24px 3px 10px;
        position: fixed;
        width: 100%;

    }
</style>
