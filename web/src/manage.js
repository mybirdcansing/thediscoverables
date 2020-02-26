import { UserConnector } from './connectors/UserConnector';

export class AdminApp {
    _userConnector;

	constructor() {

	}



  	run() {

        this._userConnector = new UserConnector();
        let authorizedResponse = this._userConnector.authorize(
            (data) => {
                console.log('authorized');
            },
            (data) => {
                console.log('unauthorized');
            }
        );

  	}
}
