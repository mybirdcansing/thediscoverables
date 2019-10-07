//
//
// Use this to conect to the server
function UserConnector() {
 	//todo: move this into a config file
 	this.handlerBase = '/lib/handlers';
 	this.handlerUrl = this.handlerBase + '/user/index.php';

	this.getUser = function(id, callback, errorCallback) {
		$.ajax({
            url: this.handlerUrl + '?id=' + id,
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

	this.createUser = function(user, callback) {
		var request = new XMLHttpRequest();
		request.open('POST', this.handlerUrl, true);
		request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
		request.onload = function () {
	      	var json = JSON.parse(request.responseText);
	        callback(json.userId);
		}
		request.send(JSON.stringify(user));
	};

	this.updateUser = function(user, callback, errorCallback) {
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

	this.authenticate = function(input, callback, errorCallback) {
		$.ajax({
            url: this.handlerBase + '/authenticate.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
            	// console.log(data);
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
            	debugger;
            	errorCallback(data.responseJSON, textStatus, errorThrown);
		    },
		    data: JSON.stringify(user)
        });
	}

	this.resetPassword = function(data, callback, errorCallback) {
		$.ajax({
            url: this.handlerUrl,
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
            	debugger;
            	callback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
            	debugger;
            	errorCallback(data.responseJSON, textStatus, errorThrown);
		    },
		    data: JSON.stringify(data)
        });
	}
}