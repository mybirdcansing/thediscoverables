"use strict"
class AdminViewModel {

	constructor(administrator, isAuthenticated) {
		this.isAuthenticated = ko.observable(isAuthenticated);
		this.administrator = ko.mapping.fromJS(administrator);
		
		this.userConnector = new UserConnector();
		this.songConnector = new SongConnector();
		this.playlistConnector = new PlaylistConnector();
		this.albumConnector = new AlbumConnector();

		this.songs = ko.observableArray();
		this.playlists = ko.observableArray();
		this.albums = ko.observableArray();
		this.users = ko.observableArray();

		this.songToUpdate;
		this.playlistToUpdate;
		this.albumToUpdate;
		this.userToUpdate;

		this.validationErrors = ko.observableArray([]);

		this.currentPage = ko.observable(isAuthenticated ? 'blank' : 'login');
		window.onpopstate = this.handlePopState;
  	}

  	handlePopState = (event) => {
  		const pathPieces = event.path[0].location.pathname.split('/');
  		const pageName = (pathPieces.length > 1) ? pathPieces[2] : 'blank';
		const page = Router.getPage(pageName);
		if (page.maintainPageState) {
			this[page.viewMethod](event.state);
			this.currentPage(page.name);
		} else if (page.modelMethod) {
			this[page.modelMethod]((model) => {
				this[page.viewMethod](model);
				this.currentPage(page.name);	
			});
		} else {
			this.currentPage(page.name);
		}
  	};

  	goToPage = function (pageName, id) {
  		// knockout puts the model in with the arguments automatically
  		// this makes the id work as expected
  		if (typeof id !== 'string') id = null;
  		
  		const page = Router.getPage(pageName);

  		this[page.modelMethod]((model) => {
			document.title = 'The Discoverables: ' + page.title;
  			this[page.viewMethod](model);
  			const path = [Router.prefix, page.name, id].filter(val => val).join('/');
  			history.pushState(model, page.title, path);
  			this.currentPage(page.name);
  		}, id);
  	};

	templateName = (model) => {
		// clear the validation errors when changing pages
		this.validationErrors.removeAll();// = ko.observableArray([]);
		return model.currentPage() + '-template';
	};

	// page routing methods

	// Users
	loadUsers = (callback) => {
		this.userConnector.getAll(callback);
	};

	putUsersInViewModel = (data) => {
		this.users.removeAll();
		data.forEach(user => {
			this.users.push(ko.mapping.fromJS(user));
		});
	};

	loadUser = (callback, id) => {
		// todo: go to the service
		let user = (id) 
			? ko.mapping.toJS(this.users().find(u => u.id() == id))
			: null;
		callback(user);
	};

	putUserInViewModel = (data) => {
		if (data) {
			this.userToUpdate = ko.mapping.fromJS(data);
		} else {
			this.userToUpdate = new UserViewModel();
		}new UserViewModel()
	};

	// User methods

	createUser = (formElement) => {
		this.userConnector.create(
			ko.mapping.toJS(this.userToUpdate), 
			this.goToPage(this, 'users'),
			this.validationErrorsCallback
		);
	};

	updateUser = () => {
		this.userConnector.update(
			 ko.mapping.toJS(this.userToUpdate), 
			 this.goToPage(this, 'users'),
			 this.validationErrorsCallback);
	};

	deleteUser = (user) => {
		$.confirm({
		    title: 'Delete user?',
		    content: 'Are you sure you want to delete ' + user.firstName() + " " + user.lastName() + "'s account?",
		    boxWidth: '500px',
			useBootstrap: false,
			draggable: true,
			dragWindowBorder: false,
			backgroundDismiss: true,
			animation: 'none',
		    buttons: {
		        confirm: {
		        	btnClass: 'btn-blue',
		        	action: () => {
		        		user.statusId = 2; //INACTIVE_USER_STATUS_ID
						this.userConnector.update(ko.mapping.toJS(user), this.goToPage(this, 'users'));
					},
					keys: ['enter']
		        },
		        cancel: {
		        	keys: ['esc']
		        }
		    }
		});
	};


	//password methods
	sendPasswordReset = (user) => {
		let successCallback = () => {
			$.alert({
				title: 'Done!',
				content: 'Email sent!',
				boxWidth: '500px',
				useBootstrap: false,
				animation: 'none',
				escapeKey: 'esc',
				backgroundDismiss: true,
				autoClose: 'Okay|2000',
				buttons: {
			        Okay: {
			        	action: () => { },
			        	keys: ['enter']
			        }
			    }
			});
        };

		$.confirm({
		    title: 'Request password reset?',
		    content: 'Send password reset email to ' + user.email() + '?',
		    boxWidth: '500px',
			useBootstrap: false,
			draggable: true,
			dragWindowBorder: false,
			backgroundDismiss: true,
			animation: 'none',
		    buttons: {
		        confirm: {
		        	btnClass: 'btn-blue',
		        	action: () => {
						this.userConnector.requestPasswordReset(
							ko.mapping.toJS(user), 
							successCallback);
					},
					keys: ['enter']
		        },
		        cancel: {
		        	keys: ['esc']
		        }
		    }
		});
	};

	requestPasswordReset = (formElement) => {
		//todo: use view model instead of form element
		let input = {};
		$(formElement).serializeArray().map((x) => { input[x.name] = x.value; });
		
		const processingAltert = $.alert({
				title: 'Processed',
				content: 'Your request is being processed.',
				boxWidth: '500px',
				useBootstrap: false,
				lazyOpen: true,
				animation: 'none',
				buttons: {}
			});

		let hasValidationIssues = false;
		setTimeout(() => {
			if(!hasValidationIssues) {
				if (processingAltert.isClosed()) processingAltert.open();
			}
		}, 300);

		const successCallback = (data, textStatus, jqXHR) => {
			hasValidationIssues = false;
			if (processingAltert.isOpen()) processingAltert.close();
			this.validationErrors([]);
			$.alert({
				title: 'Done!',
				content: 'An email was sent to <b>' + data.user.email + '</b>. Follow the directions in that eamil to reset your password.',
				boxWidth: '500px',
				useBootstrap: false,
				animation: 'none',
				backgroundDismiss: true,
				escapeKey: 'esc',
				buttons: {
			        OK: {
			        	action: () => { 
							this.currentPage('login');
			        	},
			        	keys: ['enter']
			        }
			    }
			});
        };

        const failedCallback = (data) => {
        	hasValidationIssues = true;
        	if (processingAltert.isOpen()) processingAltert.close();
        	this.validationErrors(Object.values(data.errorMessages).reverse());
	    };

		this.userConnector.requestPasswordReset(
				input, 
				successCallback, 
				failedCallback);
	};

	openPasswordResetForm = () => {
		this.currentPage("passwordrecovery");
	};

	cancelPasswordResetRequest = () => {
		this.currentPage("login");
	};

	// Song actions
	loadSongs = (callback) => {
		this.songConnector.getAll(callback);
	};

	putSongsInViewModel = (data) => {
		this.songs.removeAll();
		data.forEach(song => {
		  this.songs.push(ko.mapping.fromJS(song));
		});
	};
	
	loadSong = (callback, id) => {
		if (id) {
			this.songConnector.get(id, callback);
		} else {
			callback(null)
		}
	};

	putSongInViewModel = (data) => {
		if (data) {
			this.songToUpdate = ko.mapping.fromJS(data);
			// todo: see if there's a way to avoid setting reader here
			this.songToUpdate.reader = new FileReader();
		} else {
			this.songToUpdate = new SongViewModel();
		}
	};

	createSong = () => {
		this.songConnector.create(
			ko.mapping.toJS(this.songToUpdate),
			this.goToPage(this, 'songs'),
			this.validationErrorsCallback
		);
	};

	updateSong = () => {
		this.songConnector.update(
			ko.mapping.toJS(this.songToUpdate),
			this.goToPage(this, 'songs'),
			this.validationErrorsCallback
		);
	};

	deleteSong = (song) => {
		$.confirm({
		    title: 'Delete song?',
		    content: 'Are you sure you want to delete "' + song.title() + '"?',
		    boxWidth: '500px',
			useBootstrap: false,
			draggable: true,
			dragWindowBorder: false,
			backgroundDismiss: true,
			animation: 'none',
		    buttons: {
		        confirm: {
		        	btnClass: 'btn-blue',
		        	action: () => {
						this.songConnector.deleteThing(
							ko.mapping.toJS(song), this.goToPage(this, 'songs'));
					},
					keys: ['enter']
		        },
		        cancel: {
		        	keys: ['esc']
		        }
		    }
		});
	};

	loadPlaylists = (callback) => {
		this.playlistConnector.getAll(callback);
	};

	putPlaylistsInViewModel = (data) => {
		this.playlists.removeAll();
		data.forEach(playlist => this.playlists.push(ko.mapping.fromJS(playlist)));
	};
	
	loadPlaylist = (callback, id) => {
		// make sure the songs are loaded before getting the playlists
		if (this.songs().length === 0) {
			this.loadSongs(this.putSongsInViewModel);
		}
		if (id) {
			this.playlistConnector.get(id, callback);
		} else {
			callback({id: null, title: null, description: null, songs: []});
		}
	};

	putPlaylistInViewModel = (data) => {
		this.playlistToUpdate = ko.mapping.fromJS(data);
	};


	loadAlbums = (callback) => {
		this.albumConnector.getAll(callback);
	};

	putAlbumsInViewModel = (data) => {
		this.albums.removeAll();
		data.forEach(album => this.albums.push(ko.mapping.fromJS(album)));
	};
	
	loadAlbum = (callback, id) => {
		// make sure the playlists are loaded before getting the albums
		if (this.playlists().length === 0) {
			this.loadPlaylists(this.putPlaylistsInViewModel);
		}
		if (id) {
			this.albumConnector.get(id, callback);
		} else {
			callback({id: null, title: null, description: null, playlist: null});
		}
	};

	putAlbumInViewModel = (data) => {
		this.albumToUpdate = ko.mapping.fromJS(data);
	};

	cancelAlbumForm = () => {
		this.goToPage('albums');
	};


	// Authentication methods
	login = (formElement) => {
		let input = {};
		$(formElement).serializeArray().map((x) => {input[x.name] = x.value;});
		this.userConnector.authenticate(input, 
			(data, textStatus, jqXHR) => {
            	this.isAuthenticated(data.authenticated);
				ko.mapping.fromJS(data.user, this.administrator);
				this.goToPage('songs');
            }, 
            (data, textStatus, errorThrown) => {
            	this.isAuthenticated(false);
            	this.validationErrors(Object.values(data.errorMessages).reverse());
		    });
	};

	logout = () => {
		let done = (data, textStatus, jqXHR) => {
        	this.isAuthenticated(false);
        	this.administrator = new UserViewModel();
        	this.currentPage('login');
        };
		this.userConnector.logout(done, done);
	};


	// Playlist methods

	createPlaylist = (formElement) => {
		this.playlistConnector.create(
			ko.mapping.toJS(this.playlistToUpdate),
			this.goToPage.bind(this, 'playlists'),
			this.validationErrorsCallback
		);
	};

	updatePlaylist = (formElement) => {
		this.playlistConnector.update(
			ko.mapping.toJS(this.playlistToUpdate),
			this.goToPage.bind(this, 'playlists'), 
			this.validationErrorsCallback
		);
	};

	deletePlaylist = (playlist) => {
		let successCallback = this.goToPage.bind(this, 'playlists');

        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				$.alert({
					title: 'Problem!',
					content: Object.values(data.errorMessages).reverse().join(','),
					boxWidth: '500px',
					useBootstrap: false,
					animation: 'none',
					backgroundDismiss: true,
					escapeKey: 'esc',
					buttons: {
				        OK: {
				        	action: () => { },
				        	keys: ['enter']
				        }
				    }
				});
			}
			console.log('request failed! ' + textStatus);
	    };

		$.confirm({
		    title: 'Delete playlist?',
		    content: 'Are you sure you want to delete "' + playlist.title() + '"?',
		    boxWidth: '500px',
			useBootstrap: false,
			draggable: true,
			dragWindowBorder: false,
			backgroundDismiss: true,
			animation: 'none',
		    buttons: {
		        confirm: {
		        	btnClass: 'btn-blue',
		        	action: () => {
						this.playlistConnector.deleteThing(
							ko.mapping.toJS(playlist), successCallback, failedCallback);
					},
					keys: ['enter']
		        },
		        cancel: {
		        	keys: ['esc']
		        }
		    }
		});
	};

	// playlist helper
    togglePlaylistSongAssociation = function(playlist, song) {
    	const searchMethod = item => item.id() == song.id();
    	if (playlist.songs().find(searchMethod)) {
    		playlist.songs.remove(searchMethod);
    	} else {
    		playlist.songs.push(song);
    	}
        return true;
    };

	// Albums

	createAlbum = (formElement) => {
		//todo: use view model instead of form element
		let input = {};
		$(formElement).serializeArray().map((x) => {
			if (x.name == 'playlistId') {
				let playlistId = x.value;
				input.playlist = ko.mapping.toJS(this.playlists().find(pl => pl.id() == playlistId));
			} else {
				input[x.name] = x.value;
			}
		});

		this.albumConnector.create(
			input, 
			this.goToPage.bind(this, 'albums'),
			this.validationErrorsCallback
		);
	};

	updateAlbum = (formElement) => {
		//todo: use view model instead of form element
		let input = {};
		$(formElement).serializeArray().map((x) => { 
			if (x.name == 'playlistId') {
				let playlistId = x.value;
				input.playlist = ko.mapping.toJS(this.playlists().find(pl => pl.id() == playlistId));
			} else {
				input[x.name] = x.value;
			}
		});

		this.albumConnector.update(
			input, 
			this.goToPage.bind(this, 'albums'),
			this.validationErrorsCallback
		);
	};

	deleteAlbum = (album) => {
		$.confirm({
		    title: 'Delete album?',
		    content: 'Are you sure you want to delete "' + album.title() + '"?',
		    boxWidth: '500px',
			useBootstrap: false,
			draggable: true,
			dragWindowBorder: false,
			backgroundDismiss: true,
			animation: 'none',
		    buttons: {
		        confirm: {
		        	btnClass: 'btn-blue',
		        	action: () => {
						this.albumConnector.deleteThing(
							ko.mapping.toJS(album), this.goToPage.bind($this, 'albums'));
					},
					keys: ['enter']
		        },
		        cancel: {
		        	keys: ['esc']
		        }
		    }
		});
	};

	// album helper
	isAlbumPlaylist = (album, playlist) => {
		return album.playlist.id() == playlist.id();
	};

	validationErrorsCallback = (data) => {
    	if (data.errorMessages) {
			this.validationErrors(Object.values(data.errorMessages).reverse());
		}
    };
}


