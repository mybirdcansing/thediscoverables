<?php
require_once __dir__ . '/../connecters/DataAccess.php';
require_once __dir__ . '/../connecters/UserData.php';
require_once __dir__ . '/../AuthCookie.php';


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// $username = $_REQUEST['username'];
// $password = $_REQUEST['password'];

$json = file_get_contents('php://input');
$objJson = json_decode($json);
$username = $objJson->username;
$password = $objJson->password;
if (isset($username) && isset($password)) {
	$dbConnection = (new DataAccess())->getConnection();
	$userData = new UserData($dbConnection);
	$user = $userData->getAuthenticatedUser($username, $password);
	if ($user) {
		$cookie = AuthCookie::setCookie($username);
		echo json_encode(
	        array(
	        	"authenticated" => true, 
	        	"message" => "You have been authenticated", 
	        	"user" => $user->expose(), 
	        	"cookie" => $cookie
	        )
	    );
	} else {
		// header('HTTP/1.0 401 Unauthorized');
		echo json_encode(
	        array("authenticated" => false, "message" => "Invalid Credentials")
	    );
	}
}

?>