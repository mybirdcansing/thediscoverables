<?php
declare(strict_types=1);

class Configuration
{
	private $_settings;
    function __construct()
    {
		$json = file_get_contents(__dir__ . '/../../../config/config.json');
    	$json = preg_replace('/\s+/S', "", $json);
		$this->_settings = json_decode($json);
    }

	function getSettings()
	{
		return $this->_settings;
	}
}
