<?php
// require_once __dir__ . '/../consts.php';
require_once __dir__ . '/../objects/user.php';
require_once __dir__ . '/../connecters/data_access.php';
require_once __dir__ . '/../connecters/user_data.php';
require_once __dir__ . '/../auth_cookie.php';

// authentication handler
header("Access-Control-Allow-Origin: *");
//header("Content-Type: application/json; charset=UTF-8");

$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
// echo password_hash("rasmuslerdorf", PASSWORD_DEFAULT);
if (isset($username) && isset($password)) {
	$dbConnection = (new DataAccess())->getConnection();
	$userData = new UserData($dbConnection);
	$user = $userData->getAuthenticatedUser($username, $password);


	if ($user) {
		AuthCookie::setCookie($username);
		echo json_encode(
	        array("authenticated" => true, "message" => "You have been authenticated", "user" => $user->expose())
	    );
	} else {
		// header('HTTP/1.0 401 Unauthorized');
		echo json_encode(
	        array("authenticated" => false, "message" => "Invalid Credentials")
	    );
	}
}

?>