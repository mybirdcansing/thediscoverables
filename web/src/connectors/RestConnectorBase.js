import { ConnectorBase } from "./ConnectorBase";

export class RestConnectorBase extends ConnectorBase {
	constructor(handler) {
        super(handler);
  	}

	get(id) {
        return this._get(this.handler + id);
	}

	getAll() {
        return this._get(this.handler);
	}

	create(obj) {
        return this._post(obj, this.handler);
	}

	update(obj) {
        return this._post(obj, this.handler + obj.id);
	}

    delete(obj) {
        return this._post(obj, this.handler + obj.id + '/delete');
	}
}
