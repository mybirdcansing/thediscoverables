
class SongViewModel 
{
	constructor() {
		this.id = ko.observable();
		this.title = ko.observable();
		this.description = ko.observable();
	    this.filename = ko.observable();
	    this.fileInput = ko.observable();
		this.reader = new FileReader();
	}

}