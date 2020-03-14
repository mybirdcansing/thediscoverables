<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Users</h2>
            <div class="container">
                <div class="more-vpadding">
                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                        @click="$router.push('/manager/user/create')"><strong>+</strong> Add a User</button>
                </div>
                <table class="table table-hover table-sm">
                    <tbody>
                        <tr v-for="user in allUsers" :key="user.id" class='clickable-text'>
                            <td @click="openItem(user.id)">
                                <span class="align-middle">{{ displayName(user) }}</span>
                            </td>
                            <td @click="openItem(user.id)">
                                {{user.username}}
                            </td>
                            <td @click.self="openItem(user.id)">
                                <div class="float-right">
                                    <button class="btn-xs btn-outline-secondary" 
                                        @click.self.prevent="confirmDeleteItem(user)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <modal v-if="showDeleteModal" handler="user" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{displayName(itemToDelete)}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import DeleteButtonMixin from './layout/DeleteButtonMixin';    
    export default {
        name: "ManageUsers",
        mixins: [DeleteButtonMixin],
        components: {  
        },
        data: function() {
            return {
            }
        },        
        methods: {
            openItem(id) {
                 this.$router.push(`/manager/user/${ id }`);
            },
            displayName(user) {
                return `${user.firstName} ${user.lastName}`;
            },        
        },
        computed: {
            ...mapGetters('manage', {
                allUsers: 'userSet',
            }),

        },
    }
</script>

<style scoped></style>
