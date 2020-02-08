class ConnectorBase {

	constructor(handler) {
	 	this.handlerBase = '/lib/handlers';
 		this.handlerUrl = this.handlerBase + '/' + handler + '/';
  	}

  	errorCallback(data, textStatus, errorThrown) {
		console.log('request failed! ' + textStatus);
	}

	get(id, successCallback, errorCallback) {
		if (!errorCallback) errorCallback = this.errorCallback;
		$.ajax({
            url: this.handlerUrl + id,
            type: 'get',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
            	successCallback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
            	if (data.responseJSON)
            		errorCallback(data.responseJSON, textStatus, errorThrown);
            	else
            		console.log(data.responseText);
		    }
        });
	};

	getAll(successCallback, errorCallback) {
		if (!errorCallback) errorCallback = this.errorCallback;
		$.ajax({
            url: this.handlerUrl,
            type: 'get',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
            	successCallback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
            	if (data.responseJSON)
            		errorCallback(data.responseJSON, textStatus, errorThrown);
            	else
            		console.log(data.responseText);
		    }
        });
	};

	create(obj, successCallback, errorCallback) {
		if (!errorCallback) errorCallback = this.errorCallback;
		$.ajax({
            url: this.handlerUrl,
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
            	successCallback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
        	    if (data.responseJSON)
            		errorCallback(data.responseJSON, textStatus, errorThrown);
            	else
            		console.log(data.responseText);
		    },
		    data: JSON.stringify(obj)
        });
	};

	update(obj, successCallback, errorCallback) {
		if (!errorCallback) errorCallback = this.errorCallback;
		$.ajax({
            url: this.handlerUrl + obj.id,
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
            	successCallback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
            	if (data.responseJSON)
            		errorCallback(data.responseJSON, textStatus, errorThrown);
            	else
            		console.log(data.responseText);
		    },
            data: JSON.stringify(obj)
        });
	};

	deleteThing(obj, callback, errorCallback) {
		if (!errorCallback) errorCallback = this.errorCallback;
		$.ajax({
            url: this.handlerUrl + obj.id + '/delete',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            cache: false,
            success: function (data, textStatus, jqXHR) {
            	callback(data, textStatus, jqXHR);
            },
            error: function(data, textStatus, errorThrown) {
            	if (data.responseJSON)
            		errorCallback(data.responseJSON, textStatus, errorThrown);
            	else
            		console.log(data.responseText);
		    },
            data: JSON.stringify(obj)
        });
	};
}