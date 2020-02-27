<template>
    <div>
       <router-view></router-view>
    </div>
</template>

<script>


    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: "App",
        components: {
        },
        data() {
            return {}
        },
        methods: {
            ...mapActions([
                  'fetchCatalog'
            ])
        },
        computed: {
            ...mapGetters({
                allSongs: 'songSet',
            })
        },
        created: function() {
            this.fetchCatalog();
        }
    }
</script>

<style scoped></style>