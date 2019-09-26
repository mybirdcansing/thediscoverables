<?php

// authorization handler
require_once __DIR__ . '/../lib/consts.php';
require_once __DIR__ . '/../lib/AuthCookie.php';
require_once __dir__ . '/../lib/connecters/DataAccess.php';
require_once __dir__ . '/../lib/connecters/UserData.php';

$blankAdministrator = "{id:'', username:'', firstName:'', lastName:'', email:'', statusId:0, isAuthenticated:false}";
if (AuthCookie::isValid()) {
	$dbConnection = (new DataAccess())->getConnection();
	$userData = new UserData($dbConnection);
	$administrator = json_encode($userData->getByUsername(AuthCookie::getUsername()));
} else {
	$administrator = $blankAdministrator;
}
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
		body {
		    margin:50px 0px; padding:0px;
		    text-align: center;
		    align:center;
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

/*		#adminPage, #loginForm {
			display: none;
		}*/
		

	</style>
</head>
<body>
	<p>
	thediscoverables.com html Administration
	</p>
	<div>
		<div id='adminPage' 
			data-bind="visible:isAuthenticated">
			<button name="submit" id="logout" class="button" data-bind='click:logout'>logout</button>
			<div id="userInfo">
				<div>Email is <span data-bind="text: administrator.email"></span></div>
				<div>
					The name is <span data-bind="text: administrator.firstName"></span>
					<span data-bind="text: administrator.lastName"></span>
				</div>
				<div>Username is <span data-bind="text: administrator.username"></span></div>
			</div>
<!-- 			<div id='songsAdmin' class="adminSection">
				Admin for Songs
			</div>
			<div id='playlistsAdmin' class="adminSection">
				Admin for Playlists
			</div>
			<div id='albumsAdmin' class="adminSection">
				Admin for Album
			</div> -->
		</div>
		<div id='loginForm'
			data-bind="hidden:isAuthenticated">
			<form  data-bind="submit: login">
				<label for="username">Username</label>
				<input id="username" name="username"><br>
				<label for="password">Password</label>
				<input id="password" type="password" name="password"><br>
				<input type="submit" name="submit" id="submit" value="submit" class="button" />
			</form>
		</div>
	</div>
</body>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.0/knockout-min.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout.mapping/2.4.1/knockout.mapping.min.js'></script>
<script type='text/javascript' src='../script/view_modles/user.js'></script>
<script>
	$(function() {
		var adminPageViewModel = {
			isAuthenticated : ko.observable(<?php echo AuthCookie::isValid() ? 'true' : 'false' ?>),
			administrator : ko.mapping.fromJS(<?php echo $administrator  ?>),
			login: function(formElement) {
				var self = this;
				var input = {};
				$(formElement).serializeArray().map(function(x){input[x.name] = x.value;});
		        $.ajax({
		            url: "/lib/handlers/authenticate.php",
		            type: 'post',
		            dataType: 'json',
		            contentType: 'application/json',
		            cache: false,
		            success: function (data, textStatus, jqXHR) {
		            	self.isAuthenticated(data.authenticated);
						ko.mapping.fromJS(data.user, self.administrator);
						// self.administrator.id(data.user.id)
						// 			 .username(data.user.username)
						// 			 .firstName(data.user.firstName)
						// 			 .lastName(data.user.lastName)
						// 			 .email(data.user.email)
						// 			 .statusId(data.user.statusId);
		            },
		            error: function(data, textStatus, errorThrown) {
		            	self.isAuthenticated(false);
						console.log('getJSON request failed! ' + textStatus);
				    },
		            data: JSON.stringify(input)
		        });
			},
			logout: function() {
				var self = this;
		        $.ajax({
		            url: "/lib/handlers/logout.php",
		            type: 'post',
		            dataType: 'json',
		            contentType: 'application/json',
		            cache: false,
		            success: function (data, textStatus, jqXHR) {
		            	self.isAuthenticated(false);
		            	ko.mapping.fromJS(<?php echo $blankAdministrator  ?>, self.administrator);
						// debugger;
						// self.administrator.id('')
						// 			 .username('')
						// 			 .firstName('')
						// 			 .lastName('')
						// 			 .email('')
						// 			 .statusId(0);
		            },
		            error: function(data, textStatus, errorThrown) {
		            	self.isAuthenticated(false);
						console.log('getJSON request failed! ' + textStatus);
				    }
		        });
			}
		};

		ko.applyBindings(adminPageViewModel, document.body);
	});

</script>
</html>



