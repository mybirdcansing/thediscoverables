/*

Problems:

1) When a browser history entry is requested by clicking
or tapping on a browser's back button (or forward button) for example, 
the page and state corresponding to the history entry should be displayed. 

Form field changes that were not saved should be maintained. 
Except for login form values. Modles will not be maintained.

If a page gets refreshed, the changes will be lost, and the 
page will display the last saved content.

Use case: 
An admin is updating a song description and goes to another page before saving. 

When she navigates back to the page (using the browser back button),
the page will display the changes that were not yet saved.

If the underlying data was modified since the data was put into the history state,
show a message and prevent the content from displaying.

2) Deep linking should work. If a page is opened from a bookmark, or by typing a url,
the page should display with the last saved data. This might not work for all pages.


Solution:

Use the Javascript History API to store and retrieve state.

When a user clicks a link to go to an application page,
a method will be called to get the viewModel from a service, then
a method will be called to dispatch the the model to the view,
history.pushState() will be called, the page title will be updated,
and the page will be displayed

When content in a form is changed, history.replaceState()
will be called updating the state

When a user clicks the back button to go back to a page in the application,
the window.onpopstate event will fire, then
if the page's state is to be maintained, 
the viewModel will be retreived from the event, otherwise 
a method will be called to get the viewModel from the service then
in this case, the url after the path will be broken into arguments for the method that gets the model
a method to dispatch the viewModel to the view will be called,
and the page will be displayed



*/

class Page {
	#title;
	constructor(name, title, maintainPageState, modelMethod, viewMethod) {
		this.name = name;
		this.#title = title
		this.maintainPageState = maintainPageState;
		this.modelMethod = modelMethod;
		this.viewMethod = viewMethod;
	}

	get title() {
	    return `The Discoverables: ${this.#title}`;
  	}
}

class Router {
	static prefix = '/admin';

	static pages = [
		new Page('blank', 'Home', false, null, null),
		new Page('users', 'Users', false, 'loadUsers', 'putUsersInViewModel'),
		new Page('createuser', 'Create User', true, 'loadUser', 'putUserInViewModel'),
		new Page('user', 'Edit User', true, 'loadUser', 'putUserInViewModel'),
		new Page('songs', 'Songs', false, 'loadSongs', 'putSongsInViewModel'),
		new Page('createsong', 'Create Song', true, 'loadSong', 'putSongInViewModel'),
		new Page('song', 'Edit Song', true, 'loadSong', 'putSongInViewModel'),
		new Page('playlists', 'Playlists', false, 'loadPlaylists', 'putPlaylistsInViewModel'),
		new Page('createplaylist', 'Create Playlist', true, 'loadPlaylist', 'putPlaylistInViewModel'),
		new Page('playlist', 'Edit Playlist', true, 'loadPlaylist', 'putPlaylistInViewModel'),
		new Page('albums', 'Albums', false, 'loadAlbums', 'putAlbumsInViewModel'),
		new Page('createalbum', 'Create Album', true, 'loadAlbum', 'putAlbumInViewModel'),
		new Page('album', 'Edit Album', true, 'loadAlbum', 'putAlbumInViewModel')
	];

	static getPage(name) {
		return this.pages.find(page => page.name === name);
	}
}

