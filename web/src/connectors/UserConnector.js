import { RestConnectorBase } from "./RestConnectorBase";

export class UserConnector extends RestConnectorBase {
    constructor() {
        super('user');
    }

    authorize(callback, errorCallback) {
        $.ajax({
            url: this.handlerBase + '/authorize.php',
            type: 'GET',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
                callback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
                errorCallback(data.responseJSON, textStatus, errorThrown);
            }
        });
    };

    authenticate(input, callback, errorCallback) {
        $.ajax({
            url: this.handlerBase + '/authenticate.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
                callback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
                errorCallback(data.responseJSON, textStatus, errorThrown);
            },
            data: JSON.stringify(input)
        });
    };

    logout(callback, errorCallback) {
        $.ajax({
            url: this.handlerBase + '/logout.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
                callback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
                errorCallback(data.responseJSON, textStatus, errorThrown);
            }
        });
    };

    requestPasswordReset(user, callback, errorCallback) {
        $.ajax({
            url: this.handlerBase + '/requestpasswordreset.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
                callback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
                errorCallback(data.responseJSON, textStatus, errorThrown);
            },
            data: JSON.stringify(user)
        });
    };

    resetPassword(data, callback, errorCallback) {
        $.ajax({
            url: this.handlerUrl + 'action/password',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
                callback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
                errorCallback(data.responseJSON, textStatus, errorThrown);
            },
            data: JSON.stringify(data)
        });
    };
}
