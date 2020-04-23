<?php
require_once __dir__ . '/SongController.php';

$dbConnection = (new DataAccess())->getConnection();
$controller = new SongController($dbConnection);
$controller->processRequest();
