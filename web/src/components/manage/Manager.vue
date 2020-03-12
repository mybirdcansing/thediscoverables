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
    import(/* webpackChunkName: "bootstrap" */ '../../bootstrap');
    import ManagerNavbar from './ManagerNavbar.vue';
    import { mapActions } from 'vuex';
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
        created: function() {
            this.fetchUsers();
        }
    }
</script>

<style>
    .some-vpadding {
        padding-top: 4px;
        padding-bottom: 4px;
    }

    .more-vpadding {
        padding-top: 8px;
        padding-bottom: 10px;
    }
    .clickable-text {
        cursor: default;
    }
    @media (min-width: 992px) { 
        .collapsing {
            transition: none !important;
        }
    }
</style>
