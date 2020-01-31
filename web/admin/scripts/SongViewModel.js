
class SongViewModel 
{
	constructor(songModel) {
		this.id = ko.observable(songModel ? songModel.id() : '');
		this.title = ko.observable(songModel ? songModel.title() : '');
		this.description = ko.observable(songModel ? songModel.description() : '');
	    this.filename = ko.observable(songModel ? songModel.filename() : '');
	    this.fileInput = ko.observable();
		this.reader = new FileReader();
	}
}