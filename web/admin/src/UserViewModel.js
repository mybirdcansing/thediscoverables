class UserViewModel {
	constructor(user) {
		this.id = ko.observable();
		this.username = ko.observable();
		this.firstName = ko.observable();
		this.lastName = ko.observable();
		this.email = ko.observable();
		this.password = ko.observable();
		this.confirmPassword = ko.observable();
	}
}