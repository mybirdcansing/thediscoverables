import { RestConnector } from "./RestConnector";

export class UserConnector extends RestConnector {
    constructor() {
        super('user');
    }

    authorize() {
        return this._get('authorize.php');
    };

    authenticate(username, password) {
        return this._post({
            username: username, 
            password: password
        }, 'authenticate.php');
    };

    logout() {
        return this._post({}, 'logout.php');
    };

    requestPasswordReset(username, email) {
        return this._post({
            username: username, 
            email: email
        }, 'requestpasswordreset.php');
    };

    resetPassword(password, token) {
        return this._post({
            password: password, 
            token: token
        }, 'password.php');
    };
}
