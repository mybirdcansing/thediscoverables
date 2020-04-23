<?php
require_once __dir__ . '/../connecters/DataAccess.php';
require_once __dir__ . '/../connecters/UserData.php';
require_once __dir__ . '/../objects/AuthCookie.php';
require_once __DIR__ . '/../messages.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Origin");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


class ControllerBase {

    public $handlerName;
    public $entityId;
    public $requestAction;
    public $requestMethod;
    public $dbConnection;

    public function __construct($handlerName, $allowGet, $dbConnection)
    {
        $this->handlerName = $handlerName;
        $this->dbConnection = $dbConnection;
        $this->requestMethod = $_SERVER["REQUEST_METHOD"];
        
        $headers = getallheaders();  

        if (array_key_exists('X-HTTP-Method-Override', $headers)) {
            $this->requestMethod = strtoupper($headers['X-HTTP-Method-Override']);
        }          

        if((!$allowGet || $this->requestMethod == 'GET') && !AuthCookie::isValid()) {
            header("'HTTP/1.1 403 Forbidden'");
            echo json_encode(
                array("authorized" => false, "message" => "You do not have permission")
            );
            exit();
        }   
        
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);
        
        // endpoints start with /user, everything else results in a 404 Not Found
        if ($uri[3] !== $this->handlerName) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        if (isset($uri[4]) && $uri[4] != '' && $uri[4] != 'create') {
            $this->entityId = $uri[4];
        }

        if (isset($uri[5]) && $uri[5] != '') {
            $this->requestAction = $uri[5];
        }
    }

    public function getAdministrator() {
        $userData = new UserData($this->dbConnection);
        return $userData->getByUsername(AuthCookie::getUsername());
    }

    public function getActionName()
    {
        $action = null;
        switch ($this->requestMethod) {
            case 'OPTIONS':
                exit;
                break;
            case 'GET':
                if ($this->requestAction == 'playlistsongs') {
                    $action = GET_PLAYLIST_SONGS_ACTION;
                } else {
                    $action = GET_ACTION;
                }
                break;
            case 'POST':
                if ($this->requestAction == 'delete') {
                    $action = DELETE_ACTION;
                } elseif ($this->requestAction == 'password') {
                    $action = UPDATE_PASSWORD_ACTION;
                } elseif ($this->requestAction == 'addsong') {
                    $action = ADD_TO_PLAYLIST_ACTION;
                } elseif ($this->requestAction == 'removesong') {
                    $action = REMOVE_FROM_PLAYLIST_ACTION;                    
                } elseif ($this->entityId) {
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
        return $action;
    }
}