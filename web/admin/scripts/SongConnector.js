// Use this to conect to the server for song related requests
function SongConnector() {
 	//todo: move this into a config file
 	this.handlerBase = '/lib/handlers';
 	this.handlerUrl = this.handlerBase + '/song/';

	this.getSong = function(id, callback, errorCallback) {
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

	this.getSongs = function(callback, errorCallback) {
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

	this.createSong = function(song, callback, errorCallback) {
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
		    data: JSON.stringify(song)
        });
	};

	this.updateSong = function(song, callback, errorCallback) {
		$.ajax({
            url: this.handlerUrl + song.id,
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
            data: JSON.stringify(song)
        });
	};

	this.deleteSong = function(song, callback, errorCallback) {
		$.ajax({
            url: this.handlerUrl + song.id + '/delete',
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
            data: JSON.stringify(song)
        });
	};

}