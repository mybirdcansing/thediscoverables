<?php
require_once __dir__ . '/../consts.php';
require_once __dir__ . '/../objects/user.php';
require_once __dir__ . '/../connecters/user_data.php';

// authentication handler
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


if (isset($_REQUEST['logout'])) {
	unset($_COOKIE[LOGIN_COOKIE_NAME]);
	// empty value and expiration one hour before
	setcookie(LOGIN_COOKIE_NAME, '', time() - 3600, '/');
    echo json_encode(
        array("message" => "You have successfully logged out.")
    );
    exit();
}

$username = $_REQUEST['username'];
$password = $_REQUEST['password'];

if (isset($username) && isset($password)) {
	$userData = new UserData();
	$user = $userData->getAuthenticatedUser($username, $password);
	if ($user) {
		setcookie(LOGIN_COOKIE_NAME, $username . ',' . md5($username . SECRET_WORD), time()+3600*24*30, '/');
		echo json_encode(
	        array("message" => "Authenticated", "user" => $user->expose())
	    );
	} else {
		echo json_encode(
	        array("message" => "Invalid Credentials")
	    );
	}
}

?>