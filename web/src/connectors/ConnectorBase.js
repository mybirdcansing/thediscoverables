import axios from 'axios';

export class ConnectorBase {
	constructor(handler) {
        this.handlerBase = '/lib/handlers/';
        this.handlerUrl = `${this.handlerBase}${handler}/`;
        this.handler = handler;
    }
      
    client() {
        return axios.create({
            baseURL: this.handlerBase,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json'
            }
        });
    }

    _get(url) {
        return new Promise((resolve, reject) => {
            this.client().get(url)
                .then(response => resolve(response.data))
                .catch(error => this.rejector(reject, error));
        });
    }

    _post(obj, url) {
        return new Promise(function(resolve, reject) {
            debugger;
            this.client().post(url, { data: obj })
                .then(response => {
                    debugger;
                    return resolve(response.data)
                })
                .catch(error => {
                    debugger;
                    this.rejector(reject, error)
                });
        }.bind(this));
    }

    rejector(reject, error) {
        if (process.env.NODE_ENV === "development") {
            this.logToConsole(error);
        }
        if (error.response) {
            reject(error.response.data);
        } else {
            reject({ errorMessages: [error.message] });
        }
    }

    logToConsole(error) {
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