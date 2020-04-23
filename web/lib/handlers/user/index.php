<?php
require_once __dir__ . '/UserController.php';

$dbConnection = (new DataAccess())->getConnection();
$controller = new UserController($dbConnection);
$controller->processRequest();