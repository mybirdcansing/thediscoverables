<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/">thediscoverables.com</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <router-link class="nav-link" to="/manager/songs">Songs</router-link>
            </li>
            <li class="nav-item">
                <router-link class="nav-link" to="/manager/playlists">Playlists</router-link>
            </li>
            <li class="nav-item">
                <router-link class="nav-link" to="/manager/albums" data-toggle="collapse" data-target=".navbar-collapse">Albums</router-link>
            </li>
            <li class="nav-item">
                <router-link class="nav-link" to="/manager/users" data-toggle="collapse" data-target=".navbar-collapse">Users</router-link>
            </li>
            <li class="nav-item">
                <a @click='logout' class="nav-link">Logout</a>
            </li>
        </ul>
        </div>
    </nav>
</template>

<script>
    import { UserConnector } from '../../connectors/UserConnector';
    
    export default {
        name: "Manager-Navbar",
        methods: {
            async logout() {
                const uc = new UserConnector();
                await uc.logout();
                this.$router.push('/');
            }
        }
    }
</script>

<style scoped>
    .nav-link {
        cursor: pointer;
    }
</style>
