<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col col-md-10">
                <manager-navbar/>
                <router-view></router-view>
            </div>
        </div>
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
