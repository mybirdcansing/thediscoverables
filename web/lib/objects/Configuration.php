<?php
declare(strict_types=1);

class Configuration
{
	private $_settings;
    function __construct($configPath = __dir__ . '/../../../config/config.json')
    {
		$json = file_get_contents($configPath);
    	$json = preg_replace('/\s+/S', "", $json);
		$this->_settings = json_decode($json);
    }

	function getSettings()
	{
		return $this->_settings;
	}
}
