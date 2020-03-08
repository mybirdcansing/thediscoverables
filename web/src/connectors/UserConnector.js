import { RestConnector } from "./RestConnector";

export class UserConnector extends RestConnector {
    constructor() {
        super('user');
    }

    authorize() {
        console.log('authorize');
        return this._get('authorize.php');
    };

    authenticate(username, password) {
        console.log(`authenticate ${username}`);
        return this._post({
            username: username, 
            password: password
        }, 'authenticate.php');
    };

    logout() {
        console.log('logout');
        return this._post({}, 'logout.php');
    };

    requestPasswordReset(username, email) {
        console.log(`requestPasswordReset ${username}`);
        return this._post({
            username: username, 
            email: email
        }, 'requestpasswordreset.php');
    };

    resetPassword(password, token) {
        console.log(`resetPassword ${token}`);
        return this._post({
            password: password, 
            token: token
        }, 'password.php');
    };
   
}
