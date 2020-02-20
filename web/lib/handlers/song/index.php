<?php
require_once __dir__ . '/../../connecters/DataAccess.php';
require_once __dir__ . '/../../connecters/SongData.php';
require_once __dir__ . '/../../connecters/UserData.php';
require_once __dir__ . '/../../objects/AuthCookie.php';
require_once __DIR__ . '/../../messages.php';
require_once __dir__ . '/SongController.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Origin");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$requestMethod = $_SERVER["REQUEST_METHOD"];
if(!$requestMethod == 'GET' && !AuthCookie::isValid()) {
    header("'HTTP/1.1 403 Forbidden'");
	echo json_encode(
        array("authorized" => false, "message" => "You do not have permission")
    );
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// endpoints start with /song, everything else results in a 404 Not Found
if ($uri[3] !== 'song') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// the user id is, of course, optional and must be a uuid:
$songId = null;
if (isset($uri[4]) && $uri[4] != '') {
    $songId = $uri[4];  
}

$requestAction = null;
if (isset($uri[5]) && $uri[5] != '') {
	$requestAction = $uri[5];
}
$headers = getallheaders();  
if (array_key_exists('X-HTTP-Method-Override', $headers)) {
    $requestMethod = strtoupper($headers['X-HTTP-Method-Override']);
    // error_log('X-HTTP-Method-Override:' . $message);
}
$action = null;
switch ($requestMethod) {
    case 'OPTIONS':
     exit;
     break;
    case 'GET':
         $action = GET_ACTION;
        break;
    case 'POST':
    	if ($requestAction == 'delete') {
	    	$action = DELETE_ACTION;
		} elseif ($songId) {
            $action = UPDATE_ACTION;
        } else {
            $action = CREATE_ACTION;
        }
        break;
    case 'PUT':
        $action = UPDATE_ACTION;
        // may not work on hosting services, so
        // use the hack in POST
        break;
    case 'DELETE':
        $action = DELETE_ACTION;
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
$controller = new SongController($dbConnection, $action, $songId, $administrator);
$controller->processRequest();
