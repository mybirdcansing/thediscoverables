import axios from 'axios';
import regeneratorRuntime from "regenerator-runtime";

export class ConnectorBase {

	constructor(handler) {
        // debugger;
	 	this.handlerBase = `/lib/handlers`;
 		this.handlerUrl = `${this.handlerBase}/${handler}/`;
        this.handler = handler;
  	}

    client() {
        return axios.create({
            baseURL: '/lib/handlers',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json'
            }
        });
    }

	get(id, successCallback, logToConsole = this.logToConsole) {
        this.client().get(this.handler + id).then(function (response) {
            successCallback(response.data);
        }).catch(function (error) {
            logToConsole(error.response);
        });
	};

	getAll() {
        return new Promise((resolve, reject) => {
            this.client().get(this.handler).then((response) => {
                resolve(response.data);
            }).catch((error) => {
                if (process.env.NODE_ENV === "development") {
                    this.logToConsole(error);
                }
                reject(error.response.data);
            });
        });
	}

	create(obj, successCallback, logToConsole = this.logToConsole) {
        Api().post(this.handler, {
            data: obj
        }).then(function (response) {
            successCallback(response.data);
        }).catch(logToConsole);
	};

	update(obj, successCallback, logToConsole = this.logToConsole) {
        Api().post(this.handler + obj.id, {
            data: obj
        }).then(function (response) {
            successCallback(response.data);
        }).catch(logToConsole);
	}

	deleteThing(obj, callback, logToConsole = this.logToConsole) {
        Api().post(this.handlerUrl + obj.id + '/delete', {
            data: obj
        }).then(function (response) {
            successCallback(response.data);
        }).catch(logToConsole);
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
