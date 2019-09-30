<?php
require_once __dir__ . '/../connecters/DataAccess.php';
require_once __dir__ . '/../connecters/UserData.php';
require_once __dir__ . '/../AuthCookie.php';

header("Content-Type: application/json; charset=UTF-8");

$json = file_get_contents('php://input');
$objJson = json_decode($json);
$username = $objJson->username;
$password = $objJson->password;

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