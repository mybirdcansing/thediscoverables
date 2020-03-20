<template>
    <router-view></router-view>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: "App",
        methods: {
            ...mapActions([
                  'fetchCatalog'
            ])
        },
        created: function() {
            this.fetchCatalog();
        }
    }
</script>