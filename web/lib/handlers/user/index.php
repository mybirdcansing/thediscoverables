<?php
require_once __dir__ . '/../../connecters/DataAccess.php';
require_once __dir__ . '/../../connecters/UserData.php';
require_once __dir__ . '/UserController.php';
require_once __dir__ . '/../../AuthCookie.php';
require_once __DIR__ . '/../../messages.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if(!AuthCookie::isValid()) {
    header("'HTTP/1.1 403 Forbidden'");
	echo json_encode(
        array("authorized" => false, "message" => "You do not have permission to be here.")
    );
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// endpoints start with /user, everything else results in a 404 Not Found
if ($uri[3] !== 'user') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// the user id is, of course, optional and must be a uuid:
$userId = null;
if (isset($uri[4]) && $uri[4] != '') {
    $userId = $uri[4];  
 //    if (!preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $userId)) {
 //    	header("HTTP/1.1 404 Not Found");
 //    	exit();
	// }
}

$requestAction = null;
if (isset($uri[5]) && $uri[5] != '') {
	$requestAction = $uri[5];
} 

$action = null;
switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
         $action = GET_ACTION;
        break;
    case 'POST':
    	if ($requestAction == 'delete') {
	    	$action = DELETE_ACTION;
		} elseif ($requestAction == 'password') {
			$action = UPDATE_PASSWORD_ACTION;
		} elseif ($userId) {
            $action = UPDATE_ACTION;
        } else {
            $action = CREATE_ACTION;
        }
        break;
    case 'PUT':
        // may not work on hosting services, so
        // use the hack in POST
        break;
    case 'DELETE':
        // may not work on hosting services, so 
        // use the flag hack in POST
        break;
    default:
        break;
}

// pass the request method and user ID to the PersonController and process the HTTP request:
$dbConnection = (new DataAccess())->getConnection();
$userData = new UserData($dbConnection);
$administrator = $userData->getByUsername(AuthCookie::getUsername());
$controller = new UserController($dbConnection, $action, $userId, $administrator);
$controller->processRequest();
