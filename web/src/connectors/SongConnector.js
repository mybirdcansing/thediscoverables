import { RestConnector } from "./RestConnector";

export class SongConnector extends RestConnector {
	constructor() {
    	super('song');
	}
}
