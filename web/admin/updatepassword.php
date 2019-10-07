<?php

// authorization handler
require_once __DIR__ . '/../lib/messages.php';
require_once __DIR__ . '/../lib/AuthCookie.php';
require_once __dir__ . '/../lib/connecters/DataAccess.php';
require_once __dir__ . '/../lib/connecters/UserData.php';

?>
<!DOCTYPE html>
<html>
<head>
	<title>The Discoverables Administration</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<p>
	thediscoverables.com html Administration
	</p>
	<div id='page' data-bind="template: { name: pageToDisplay, data: $data}"></div>

	<script type="text/html" id="enter-new-password-template">
		<form data-bind="submit: $parent.updatePassword" class="password-reset">
			<p class='formTitle'>Reset your password:</p>
			<ul data-bind="foreach: validationErrors" class="validationErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label for="password">New Password</label>
			<input id="password" type="password" name="password"><br>
			<label for="confirm">Confirm</label>
			<input id="confirm" type="password" name="confirm"><br>
			<div style="padding-left: 150px"><input type="submit" type="password" name="submit" value="submit" class="button" /></div>
		</form>
	</script>

</body>
<!-- <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script> -->
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js'></script>
<!-- <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.0/knockout-min.js'></script> -->
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.2.0/knockout-debug.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout.mapping/2.4.1/knockout.mapping.min.js'></script>
<script type='text/javascript' src='scripts/UserConnector.js'></script>
<script type='text/javascript'>
	$(function() {
		var passwordResetViewModel = new PasswordResetViewModel()
		ko.applyBindings(passwordResetViewModel, document.body);
	});
	function PasswordResetViewModel() {
		var self = this;
		this.currentPage = ko.observable('enter-new-password');
		this.validationErrors = ko.observableArray([]);
		this.userConnector = new UserConnector();

		this.pageToDisplay = function(model) {
			return model.currentPage() + '-template';
		};

		this.updatePassword = function(formElement) {
			debugger;
			var input = {};
			$(formElement).serializeArray().map(function(x){input[x.name] = x.value;});
			// var errors = [], i = 0;
			// if (input['password'] === '') {
			// 	errors[i++] = 
			// }
			input['token'] = getUrlParameter('token');
			input['updatePassword'] = 1;
			
			this.userConnector.resetPassword(input, 
				function (data, textStatus, jqXHR) {
					debugger;
					self.validationErrors([]);
	            	// show thank you message
	            }, 
	            function(data, textStatus, errorThrown) {
	            	debugger;
	            	self.validationErrors(Object.values(data.errorMessages).reverse());
			    });
		};
	};
	function getUrlParameter(name) {
	    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
	    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
	    var results = regex.exec(location.search);
	    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	};
</script>
</html>