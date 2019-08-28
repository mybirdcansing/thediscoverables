<?php
require_once __dir__ . '/../consts.php';
require_once __dir__ . '/../authorize_cookie.php';

// authorization handler
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
$cookieAuth = new AuthorizeCookie($_COOKIE[LOGIN_COOKIE_NAME]);
if($cookieAuth->isValid()) {
    echo json_encode(
        array("message" => "You have permission to be here.", "username" => $cookieAuth->getUsername())
    );
} else {
	echo json_encode(
        array("message" => "You do not have permission to be here.")
    );
}