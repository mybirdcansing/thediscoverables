<template>
    <div>
        <navbar/>
        <br />
        <router-view></router-view>
        <br />
        <accordion v-for="song in allSongs" :key="song.id" :item="song" />
        <br />

    </div>
</template>

<script>
    import Accordion from './layout/Accordion.vue';
    import Navbar from './layout/Navbar.vue';
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: "App",
        components: {
            Accordion,
            Navbar
        },
        data() {
            return {}
        },
        methods: {
            ...mapActions([
                  'fetchCatalog'
            ]),
            ...mapActions('manage', [
                'fetchUsers'
            ])
        },
        computed: {
            ...mapGetters({
                allSongs: 'songSet',
            })
        },
        mounted: function() {

        },
        created: function() {
            this.fetchCatalog();
            this.fetchUsers();
        }
    }
</script>

<style scoped></style>