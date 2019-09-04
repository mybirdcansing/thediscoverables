<?php

// authorization handler
require __DIR__ . '/../lib/consts.php';
require __DIR__ . '/../lib/AuthCookie.php';

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
	</style>
</head>
<body>
	<p>
	admin page
	</p>
<?php
	if(AuthCookie::isValid()) {
?>
	<form method="get" action="login.php">
	    <input type="submit" name="submit" id="logout" value="submit" class="button" />
	</form>
<?php
	} else {
?>
	<form method="post" action="login.php">
		<label for="username">Username</label>
		<input id="username" name="username"><br>

		<label for="password">Password</label>
		<input id="password" name="password"><br>

		<input type="submit" name="submit" id="submit" value="submit" class="button" />
	</form>
</body>
</html>
<?php
}
?>
