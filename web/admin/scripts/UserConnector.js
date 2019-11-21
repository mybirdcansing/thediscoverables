
// Use this to conect to the server for user related requests
function UserConnector() {
 	//todo: move this into a config file
 	this.handlerBase = '/lib/handlers';
 	this.handlerUrl = this.handlerBase + '/user/';

	this.getUser = function(id, callback, errorCallback) {
		$.ajax({
            url: this.handlerUrl + id,
            type: 'get',
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

	this.getUsers = function(callback, errorCallback) {
		$.ajax({
            url: this.handlerUrl,
            type: 'get',
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

	this.createUser = function(user, callback, errorCallback) {
		$.ajax({
            url: this.handlerUrl,
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

	this.updateUser = function(user, callback, errorCallback) {
		debugger;
		$.ajax({
            url: this.handlerUrl + user.id,
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

	this.authenticate = function(input, callback, errorCallback) {
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

	this.logout = function(callback, errorCallback) {
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

	this.requestPasswordReset = function(user, callback, errorCallback) {
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
	}

	this.resetPassword = function(data, callback, errorCallback) {
		$.ajax({
            url: this.handlerUrl + '00000000-0000-0000-0000-000000000000/password',
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
	}
}