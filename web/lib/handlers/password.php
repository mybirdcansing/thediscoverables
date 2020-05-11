<?php
require_once __dir__ . '/../connecters/DataAccess.php';
require_once __dir__ . '/../connecters/UserData.php';
require_once __dir__ . '/../objects/AuthCookie.php';
require_once __DIR__ . '/../messages.php';
require_once __dir__ . '/user/UserController.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Origin");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$requestMethod = $_SERVER["REQUEST_METHOD"];
if ($requestMethod === 'OPTIONS') {
    exit;
}
$controller = new UserController((new DataAccess())->getConnection(), UPDATE_PASSWORD_ACTION);
$controller->processRequest();