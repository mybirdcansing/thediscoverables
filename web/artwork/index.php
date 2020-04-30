<?php
require_once __DIR__ . '/../lib/messages.php';
require_once __DIR__ . '/../lib/objects/Configuration.php';
include __DIR__ . '/../vendor/gumlet/php-image-resize/lib/ImageResize.php';
use \Gumlet\ImageResize;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET");
header("Access-Control-Allow-Headers: Origin");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$requestMethod = $_SERVER["REQUEST_METHOD"];
if ($requestMethod === 'OPTIONS') {
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$filename = $uri[2];
$options = explode('~', $filename);
$size = $options[0];
$originalFilename = $options[1];
$originalArtworkPath = "../original_artwork/$originalFilename";
if (!file_exists($originalArtworkPath)) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$sizes = ((new Configuration())->getSettings())->artwork->sizes;
$dimensions = $sizes->$size;

if (!isset($dimensions)) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$image = new ImageResize($originalArtworkPath);
$image->resizeToBestFit($dimensions->width, $dimensions->height);
$image->output();
$image->save("$size~$originalFilename");