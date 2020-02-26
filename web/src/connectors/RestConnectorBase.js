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

    _get(url) {
        return new Promise((resolve, reject) => {
            this.client().get(url)
                .then(response => resolve(response.data))
                .catch(error => this.rejector(reject, error));
        });
    }

    _post(obj, url) {
        return new Promise((resolve, reject) => {
            this.client().post(url, { data: obj })
                .then(response => resolve(response.data))
                .catch(error => this.rejector(reject, error));
        });
    }


}
