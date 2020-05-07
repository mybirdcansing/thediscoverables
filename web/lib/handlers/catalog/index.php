<?php
require_once __dir__ . '/../../connecters/DataAccess.php';
require_once __dir__ . '/../../connecters/PlaylistData.php';
require_once __DIR__ . '/../../connecters/SongData.php';
require_once __dir__ . '/../../connecters/AlbumData.php';
require_once __DIR__ . '/../../messages.php';
require_once __dir__ . '/CatalogController.php';
require_once __dir__ . '/../../objects/Configuration.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Origin");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

switch ($_SERVER["REQUEST_METHOD"]) {
    case 'OPTIONS':
        exit;
        break;
    case 'GET':
        $action = GET_ACTION;
        break;
    default:
        break;
}

// pass the action name and user ID to the PersonController and process the HTTP request:
$dbConnection = (new DataAccess())->getConnection();
$controller = new CatalogController($dbConnection, $action);
$controller->processRequest();