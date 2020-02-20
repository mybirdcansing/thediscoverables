//app.js

$(function() {
	ko.applyBindings(
		new AdminViewModel(administrator, isAuthenticated),
		document.body);
});
