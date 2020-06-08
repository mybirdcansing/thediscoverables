import { ConnectorBase } from "./ConnectorBase";

export class CatalogConnector extends ConnectorBase {
    constructor() {
        super('catalog');
    }
      
    get() {
        return new Promise((resolve, reject) => {
            this.client().get(this.handler)
                .then(response => resolve(response.data))
                .catch(error => this.rejector(reject, error));
        });
    }
}
