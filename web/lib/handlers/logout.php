<?php
require_once __dir__ . '/../consts.php';
require_once __dir__ . '/../auth_cookie.php';

// authorization handler
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate");


AuthCookie::logout();
echo json_encode(
    array("message" => "You have successfully logged out.")
);
