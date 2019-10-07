<?php
require_once __dir__ . '/../messages.php';
require_once __dir__ . '/../AuthCookie.php';

// authorization handler
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate");

if(AuthCookie::isValid()) {
    echo json_encode(
        array("authorized" => true, "message" => "You have permission to be here.", "username" => AuthCookie::getUsername())
    );
} else {
	header("HTTP/1.1 401 Unauthorized");
	echo json_encode(
        array("authorized" => false, "message" => "You do not have permission to be here.")
    );
}