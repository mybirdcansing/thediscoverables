"use strict"
// import {Router} from "./Router.js";
// import {Page} from "./Router.js";

class AdminViewModel {

	constructor(administrator, isAuthenticated) {
		this.isAuthenticated = ko.observable(isAuthenticated);
		this.administrator = ko.mapping.fromJS(new UserViewModel(), administrator);

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




	// page routing methods
  	handlePopState = (event) => {
			//router.navigate(Backbone.history.getFragment(), { trigger: true, replace: true });

  		const path = event.path[0].location.pathname;
  		const pathPieces = path.split('/');

	  	const regex = /(?<=\/admin\/)(.*)(?=\/?)/gmi;
			// const str = `http://localhost/admin/playlist/250F1F3C-70BD-4452-A2C6-C84B9BFDE581`;
			let m = regex.exec(path);
			debugger;
			while ((m = regex.exec(path)) !== null) {
			    // This is necessary to avoid infinite loops with zero-width matches
			    if (m.index === regex.lastIndex) {
			        regex.lastIndex++;
			    }
			    // The result can be accessed through the `m`-variable.
			    m.forEach((match, groupIndex) => {
			        console.log(`Found match, group ${groupIndex}: ${match}`);
			    });
			}

	  	const pageName = (pathPieces.length > 1) ? pathPieces[2] : 'blank';
			const page = Router.getPage(pageName);
			if (page.maintainPageState) {
				let viewModel = this[page.viewMethod](event.state);
		  		this.maintainHistoryState(viewModel, page.title, path);
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

  		// clear the validation errors when changing pages
		this.validationErrors.removeAll();

  		const page = Router.getPage(pageName);

  		this[page.modelMethod]((model) => {
			document.title = page.title;
  			const viewModel = this[page.viewMethod](model);
  			const path = [Router.prefix, page.name, id].filter(val => val).join('/');
  			history.pushState(model, page.title, path);
  			if (page.maintainPageState) {
	  			this.maintainHistoryState(viewModel, page.title, path);
  			}
  			this.currentPage(page.name);
  		}, id);
  	};

  	maintainHistoryState = (viewModel, title, path) => {
  		ko.computed(() => {
		    return ko.mapping.toJS(viewModel);
		}).subscribe((state) => {
			history.replaceState(state, title, path);
		});
	};

	templateName = (model, context) => {
		return model.currentPage() + '-template';
	};

	// Authentication methods
	login = () => {
		this.userConnector.authenticate(ko.toJS(this.administrator),
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


	// Users methods
	loadUsers = (callback) => {
		this.userConnector.getAll(callback);
	};

	putUsersInViewModel = (data) => {
		this.users.removeAll();
		data.forEach(user => {
			this.users.push(ko.mapping.fromJS(user));
		});
		return this.users;
	};

	loadUser = (callback, id) => {
		if (id) {
			this.userConnector.get(id, callback);
		} else {
			callback(null);
		}
	};

	putUserInViewModel = (data) => {
		if (data) {
			this.userToUpdate = ko.mapping.fromJS(data);
		} else {
			this.userToUpdate = new UserViewModel();
		}
		return this.userToUpdate;
	};

	createUser = () => {
		this.userConnector.create(
			ko.mapping.toJS(this.userToUpdate),
			this.goToPage.bind(this, 'users'),
			this.validationErrorsCallback
		);
	};

	updateUser = () => {
		this.userConnector.update(
			 ko.mapping.toJS(this.userToUpdate),
			 this.goToPage.bind(this, 'users'),
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
						this.userConnector.update(ko.mapping.toJS(user), this.goToPage.bind(this, 'users'));
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
		const successCallback = () => {
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

	requestPasswordReset = () => {

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
		setTimeout(function() {
			if(!hasValidationIssues && processingAltert.isClosed()) {
				processingAltert.open();
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
        	if (processingAltert.isOpen()) {
        		processingAltert.close();
        	}
        	this.validationErrors(Object.values(data.errorMessages).reverse());
	    };

		this.userConnector.requestPasswordReset(
				ko.toJS(this.administrator),
				successCallback,
				failedCallback);
	};

	openPasswordResetForm = () => {
		this.administrator = new UserViewModel();
		this.currentPage("passwordrecovery");
	};

	cancelPasswordResetRequest = () => {
		this.currentPage("login");
	};

	// Song methods
	loadSongs = (callback) => {
		this.songConnector.getAll(callback);
	};

	putSongsInViewModel = (data) => {
		this.songs.removeAll();
		data.forEach(song => {
		  this.songs.push(ko.mapping.fromJS(song));
		});
		return this.songs;
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
		return this.songToUpdate;
	};

	createSong = () => {
		this.songConnector.create(
			ko.mapping.toJS(this.songToUpdate),
			this.goToPage.bind(this, 'songs'),
			this.validationErrorsCallback
		);
	};

	updateSong = () => {
		this.songConnector.update(
			ko.mapping.toJS(this.songToUpdate),
			this.goToPage.bind(this, 'songs'),
			this.validationErrorsCallback
		);
	};

	deleteSong = (song) => {
		$.confirm({
		    title: 'Delete song?',
		    content: `Are you sure you want to delete "${song.title()}"?`,
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
							ko.mapping.toJS(song), this.goToPage.bind(this, 'songs'));
					},
					keys: ['enter']
		        },
		        cancel: {
		        	keys: ['esc']
		        }
		    }
		});
	};

	// playlist methods
	loadPlaylists = (callback) => {
		this.playlistConnector.getAll(callback);
	};

	putPlaylistsInViewModel = (data) => {
		this.playlists.removeAll();
		data.forEach(playlist => this.playlists.push(ko.mapping.fromJS(playlist)));
		return this.playlists;
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
		return this.playlistToUpdate;
	};

	createPlaylist = () => {
		this.playlistConnector.create(
			ko.mapping.toJS(this.playlistToUpdate),
			this.goToPage.bind(this, 'playlists'),
			this.validationErrorsCallback
		);
	};

	updatePlaylist = () => {
		this.playlistConnector.update(
			ko.mapping.toJS(this.playlistToUpdate),
			this.goToPage.bind(this, 'playlists'),
			this.validationErrorsCallback
		);
	};

	deletePlaylist = (playlist) => {
		$.confirm({
		    title: 'Delete playlist?',
		    content: `Are you sure you want to delete "${playlist.title()}"?`,
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
							ko.mapping.toJS(playlist),
							this.goToPage.bind(this, 'playlists'),
							(data, textStatus) => {
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
								console.log(`request failed! ${textStatus}`);
						    }
						);
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
    	// try to remove the song from the playlist
    	if (playlist.songs.remove(s => s.id() == song.id()).length === 0) {
    		// add the song if it wasn't in the playlist
    		playlist.songs.push(song);
    	}
        return true;
    };

    // album methods
	loadAlbums = (callback) => {
		this.albumConnector.getAll(callback);
	};

	putAlbumsInViewModel = (data) => {
		this.albums.removeAll();
		data.forEach(album => this.albums.push(ko.mapping.fromJS(album)));
		return this.albums;
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
		return this.albumToUpdate;
	};

	setPlaylistAlbumAssociation = (album, playlist) => {
		debugger
		album.playlist(playlist);
		return true;
	};

	createAlbum = () => {
		this.albumConnector.create(
			ko.mapping.toJS(this.albumToUpdate),
			this.goToPage.bind(this, 'albums'),
			this.validationErrorsCallback
		);
	};

	updateAlbum = () => {
		this.albumConnector.update(
			ko.mapping.toJS(this.albumToUpdate),
			this.goToPage.bind(this, 'albums'),
			this.validationErrorsCallback
		);
	};

	deleteAlbum = (album) => {
		$.confirm({
		    title: 'Delete album?',
		    content: `Are you sure you want to delete "${album.title()}"?`,
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
		return album.playlist.id() === playlist.id();
	};

	// base helper methods
	validationErrorsCallback = (data) => {
    	if (data.errorMessages) {
			this.validationErrors(Object.values(data.errorMessages).reverse());
		}
    };
}
