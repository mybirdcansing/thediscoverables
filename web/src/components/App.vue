<template>
    <div>
        <navbar/>
        <br />
        <router-view></router-view>
        <br />
        <accordion v-for="song in songs" :key="song.id" :item="song" />
        <br />

    </div>
</template>

<script>
    import Accordion from './layout/Accordion.vue';
    import Navbar from './layout/Navbar.vue';
    import { mapActions } from 'vuex';

    export default {
        name: "App",
        components: {
            Accordion,
            Navbar
        },
        data(){
            return {
                songs: []
            }
        },
        methods: {
            ...mapActions([
                  'initSongs', // map `this.increment()` to `this.$store.dispatch('increment')`
                  'initStore'
              ])
        },
        computed: {

        },
        created: async function() {
            let songs;
            try {
                songs = await this.initSongs();
            } catch(e) {
                console.log('in async function', e);
            }
            this.songs = songs;

            // this.initSongs().then((songs) => {
            //     this.songs = songs;
            // }).catch(()=>{});
        },
        computed: { }
    }
</script>

<style scoped></style>
