<?php
require_once __dir__ . '/../messages.php';
require_once __dir__ . '/../AuthCookie.php';

// authorization handler
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate");


AuthCookie::logout();
echo json_encode(
    array(
    	"authorized" => false, 
    	"message" => "You have successfully logged out."
    )
);
