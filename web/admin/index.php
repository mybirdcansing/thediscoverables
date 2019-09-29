<?php

// authorization handler
require_once __DIR__ . '/../lib/consts.php';
require_once __DIR__ . '/../lib/AuthCookie.php';
require_once __dir__ . '/../lib/connecters/DataAccess.php';
require_once __dir__ . '/../lib/connecters/UserData.php';

$blankAdministrator = json_encode(new User());
if (AuthCookie::isValid()) {
	$isAuthenticated = 'true';
	$dbConnection = (new DataAccess())->getConnection();
	$userData = new UserData($dbConnection);
	$administrator = json_encode($userData->getByUsername(AuthCookie::getUsername()));
} else {
	$isAuthenticated = 'false';
	$administrator = $blankAdministrator;
}
echo password_hash('abacadae', PASSWORD_DEFAULT);
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		p {
			font-family: Arial, Geneva, Helvetica, sans-serif;
			font-size: 18pt;
			text-align: center;
		}
		#page {
		    margin:50px 0px; padding:0px;
		    text-align: center;
		    align:center;
		}
		#mainMenue {
			margin:15px 50px; 
			padding:0px;
		    text-align: left;
		    align:left;
		    display: inline-block;
		    float: left;
		}
		form {
    		display: inline-block;
		}
		label, input {
		 display: block;
		 width: 150px;
		 float: left;
		 margin-bottom: 10px;
		}

		label {
		 width: 75px;
		 padding-right: 20px;
		}

		br {
		 clear: left;
		}
	</style>
</head>
<body>
	<p>
	thediscoverables.com html Administration
	</p>
	<div id='mainMenue' 
		data-bind="visible:isAuthenticated" style="display: none;">
		<div id="administratorName">
			<span data-bind="text: administrator.firstName"></span> <span data-bind="text: administrator.lastName"></span>
		</div>
		<div>MENUE</div>
		<div>
			<button data-bind='click: openUsers'>
				Users
			</button>
		</div>
		<div>
			<button data-bind='click: openMusic'>
				Music
			</button>
		</div>
		<div>
			<button data-bind='click: logout'>
				Logout
			</button>
		</div>
	</div>

	<div id='page' data-bind="template: { name: pageToDisplay, data: $data}"></div>

	<script type="text/html" id="login-template">
		<form data-bind="submit: login">
			<label for="username">Username</label>
			<input id="username" name="username"><br>
			<label for="password">Password</label>
			<input id="password" type="password" name="password"><br>
			<input type="submit" name="submit" id="submit" value="submit" class="button" />
		</form>
	</script>

	<script type="text/html" id="users-template">
		<table>
		    <thead>
		        <tr>
		        	<th>Name</th>
		        	<th>Username</th>
		        	<th>Email</th>
		        	<th>&nbsp;</th>
		        </tr>
		    </thead>
		    <tbody data-bind="foreach: users">
		        <tr>
		            <td>
		            	<span data-bind="text: firstName"></span>
		            	<span data-bind="text: lastName"></span>
		            </td>
		            <td>
		            	<span data-bind="text: username"></span>
		            </td>
		            <td>
		            	<span data-bind="text: email"></span>
		            </td>
		            <td>
		            	<span 
		            		style='cursor: pointer; color: blue' 
		            		data-bind="click: $root.openEditUser.bind(this, $parent)">edit</span>
		            </td>
		        </tr>
		    </tbody>
		</table>
	</script>

	<script type="text/html" id="edit-user-template">
		<form data-bind="submit: updateUser, with: userToEdit">
			<label for="username">Username</label>
			<input name="username" data-bind="value: username" /><br>
			<label for="email">Email</label>
			<input name="email" data-bind="value: email" /><br>
			<label for="firstName">First Name</label>
			<input name="firstName" data-bind="value: firstName" /><br>
			<label for="lastName">Last Name</label>
			<input name="lastName" data-bind="value: lastName" /><br>
			<input type="submit" name="submit" value="submit" class="button" />
		</form>
	</script>


	<script type="text/html" id="blank-template">[blank]</script>
	<script type="text/html" id="music-template">[music]</script>
</body>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js'></script>
<!-- <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.0/knockout-min.js'></script> -->
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.2.0/knockout-debug.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout.mapping/2.4.1/knockout.mapping.min.js'></script>
<!-- <script type='text/javascript' src='../script/view_modles/user.js'></script> -->
<script type='text/javascript'>
	$(function() {
		var adminViewModel = new AdminViewModel(
			<?php echo "$administrator, $blankAdministrator, $isAuthenticated" ?>)
		ko.applyBindings(adminViewModel, document.body);
	});

function AdminViewModel(administrator, blankAdministrator, isAuthenticated) {
		var self = this;
		this.blankAdministrator = blankAdministrator;
		this.isAuthenticated = ko.observable(isAuthenticated);
		this.administrator = ko.mapping.fromJS(administrator);
		this.userConnector = new UserConnector();
		this.currentPage = ko.observable('blank');
		this.users = ko.observableArray();
		this.userToEdit = ko.observable();

		this.pageToDisplay = function(model) {
			if (model.isAuthenticated()) {
				return model.currentPage() + '-template';
			} else {
				return 'login-template';
			}
		};

		this.login = function(formElement) {
			var input = {};
			$(formElement).serializeArray().map(function(x){input[x.name] = x.value;});
			this.userConnector.authenticate(input, 
				function (data, textStatus, jqXHR) {
	            	self.isAuthenticated(data.authenticated);
					ko.mapping.fromJS(data.user, self.administrator);
	            }, 
	            function(data, textStatus, errorThrown) {
	            	self.isAuthenticated(false);
					console.log('request failed! ' + textStatus);
			    });
		};

		this.logout = function() {
			this.userConnector.logout(
				function (data, textStatus, jqXHR) {
	            	self.isAuthenticated(false);
	            	ko.mapping.fromJS(self.blankAdministrator, self.administrator);
	            	self.currentPage('blank');
	            },
	            function(data, textStatus, errorThrown) {
	            	self.isAuthenticated(false);
					console.log('request failed! ' + textStatus);
			    });
		};

		this.openUsers = function() {
			var successCallback = function (data, textStatus, jqXHR) {
				self.users.removeAll();
				for (var i = 0; i < data.length; i++) {
					var u = ko.mapping.fromJS(data[i]);
					self.users.push(u);
				}
				self.currentPage('users');
            };
            var failedCallback = function(data, textStatus, errorThrown) {
				if (typeof data.authenticated  !== undefined && !data.authenticated) {
					self.isAuthenticated(false);
				}
				console.log('request failed! ' + textStatus);
		    };
			this.userConnector.getUsers(successCallback, failedCallback);
		};

		this.openEditUser = function(root, user, third) {
			root.userToEdit(user);
			root.currentPage('edit-user');
		};

		this.updateUser = function() {
			console.log("updateUser called");
		};

		this.openMusic = function() {
			self.currentPage('music');
		};
	};

	//
	//
	// Use this to conect to the server
	function UserConnector() {
	 	//todo: move this into a config file
	 	this.handlerBase = '/lib/handlers';
	 	this.handlerUrl = this.handlerBase + '/user/index.php';

		this.getUser = function(id, callback, errorCallback) {
			$.ajax({
	            url: this.handlerUrl + '?id=' + id,
	            type: 'get',
	            dataType: 'json',
	            contentType: 'application/json',
	            cache: false,
	            success: function (data, textStatus, jqXHR) {
	            	callback(data, textStatus, jqXHR);
	            },
	            error: function(data, textStatus, errorThrown) {
	            	errorCallback(data, textStatus, errorThrown);
			    }
	        });
		};

		this.getUsers = function(callback, errorCallback) {
			$.ajax({
	            url: this.handlerUrl,
	            type: 'get',
	            dataType: 'json',
	            contentType: 'application/json',
	            cache: false,
	            success: function (data, textStatus, jqXHR) {
	            	callback(data, textStatus, jqXHR);
	            },
	            error: function(data, textStatus, errorThrown) {
	            	errorCallback(data, textStatus, errorThrown);
			    }
	        });
		};

		this.createUser = function(user, callback) {
			var request = new XMLHttpRequest();
			request.open('POST', this.handlerUrl, true);
			request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			request.onload = function () {
		      	var json = JSON.parse(request.responseText);
		        callback(json.userId);
			}
			request.send(JSON.stringify(user));
		};

		this.authenticate = function(input, callback, errorCallback) {
			$.ajax({
	            url: this.handlerBase + '/authenticate.php',
	            type: 'post',
	            dataType: 'json',
	            contentType: 'application/json',
	            cache: false,
	            success: function (data, textStatus, jqXHR) {
	            	callback(data, textStatus, jqXHR);
	            },
	            error: function(data, textStatus, errorThrown) {
	            	errorCallback(data, textStatus, errorThrown);
			    },
	            data: JSON.stringify(input)
	        });
		};

		this.logout = function(callback, errorCallback) {
			$.ajax({
	            url: this.handlerBase + '/logout.php',
	            type: 'post',
	            dataType: 'json',
	            contentType: 'application/json',
	            cache: false,
	            success: function (data, textStatus, jqXHR) {
	            	callback(data, textStatus, jqXHR);
	            },
	            error: function(data, textStatus, errorThrown) {
	            	errorCallback(data, textStatus, errorThrown);
			    }
	        });
		};
	}

</script>
</html>



