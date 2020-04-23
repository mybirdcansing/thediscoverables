<?php
require_once __dir__ . '/PlaylistController.php';

$dbConnection = (new DataAccess())->getConnection();
$controller = new PlaylistController($dbConnection);
$controller->processRequest();
