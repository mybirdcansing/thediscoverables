class PageBase {

	constructor(title, viewModel) {
		this._title = title;
		this.viewModel = viewModel;
		
		this.userConnector = new UserConnector();
		this.songConnector = new SongConnector();
		this.playlistConnector = new PlaylistConnector();
		this.albumConnector = new AlbumConnector();
	}


	get title() {
	    return 'The Discoverables: ' + this._title;
  	}
}