
function AdminViewModel(administrator, blankAdministrator, isAuthenticated) {
	var self = this;
	this.blankAdministrator = blankAdministrator;
	this.isAuthenticated = ko.observable(isAuthenticated);
	this.administrator = ko.mapping.fromJS(administrator);
	this.loginErrors = ko.observableArray([]);
	this.userConnector = new UserConnector();
	this.currentPage = ko.observable(isAuthenticated ? 'blank' : 'login');
	this.users = ko.observableArray();
	this.userToUpdate = ko.observable();
	this.validationErrors = ko.observableArray([]);
	this.pageToDisplay = function(model) {
			return model.currentPage() + '-template';
	};

	this.login = function(formElement) {
		var input = {};
		$(formElement).serializeArray().map(function(x){input[x.name] = x.value;});
		this.userConnector.authenticate(input, 
			function (data, textStatus, jqXHR) {
				self.loginErrors([]);
            	self.isAuthenticated(data.authenticated);
				ko.mapping.fromJS(data.user, self.administrator);
				self.currentPage('music');
            }, 
            function(data, textStatus, errorThrown) {
            	self.isAuthenticated(false);
            	self.loginErrors(Object.values(data.errorMessages).reverse());
		    });
	};

	this.logout = function() {
		var done = function (data, textStatus, jqXHR) {
        	self.isAuthenticated(false);
        	ko.mapping.fromJS(self.blankAdministrator, self.administrator);
        	self.currentPage('login');
        };
		this.userConnector.logout(done, done);
	};

	this.openUsers = function() {
		var successCallback = function (data, textStatus, jqXHR) {
			self.users.removeAll();
			for (var i = 0; i < data.length; i++) {
				self.users.push(ko.mapping.fromJS(data[i]));
			}
			self.currentPage('users');
        };
        var failedCallback = function(data, textStatus, errorThrown) {
			console.log('request failed! ' + textStatus);
	    };
		this.userConnector.getUsers(successCallback, failedCallback);
	};

	this.openCreateUser = function() {
		self.currentPage('create-user');
	};

	this.openEditUser = function(root, user) {
		root.userToUpdate(user);
		root.currentPage('edit-user');
	};

	this.deleteUser = function(root, user) {
		var successCallback = function (data, textStatus, jqXHR) {
			self.openUsers();
        };

        var failedCallback = function(data, textStatus, errorThrown) {
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
		        	action: function () {
		        		user.statusId = 2; //INACTIVE_USER_STATUS_ID
						root.userConnector.updateUser(
							ko.mapping.toJS(user), 
							successCallback, 
							failedCallback);
					},
					keys: ['enter']
		        },
		        cancel: {
		        	action: function () { },
		        	keys: ['esc']
		        }
		    }
		});
	};

	this.createUser = function(formElement) {
		var input = {};
		$(formElement).serializeArray().map(function(x){input[x.name] = x.value;});
		if (input.password != input.confirmPassword) {
			self.validationErrors(["Passwords do not match. Please correct."]);
			return;
		}

		var successCallback = function (data, textStatus, jqXHR) {
			self.openUsers();
        };
        var failedCallback = function(data, textStatus, errorThrown) {
        	if (data.errorMessages) {
				self.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		self.userConnector.createUser(input, successCallback, failedCallback);
	};

	this.updateUser = function() {
		var successCallback = function (data, textStatus, jqXHR) {
			self.openUsers();
        };
        var failedCallback = function(data, textStatus, errorThrown) {
        	if (data.errorMessages) {
				self.validationErrors(Object.values(data.errorMessages).reverse());
			}
			console.log('request failed! ' + textStatus);
	    };
		self.userConnector.updateUser(
			 ko.mapping.toJS(self.userToUpdate()), successCallback, failedCallback);
	};

	this.cancelUserForm = function() {
		self.currentPage('users');
	};

	this.openPasswordResetForm = function() {
		self.currentPage("password-recovery");
	};

	this.cancelPasswordResetRequest = function() {
		self.currentPage("login");
	};

	this.requestPasswordReset = function(formElement) {
		var input = {};
		$(formElement).serializeArray().map(function(x){input[x.name] = x.value;});
		
		var processingAltert = $.alert({
				title: 'Processed',
				content: 'Your request is being processed.',
				boxWidth: '500px',
				useBootstrap: false,
				lazyOpen: true,
				animation: 'none',
				buttons: {}
			});

		var hasValidationIssues = false;
		setTimeout(function() {
			if(!hasValidationIssues) {
				if (processingAltert.isClosed()) processingAltert.open();
			}
		}, 300);

		var successCallback = function (data, textStatus, jqXHR) {
			hasValidationIssues = false;
			if (processingAltert.isOpen()) processingAltert.close();
			self.loginErrors([]);
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
			        	action: function () { 
							self.currentPage('login');
			        	},
			        	keys: ['enter']
			        }
			    }
			});

        };
        var failedCallback = function(data, textStatus, errorThrown) {
        	hasValidationIssues = true;
        	if (processingAltert.isOpen()) processingAltert.close();
        	self.loginErrors(Object.values(data.errorMessages).reverse());
	    };

		self.userConnector.requestPasswordReset(
				input, 
				successCallback, 
				failedCallback);
	};

	this.sendPasswordReset = function (root, user) {
		var successCallback = function (data, textStatus, jqXHR) {
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
			        	action: function () { },
			        	keys: ['enter']
			        }
			    }
			});
        };
        var failedCallback = function(data, textStatus, errorThrown) {
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
		        	action: function () {
						root.userConnector.requestPasswordReset(
							ko.mapping.toJS(user), 
							successCallback, 
							failedCallback);
					},
					keys: ['enter']
		        },
		        cancel: {
		        	action: function () { },
		        	keys: ['esc']
		        }
		    }
		});
	};

	this.openMusic = function() {
		self.currentPage('music');
	};
};