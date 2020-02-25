import Api from './api';
import { ConnectorBase } from "./ConnectorBase";

export class PlaylistConnector extends ConnectorBase {
	constructor() {
    	super('playlist');
  	}

    getPlaylistSongs(successCallback, errorCallback) {
		if (!errorCallback) errorCallback = this.errorCallback;
        Api().get(this.handler + 'action/playlistsongs').then(function (response) {
            successCallback(response.data);
        }).catch(function (error) {
            errorCallback(error.response);
        });
	};
}
