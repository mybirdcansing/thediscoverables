<?php
// authorization handler
require_once __dir__ . '/../objects/AuthCookie.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET");
header("Access-Control-Max-Age: 0");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(AuthCookie::isValid()) {
    header('HTTP/1.1 200 OK');
    echo json_encode([
            "authorized" => true,
            "message" => "You have permission to be here",
            "username" => AuthCookie::getUsername()
    ]);
} else {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode([
        "authorized" => false,
        "message" => "You do not have permission"
    ]);
}
