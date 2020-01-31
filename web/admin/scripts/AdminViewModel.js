"use strict"
class AdminViewModel {

	constructor(administrator, blankAdministrator, isAuthenticated) {
		this.blankAdministrator = blankAdministrator;
		this.isAuthenticated = ko.observable(isAuthenticated);
		this.administrator = ko.mapping.fromJS(administrator);
		this.loginErrors = ko.observableArray([]);
		this.userConnector = new UserConnector();
		this.songConnector = new SongConnector();
		this.playlistConnector = new PlaylistConnector();
		this.albumConnector = new AlbumConnector();
		this.currentPage = ko.observable(isAuthenticated ? 'blank' : 'login');
		this.users = ko.observableArray();
		this.songs = ko.observableArray();
		this.playlists = ko.observableArray();
		this.albums = ko.observableArray();
		this.userToUpdate = ko.observable();
		this.playlistToUpdate = ko.observable();
		this.albumToUpdate = ko.observable();
		this.validationErrors = ko.observableArray([]);
		this.songToUpdate;
		this.songToCreate;
  	}

	pageToDisplay = (model) => {
		this.validationErrors = ko.observableArray([]);
		return model.currentPage() + '-template';
	}

	// Authentication methods
	login = (formElement) => {
		let input = {};
		$(formElement).serializeArray().map((x) => {input[x.name] = x.value;});
		this.userConnector.authenticate(input, 
			(data, textStatus, jqXHR) => {
				this.loginErrors([]);
            	this.isAuthenticated(data.authenticated);
				ko.mapping.fromJS(data.user, this.administrator);
				this.openSongs();
            }, 
            (data, textStatus, errorThrown) => {
            	this.isAuthenticated(false);
            	this.loginErrors(Object.values(data.errorMessages).reverse());
		    });
	}

	logout = () => {
		let done = (data, textStatus, jqXHR) => {
        	this.isAuthenticated(false);
        	ko.mapping.fromJS(this.blankAdministrator, this.administrator);
        	this.currentPage('login');
        };
		this.userConnector.logout(done, done);
	}

	// User methods
	openUsers = () => {
		let successCallback = (users, textStatus, jqXHR) => {
			this.users.removeAll();
			users.forEach(user => {
				this.users.push(ko.mapping.fromJS(user));
			});
			this.currentPage('users');
        };
        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };
		this.userConnector.getAll(successCallback, failedCallback);
	}


	openCreateUser = () => {
		this.currentPage('create-user');
	}

	openEditUser = (user) => {
		this.userToUpdate(user);
		this.currentPage('edit-user');
	}

	deleteUser = (user) => {
		let successCallback = (data, textStatus, jqXHR) => {
			this.openUsers();
        };

        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };

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
						this.userConnector.update(
							ko.mapping.toJS(user), 
							successCallback, 
							failedCallback);
					},
					keys: ['enter']
		        },
		        cancel: {
		        	action: () => { },
		        	keys: ['esc']
		        }
		    }
		});
	}

	createUser = (formElement) => {
		let input = {};
		$(formElement).serializeArray().map((x) => {input[x.name] = x.value;});
		if (input.password != input.confirmPassword) {
			this.validationErrors(["Passwords do not match. Please correct."]);
			return;
		}

		let successCallback = (data, textStatus, jqXHR) => {
			this.openUsers();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				this.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		this.userConnector.create(input, successCallback, failedCallback);
	}

	updateUser = () => {
		let successCallback = (data, textStatus, jqXHR) => {
			this.openUsers();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				this.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		this.userConnector.update(
			 ko.mapping.toJS(this.userToUpdate()), successCallback, failedCallback);
	}

	cancelUserForm = () => {
		this.currentPage('users');
	}

	sendPasswordReset = (user) => {
		let successCallback = (data, textStatus, jqXHR) => {
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
        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
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
							successCallback, 
							failedCallback);
					},
					keys: ['enter']
		        },
		        cancel: {
		        	action: () => { },
		        	keys: ['esc']
		        }
		    }
		});
	}

	requestPasswordReset = (formElement) => {
		let input = {};
		$(formElement).serializeArray().map((x) => { input[x.name] = x.value; });
		
		let processingAltert = $.alert({
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

		let successCallback = (data, textStatus, jqXHR) => {
			hasValidationIssues = false;
			if (processingAltert.isOpen()) processingAltert.close();
			this.loginErrors([]);
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
        let failedCallback = (data, textStatus, errorThrown) => {
        	hasValidationIssues = true;
        	if (processingAltert.isOpen()) processingAltert.close();
        	this.loginErrors(Object.values(data.errorMessages).reverse());
	    };

		this.userConnector.requestPasswordReset(
				input, 
				successCallback, 
				failedCallback);
	}

	openPasswordResetForm = () => {
		this.currentPage("password-recovery");
	}

	cancelPasswordResetRequest = () => {
		this.currentPage("login");
	}

	// Songs methods
	openSongs = () => {
		this.loadSongs(() => {this.currentPage('songs');});
	}

	// Songs methods
	loadSongs = (callback) => {
		let successCallback = (data, textStatus, jqXHR) => {
			this.songs.removeAll();

			data.forEach(song => {
			  this.songs.push(ko.mapping.fromJS(song));
			});
			if (callback) callback();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };
		this.songConnector.getAll(successCallback, failedCallback);
	}

	createSong = (formElement) => {
		debugger;
		let successCallback = (data, textStatus, jqXHR) => {
			this.openSongs();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				this.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
	    let song = ko.mapping.toJS(this.songToCreate);
		this.songConnector.create(song, successCallback, failedCallback);
	}

	updateSong = () => {
		let successCallback = (data, textStatus, jqXHR) => {
			this.openSongs();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				this.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		this.songConnector.update(ko.mapping.toJS(this.songToUpdate), successCallback, failedCallback);
	}

	openCreateSong = () => {
		this.songToCreate = new SongViewModel();
		this.currentPage('create-song');
	}

	openEditSong = (song) => {
		this.songToUpdate = new SongViewModel(song);
		this.currentPage('edit-song');
	}

	cancelSongForm = () => {
		this.currentPage('songs');
	}

	deleteSong = (song) => {
		let successCallback = (data, textStatus, jqXHR) => {
			this.openSongs();
        };

        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };

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
							ko.mapping.toJS(song), successCallback, failedCallback);
					},
					keys: ['enter']
		        },
		        cancel: {
		        	action: () => { },
		        	keys: ['esc']
		        }
		    }
		});
	}

	// Playlist methods

	openPlaylists = () => {
		this.loadSongs();
		this.loadPlaylists(() => {this.currentPage('playlists');});
	}


	loadPlaylists = (callback) => {
		let successCallback = (data, textStatus, jqXHR) => {
			this.playlists.removeAll();

			data.forEach(playlist => {
			  this.playlists.push(ko.mapping.fromJS(playlist));
			});
			if (callback) callback();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };
		this.playlistConnector.getAll(successCallback, failedCallback);
	}

	openCreatePlaylist = () => {
		this.currentPage('create-playlist');
	}

	cancelPlaylistForm = () => {
		this.currentPage('playlists');
	}
	_addFormDataToPlaylistInput = (input, formElement) => {
		$(formElement).serializeArray().map((x) => {
			if (x.name == 'songs') {
				if (!input.hasOwnProperty('songs')) {
					input.songs = [];
				}
				input.songs[input.songs.length] = ko.mapping.toJS(this.songs().find(song => song.id = x.value));
			} else {
				input[x.name] = x.value;
			}
		});
	}

	createPlaylist = (formElement) => {
		let input = {};
		this._addFormDataToPlaylistInput(input, formElement);

		let successCallback = (data, textStatus, jqXHR) => {
			this.openPlaylists();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				this.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		this.playlistConnector.create(input, successCallback, failedCallback);
	}

	openEditPlaylist = (playlist) => {
		let successCallback = (playlist, textStatus, jqXHR) => {
			this.playlistToUpdate(ko.mapping.fromJS(playlist));
			this.currentPage('edit-playlist');
        };
        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };
		this.playlistConnector.get(playlist.id(), successCallback, failedCallback);
	}

	updatePlaylist = (formElement) => {
		let input = {};
		this._addFormDataToPlaylistInput(input, formElement);

		let successCallback = (data, textStatus, jqXHR) => {
			this.openPlaylists();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				this.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		this.playlistConnector.update(input, successCallback, failedCallback);
	}

	deletePlaylist = (playlist) => {
		let successCallback = (data, textStatus, jqXHR) => {
			this.openPlaylists();
        };

        let failedCallback = (data, textStatus, errorThrown) => {
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
		        	action: () => {},
		        	keys: ['esc']
		        }
		    }
		});
	}

	// Albums




	openAlbums = () => {
		let successCallback = (albums, textStatus, jqXHR) => {
			this.albums.removeAll();
			albums.forEach(album => {
				this.albums.push(ko.mapping.fromJS(album));
			});
			this.loadPlaylists();
			this.currentPage('albums');
        };
        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };
		this.albumConnector.getAll(successCallback, failedCallback);
	}


	openCreateAlbum = () => {
		if (this.playlists().length < 1) {
			$.alert({
				title: 'Sorry!',
				content: 'Please make a playlist before you make an Album',
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
        } else {
			this.currentPage('create-album');
        }
		
	}

	cancelAlbumForm = () => {
		this.currentPage('albums');
	}

	createAlbum = (formElement) => {
		let input = {};
		$(formElement).serializeArray().map((x) => {
			if (x.name == 'playlistId') {
				let playlistId = x.value;
				input.playlist = ko.mapping.toJS(this.playlists().find(pl => pl.id() == playlistId));
			} else {
				input[x.name] = x.value;
			}
		});

		let successCallback = (data, textStatus, jqXHR) => {
			this.openAlbums();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				this.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		this.albumConnector.create(input, successCallback, failedCallback);
	}

	openEditAlbum = (album) => {
		let successCallback = (album) => {
			this.albumToUpdate(ko.mapping.fromJS(album));
			this.currentPage('edit-album');
        };
        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };
		this.albumConnector.get(album.id(), successCallback, failedCallback);
	}

	isAlbumPlaylist = (album, playlist) => {
		return album.playlist.id() == playlist.id();
	}

	updateAlbum = (formElement) => {
		let input = {};
		$(formElement).serializeArray().map((x) => { 
			if (x.name == 'playlistId') {
				let playlistId = x.value;
				input.playlist = ko.mapping.toJS(this.playlists().find(pl => pl.id() == playlistId));
			} else {
				input[x.name] = x.value;
			}
		});

		let successCallback = (data, textStatus, jqXHR) => {
			this.openAlbums();
        };
        let failedCallback = (data, textStatus, errorThrown) => {
        	if (data.errorMessages) {
				this.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		this.albumConnector.update(input, successCallback, failedCallback);
	}

	deleteAlbum = (album) => {
		let successCallback = (data, textStatus, jqXHR) => {
			this.openAlbums();
        };

        let failedCallback = (data, textStatus, errorThrown) => {
			console.log('request failed! ' + textStatus);
	    };

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
							ko.mapping.toJS(album), successCallback, failedCallback);
					},
					keys: ['enter']
		        },
		        cancel: {
		        	action: () => {},
		        	keys: ['esc']
		        }
		    }
		});
	}
}


