<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-10">
                <nav class="navbar-light">
                    <a class="navbar-brand" href="/">thediscoverables.com</a>
                </nav>                
                <h2>Password Help</h2>
                <div class="container">
                    <div class="row">
                        <div id="col">
                            <p>
                                To reset your password, please enter the username or email address associated with your accoutnt.
                                Then we'll send you an email with a link to where you can reset your password.
                            </p>
                            <form v-on:submit.prevent="submit">
                                <form-alerts v-bind:errors="errors" v-bind:showSavingAlert="showSavingAlert" v-bind:savingMessage="$data.actionMessage" />
                                <div class="form-group">
                                    <label for="manageUsername">Username</label>
                                    <input v-model="$data.username" class="form-control" type="text" id="manageUsername" placeholder="username">
                                </div>
                                <div class="form-group">
                                    <label for="manageEmail">Email</label>
                                    <input v-model="$data.email" class="form-control" type="text" id="manageEmail" placeholder="email">
                                </div>
                                <div class="form-group">  
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Send Password Request</button>
                                </div>
                                <div>
                                    <router-link class="underlineHover" to="/login">Go to login page</router-link>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>        
</template>

<script>
    import './management-css';
    import FormAlerts from './layout/FormAlerts.vue';
    import {UserConnector} from '../../connectors/UserConnector';
    export default {
        name: "PasswordHelp",
        components: {
            FormAlerts
        },
        data: function() {
            return {
                showSavingAlert: false,
                errors: [],
                username: null,
                email: null,
                actionMessage: 'Saving...'
            }
        },
        methods: {
            submit() {
                const userConnecter = new UserConnector();
                this.$data.showSavingAlert = true;
                this.errors = [];
                userConnecter.requestPasswordReset(this.$data.username, this.$data.email)
                .then((response) => {
                    if (response.passwordResetSent) {
                        this.$data.actionMessage = `An email was sent to ${response.user.email}.`
                        setTimeout(() => {
                            this.showSavingAlert = false;
                        }, 4000);
                    }
                })
                .catch((data) => {
                    this.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                });
            }
        },
    }
</script>

<style scoped>

</style>
