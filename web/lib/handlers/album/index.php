<?php
require_once __dir__ . '/AlbumController.php';

$dbConnection = (new DataAccess())->getConnection();
$controller = new AlbumController($dbConnection);
$controller->processRequest();
