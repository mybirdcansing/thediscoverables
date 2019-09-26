

function User() {
	this.id = ko.observable();
    this.username = ko.observable();
    this.firstName = ko.observable();
    this.lastName = ko.observable();
    this.email = ko.observable();
    this.statusId = ko.observable();
    this.isAuthenticated =  ko.observable(false);
}