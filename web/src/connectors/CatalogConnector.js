import Api from './api';

export class CatalogConnector {
	constructor() {
        this.handler = 'catalog';
  	}

    get(url) {
        return new Promise((resolve, reject) => {
            Api().get(this.handler)
                .then(response => resolve(response.data))
                .catch(error => this.#rejector(reject, error));
        });
    }

    #rejector(reject, error) {
        if (process.env.NODE_ENV === "development") {
            this.#logToConsole(error);
        }
        if (error.response) {
            reject(error.response.data);
        } else {
            reject({ errorMessages: [error.message] });
        }
    }

    #logToConsole(error) {
        if (error.response) {
          // The request was made and the server responded with a status code
          // that falls out of the range of 2xx
          console.log(error.response.data);
          console.log(error.response.status);
          console.log(error.response.headers);
        } else if (error.request) {
          // The request was made but no response was received
          // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
          // http.ClientRequest in node.js
          console.log(error.request);
        } else {
          // Something happened in setting up the request that triggered an Error
          console.log('Error', error.message);
        }
        console.log(error.config);
    }

}
