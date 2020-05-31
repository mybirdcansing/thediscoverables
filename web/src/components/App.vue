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

            // disable pinch-zoom gesture
            document.addEventListener("touchstart", event => {
                if(event.touches.length > 1) {
                    event.preventDefault();
                    event.stopPropagation(); // maybe useless
                }
            }, {passive: false});            
        }
    }
</script>