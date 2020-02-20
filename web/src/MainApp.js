import { SongConnector } from './connectors/SongConnector';
import { UserConnector } from './connectors/UserConnector';

const komapping = require('knockout.mapping');
ko.mapping = komapping;

export class MainApp {

	#songConnector;
    #userConnector;

	constructor() {
		window.onpopstate = this.handlePopState;
		this.songs = ko.observableArray();
	}

		handlePopState = (event) => {
			this.whatUp();
			console.log(`Handle pop state for ${event.path[0].location.href}`)
  	};

  	run() {
  		// debugger;
        this.#userConnector = new UserConnector();
        let authorizedResponse = this.#userConnector.authorize(
            (data) => {
                console.log('authorized');
            },
            (data) => {
                console.log('unauthorized');
            }
        );
  		this.#songConnector = new SongConnector();
  		this.#songConnector.getAll((songs) => {
				this.songs.removeAll();
				songs.forEach(song => {
				  	this.songs.push(ko.mapping.fromJS(song));
				});
  		});
  	};
  	whatUp = () =>  {
  		console.log("what's up?")
  	};
}
