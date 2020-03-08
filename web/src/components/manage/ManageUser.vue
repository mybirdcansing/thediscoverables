<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage User</h2>
            <router-link to="/manager/users" class="text-secondary">&lt;&lt; Back to Users</router-link>
        </div>
        <div class="container">
            <form v-on:submit.prevent="submitUser">
                <form-buttons @cancel="goToUsersPage" @delete="confirmDeleteItem(user)" @submit="submitUser" />          
                <form-alerts v-bind:errors="errors" v-bind:showSavingAlert="showSavingAlert" savingMessage="Saving user..." />
                <div class="form-group">
                    <label for="manageUsername">Username</label>
                    <input v-model="user.username" class="form-control" type="text" id="manageUsername" placeholder="username">
                </div>
                <div class="form-group">
                    <label for="manageEmail">Email</label>
                    <input v-model="user.email" class="form-control" type="text" id="manageEmail" placeholder="email">
                </div>                
                <div class="form-group">
                    <label for="manageFirstName">First name</label>
                    <input v-model="user.firstName" class="form-control" type="text" id="manageFirstName" placeholder="first name">
                </div>
                <div class="form-group">
                    <label for="manageLastName">Last name</label>
                    <input v-model="user.lastName" class="form-control" type="text" id="manageLastName" placeholder="last name">
                </div>
                <div v-if="create">
                    <div class="form-group">
                        <label for="managePassword">Password</label>
                        <input v-model="user.password" class="form-control" type="password" id="managePassword" placeholder="password">
                    </div>
                    <div class="form-group">
                        <label for="managePasswordConfirmation">Password Confirmation</label>
                        <input v-model="user.passwordConfirm" class="form-control" type="password" id="managePasswordConfirmation" placeholder="Re-enter password">
                    </div>                       
                </div>
                <br/>
                <form-buttons @cancel="goToUsersPage" @delete="confirmDeleteItem(user)" @submit="submitUser" />
            </form>
        </div>
        <modal v-if="showModal" handler="user" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import FormAlerts from './FormAlerts.vue';
    import FormButtons from './FormButtons.vue';
    import DeleteButtonMixin from './DeleteButtonMixin';
    import { StatusEnum } from '../../store/StatusEnum';    
    export default {
        name: "ManageUser",
        mixins: [DeleteButtonMixin],
        components: {
            FormAlerts,
            FormButtons,
        },
        data: function() {
            return {
                errors: [],
                showSavingAlert: false,
                create: this.$route.params.id === 'create'
            }
        },
        methods: {
            ...mapGetters('manage', {
                getById: 'getUserById'
            }),
            submitUser() {
                this.errors = [];
                const saveAction = (this.$data.create) ? this.createItem : this.updateItem;
                if (this.$data.create) {
                    if (!this.user.password){
                        this.$data.errors.push("Please enter a password");
                        return;
                    }
                    if (this.user.password !== this.user.passwordConfirm) {
                        this.$data.errors.push("The password confirmation and the password don't match");
                        return;
                    }
                }
                this.showSavingAlert = true;
                saveAction({
                    data: this.user,
                    handler: 'user'            
                }).then((response) => {
                    this.errors = [];
                    setTimeout(() => {
                        this.showSavingAlert = false;
                    }, 900);
                }).catch(function(data) {
                    this.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                }.bind(this));
            },
            goToUsersPage() {
                this.$router.push('/manager/users');
            },
            ...mapActions('manage', [
                'deleteItem'
            ]),            
            ...mapActions('manage', [
                'updateItem',
                'createItem'
            ])
        },
        computed: {
            user: function() {
                if (this.$data.create) {
                    return { id: null, username: null, firstName: null, lastName: null, email: null, password: null, passwordConfirm: null };
                } else {
                    const storeData = this.getById()(this.$route.params.id);
                    return Vue.util.extend({}, storeData);
                }
            }
        } 
    }
</script>

<style scoped></style>
