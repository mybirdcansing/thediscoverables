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
    import "./manage.css";
    import ManagerNavbar from './ManagerNavbar.vue';
    import { mapActions, mapGetters } from 'vuex';
    import { StatusEnum } from '../../store/StatusEnum';

    export default {
        name: "Manager",
        components: {
            ManagerNavbar
        },
        methods: {
            ...mapActions('manage', [
                'fetchData'
            ]),
        },
        computed: {
           ...mapGetters('manage', [
                'manageState',
                'getManager'
            ]),
        },
        created: function() {
            this.fetchData();
        },
    }
</script>

<style>
    
    @media (min-width: 992px) { 
        .collapsing {
            transition: none !important;
        }
    }
</style>
