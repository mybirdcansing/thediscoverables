class ConnectorBase {

	constructor(handler) {
	 	this.handlerBase = '/lib/handlers';
 		this.handlerUrl = this.handlerBase + '/' + handler + '/';
  	}

	get(id, successCallback, errorCallback) {
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
            	errorCallback(data.responseJSON, textStatus, errorThrown);
		    }
        });
	};

	getAll(successCallback, errorCallback) {
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
            	errorCallback(data.responseJSON, textStatus, errorThrown);
		    }
        });
	};

	create(obj, successCallback, errorCallback) {
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
            	errorCallback(data.responseJSON, textStatus, errorThrown);
		    },
		    data: JSON.stringify(obj)
        });
	};

	update(obj, successCallback, errorCallback) {
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
            	errorCallback(data.responseJSON, textStatus, errorThrown);
		    },
            data: JSON.stringify(obj)
        });
	};

	deleteThing(obj, callback, errorCallback) {
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
            	errorCallback(data.responseJSON, textStatus, errorThrown);
		    },
            data: JSON.stringify(obj)
        });
	};
}