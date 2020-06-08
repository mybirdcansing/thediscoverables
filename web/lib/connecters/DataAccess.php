<?php
require_once __dir__ . '/../messages.php';
require_once __dir__ . '/../objects/Configuration.php';

class DataAccess {

    private $dbConnection = null;
    private $configPath = null;

    public function __construct($configPath = null)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->configPath = $configPath;
        $this->dbConnection = $this->getConnection();
    }

    public function getConnection()
    {
        if (!$this->dbConnection) {
            try {
                if ($this->configPath == null) {
                    $config = new Configuration();
                } else {
                    $config = new Configuration($this->configPath);
                }
                $settings = $config->getSettings();
                $this->dbConnection = new mysqli(
                    $settings->dataAccess->DB_HOST, 
                    $settings->dataAccess->DB_USER, 
                    $settings->dataAccess->DB_PASSWORD, 
                    $settings->dataAccess->DB_DATABASE);
                $this->dbConnection->set_charset("utf8mb4");
            } catch(Exception $e) {
                error_log($e->getMessage());
                exit($e->getMessage());
            }
        }
        return $this->dbConnection;
    }

    public function closeConnection()
    {
        //try to close the MySql connection
        $closeResults = $this->dbConnection->close();

        //make sure it closed
        if($closeResults === false)
        {
            echo "Could not close MySQL connection.";
        }
    }
}