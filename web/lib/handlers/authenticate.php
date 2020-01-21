<?php
require_once __dir__ . '/../connecters/DataAccess.php';
require_once __dir__ . '/../connecters/UserData.php';
require_once __dir__ . '/../objects/AuthCookie.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$input = json_decode(file_get_contents('php://input'));
$username = $input->username;
$password = $input->password;

if (isset($username) && isset($password)) {
	$dbConnection = (new DataAccess())->getConnection();
	$userData = new UserData($dbConnection);

	$errorMessages = [];
    if (!isset($username) || $username == '') {
        $errorMessages[USERNAME_BLANK_CODE] = USERNAME_BLANK_MESSAGE;
    }
    if (!isset($password) || $password == '') {
        $errorMessages[PASSWORD_BLANK_CODE] = PASSWORD_BLANK_MESSAGE;
    }

    if (count($errorMessages) == 0) {
		$user = $userData->getAuthenticatedUser($username, $password);
		if ($user) {
			$cookie = AuthCookie::setCookie($username);
			echo json_encode(
		        array(
		        	"authenticated" => true,
		        	"user" => $user->expose(), 
		        	"cookie" => $cookie
		        )
		    );
		} else {
			$errorMessages[AUTH_FAILED_CODE] = AUTH_FAILED_MESSAGE;
		}
	}

	if (count($errorMessages) > 0)  {
		header('HTTP/1.0 401 Unauthorized');
		echo json_encode(
	        array("authenticated" => false, "errorMessages" => $errorMessages)
	    );
	}
}

?>