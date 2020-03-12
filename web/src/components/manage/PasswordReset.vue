<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-10">
                <nav class="navbar-light">
                    <a class="navbar-brand" href="/">thediscoverables.com</a>
                </nav>  
                <h2>Reset Password</h2>
                <div class="container">
                    <div class="row">
                        <div id="col">
                            <p>Enter a new password to unlock your account. This is gonna be fun!</p>
                            <form v-on:submit.prevent="submit">
                                <form-alerts v-bind:errors="errors" v-bind:showSavingAlert="showSavingAlert" v-bind:savingMessage="$data.actionMessage" />
                                <div class="form-group">
                                    <label for="managePassword">New password</label>
                                    <input v-model="$data.password" class="form-control" type="password" id="managePassword" placeholder="new password">
                                </div>
                                <div class="form-group">
                                    <label for="managePasswordConfirm">Re-enter new password</label>
                                    <input v-model="$data.passwordConfirm" class="form-control" type="password" id="managePasswordConfirm" placeholder="confirm password">
                                </div>
                                <div class="form-group">  
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Send</button>
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
    import(/* webpackChunkName: "bootstrap" */ '../../bootstrap');
    import FormAlerts from './FormAlerts.vue';
    import {UserConnector} from '../../connectors/UserConnector';

    export default {
        name: "PasswordReset",
        data: function() {
            return {
                showSavingAlert: false,
                errors: [],
                password: null,
                passwordConfirm: null,
                actionMessage: 'Saving...'
            }
        },
        components: {
            FormAlerts
        },
        methods: {
            submit() {
                const userConnecter = new UserConnector();
                this.errors = [];
                const token = '';
                if (!this.$data.password){
                    this.$data.errors.push("Please enter a password");
                    return;
                }
                if (this.$data.password !== this.$data.passwordConfirm) {
                    this.$data.errors.push(`The password confirmation and the password don't match`);
                    return;
                }
                this.$data.showSavingAlert = true;
                userConnecter.resetPassword(this.$data.password, this.$route.query.token)
                    .then((response) => {
                        if (response.userPasswordUpdated) {
                            this.$data.actionMessage = 'Your password has been reset.';
                        }
                    })
                    .catch((data) => {
                        this.$data.showSavingAlert = false;
                        this.$data.errors = Object.values(data.errorMessages).reverse();
                    });
            }          
        },
    }
</script>

<style scoped></style>
