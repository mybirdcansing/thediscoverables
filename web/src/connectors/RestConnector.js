import { ConnectorBase } from "./ConnectorBase";

export class RestConnector extends ConnectorBase {
	constructor(handler) {
        super(handler);
  	}

	get(id) {
        return this._get(this.handler + '/' + id);
	}

	getAll() {
        return this._get(this.handler);
	}

	create(obj) {
        return this._post(obj, this.handler + '/create');
	}

	update(obj) {
        return this._post(obj, this.handler + '/' + obj.id);
	}

    delete(id) {
        return this._post({}, this.handler + '/' + id + '/delete');
	}
}
