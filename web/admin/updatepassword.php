<!DOCTYPE html>
<html>
<head>
	<title>The Discoverables Administration</title>
	<link rel="stylesheet" href="styles.css?v=1.1">
</head>
<body>
	<p>
	The Discoverables Administration
	</p>
	<div id='page' data-bind="template: { name: templateName, data: $data}"></div>

	<script type="text/html" id="enter-new-password-template">
		<form data-bind="submit: $parent.updatePassword" class="password-reset">
			<p class='formTitle'>Reset your password:</p>
			<ul data-bind="foreach: validationErrors" class="validationErrors">
				<li data-bind='text:$data'></li>
			</ul>
			<label for="password">New Password</label>
			<input type="password" name="password"><br>
			<label for="confirm">Confirm</label>
			<input type="password" name="confirm"><br>
			<div style="padding-left: 150px"><input type="submit" type="password" name="submit" value="submit" class="button" /></div>
		</form>
	</script>
	<script type="text/html" id="success-template">
		<p>Your password has been updated. <a href="/admin/">Enter the admin site.</a></p>
	</script>
</body>
<!-- <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script> -->
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js'></script>
<!-- <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.0/knockout-min.js'></script> -->
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout/3.2.0/knockout-debug.js'></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/knockout.mapping/2.4.1/knockout.mapping.min.js'></script>
<script type='text/javascript' src='scripts/ConnectorBase.js'></script>
<script type='text/javascript' src='scripts/UserConnector.js'></script>
<script type='text/javascript'>
	$(function() {
		ko.applyBindings(new PasswordResetViewModel(), document.body);
	});
	function PasswordResetViewModel() {
		var self = this;
		this.currentPage = ko.observable('enter-new-password');
		this.validationErrors = ko.observableArray([]);
		this.userConnector = new UserConnector();

		this.templateName = function(model) {
			return model.currentPage() + '-template';
		};

		this.updatePassword = function(formElement) {
			var input = {};
			$(formElement).serializeArray().map(function(x){input[x.name] = x.value;});

			if (input.password !== input.confirm) {
				self.validationErrors(["The passwords don't match"]);
				return;
			}
			var data = {
				'password' : input.password,
				'token' : this._getUrlParameter('token')
			};

			this.userConnector.resetPassword(data, 
				function (data, textStatus, jqXHR) {
					self.validationErrors([]);
	            	self.currentPage('success');
	            }, 
	            function(data, textStatus, errorThrown) {
	            	self.validationErrors(Object.values(data.errorMessages).reverse());
			    });
		};

		this._getUrlParameter = function(name) {
		    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
		    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
		    var results = regex.exec(location.search);
		    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
		}
	};
</script>
</html>