<template>
    <nav>
        <a href="/">the discoverables</a>
        <div>
            <ul>
                <li>
                    <router-link class="nav-link" to="/">Home</router-link>
                </li>
                <li>
                    <router-link class="nav-link" to="/album/1">Album</router-link>
                </li>
                <li>
                    <router-link class="nav-link" to="/song/2">Song</router-link>
                </li>
                <li v-if='isDevelopment'>
                    <router-link class="nav-link" to="/manager">Manager</router-link>
                </li>
            </ul>
        </div>
    </nav>
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
