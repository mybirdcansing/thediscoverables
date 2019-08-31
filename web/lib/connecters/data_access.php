<?php
require_once __dir__ . '/../consts.php';

class DataAccess {

    private $dbConnection = null;

    public function __construct()
    {
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);	
		try {
			$this->dbConnection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
			$this->dbConnection->set_charset("utf8mb4");
		} catch(Exception $e) {
			error_log($e->getMessage());
			exit($e->getMessage());
		}
    }

    public function getConnection()
    {
        return $this->dbConnection;
    }
}