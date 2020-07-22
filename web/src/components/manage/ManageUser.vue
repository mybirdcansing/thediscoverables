<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage User</h2>
            <router-link to="/manager/users" class="text-secondary">&lt;&lt; Back to Users</router-link>
        </div>
        <div class="container">
            <form v-on:submit.prevent="submitUser">
                <form-buttons @cancel="goToUsersPage" @delete="confirmDeleteItem(user)" @submit="submitUser" />          
                <form-alerts :errors="errors" :showSavingAlert="showSavingAlert" :savingMessage="savingMessage" />
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
                <div v-else>
                    <div class="form-group" style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-outline-dark" @click="showPasswordResetModal = true">Send Password Rest Email</button>
                    </div>
                </div>
                <br/>
                <form-buttons @cancel="goToUsersPage" @delete="confirmDeleteItem(user)" @submit="submitUser" />
            </form>
        </div>
        <modal v-if="showDeleteModal" handler="user" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.firstName}} {{itemToDelete.lastName}}</strong>?</div>
        </modal>
        <modal v-if="showPasswordResetModal" handler="user" @close="showPasswordResetModal = false" @submit="submitPasswordResetRequest">
            <h3 slot="header">Confirm!</h3>
            <div slot="body" :class="{ 'sending-email': sendingPasswordResetEmailStatus }">
                {{passwordResetConfirm}}
            </div>
        </modal>        
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import FormAlerts from './layout/FormAlerts.vue';
    import FormButtons from './layout/FormButtons.vue';
    import DeleteButtonMixin from './layout/DeleteButtonMixin';
    import StatusEnum from '../../store/StatusEnum';
    import {UserConnector} from '../../connectors/UserConnector';
    const userConnector = new UserConnector();
    const PasswordResetEmailStatus = {
        INIT: 0,
        SENDING: 1,
        ERROR: 2,
        SENT: 3
    };
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
                create: this.$route.params.id === 'create',
                showPasswordResetModal: false,
                sendingPasswordResetEmailStatus: PasswordResetEmailStatus.INIT,
                savingMessage: 'Saving user...',
            }
        },
        methods: {
            submitPasswordResetRequest() {
                this.errors = [];
                this.$data.sendingPasswordResetEmailStatus = PasswordResetEmailStatus.SENDING;
                userConnector.requestPasswordReset(this.user.username, this.user.email)
                    .then((response) => {
                        this.errors = [];
                        this.$data.sendingPasswordResetEmailStatus = PasswordResetEmailStatus.SENT;
                        setTimeout(() => {
                            this.$data.showPasswordResetModal = false;
                            this.$data.sendingPasswordResetEmailStatus = PasswordResetEmailStatus.INIT;
                        }, 900);
                    }).catch((data) => {
                        this.errors = Object.values(data.errorMessages).reverse();
                    });
                
            },
            submitUser: async function() {
                this.errors = [];
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
                try {
                    if (this.$data.create) {
                        await this.createItem({ data: this.user, handler: 'user'});
                    } else {
                        await this.updateItem({ data: this.user, handler: 'user'});
                    }
                    this.errors = [];
                    setTimeout(() => {
                        this.showSavingAlert = false;
                        if (this.$data.create) {
                            this.goToUsersPage();
                        }
                            
                    }, 900);
                } catch(data) {
                    this.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                };
            },
            goToUsersPage() {
                this.$router.push('/manager/users');
            },   
            ...mapActions('manage', [
                'deleteItem',
                'updateItem',
                'createItem'
            ])
        },
        computed: {
            user: function() {
                if (this.$data.create) {
                    return { 
                        id: null,
                        username: null,
                        firstName: null,
                        lastName: null,
                        email: null,
                        password: null,
                        passwordConfirm: null
                    };
                } else {
                    return Vue.util.extend({}, this.getUserById(this.$route.params.id));
                }
            },
            passwordResetConfirm: function() {
                if (this.$data.sendingPasswordResetEmailStatus === PasswordResetEmailStatus.SENDING){
                    return `Okay, sending email to: ${this.user.email}...`;
                } else if (this.$data.sendingPasswordResetEmailStatus === PasswordResetEmailStatus.INIT) {
                    return `Send a password reset email to ${this.user.firstName} ${this.user.lastName}?`;
                } else if (this.$data.sendingPasswordResetEmailStatus === PasswordResetEmailStatus.SENT) {
                    return `Done!`;
                }
            },
            ...mapGetters('manage', [
                'getUserById'
            ]),
        } 
    }
</script>

<style scoped>
.sending-email {
    color: rgb(41, 113, 41);
}
</style>
