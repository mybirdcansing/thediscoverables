<template>
    <div>
        <manager-navbar/>
        <router-view></router-view>
    </div>
</template>

<script>
    import ManagerNavbar from './ManagerNavbar.vue';
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: "Manager",
        components: {
            ManagerNavbar
        },
        methods: {
            ...mapActions('manage', [
                'fetchUsers'
            ])
        },
        data: function() {
            return {

            }
        },
        created: function() {
            this.fetchUsers();
        }
    }
</script>

<style>

</style>
