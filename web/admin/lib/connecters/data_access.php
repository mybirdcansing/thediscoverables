<?php
require_once __dir__ . '/../consts.php';

class DataAccess
{ 
	
	public static function getConnection() {
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);	
		try {
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
			$conn->set_charset("utf8mb4");
		} catch(Exception $e) {
			error_log($e->getMessage());
			exit('Error connecting to database');
		}
		return $conn;
	}
}