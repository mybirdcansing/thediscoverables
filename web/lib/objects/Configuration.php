<?php
declare(strict_types=1);

class Configuration
{
	private $_settings;
    function __construct()
    {
		$json = '{
			"dataAccess" : {
				"DB_HOST": "mysql-localhost",
				"DB_USER": "root",
				"DB_PASSWORD": "abacadae",
				"DB_DATABASE": "thediscoverables"
			},
			"security" : {
				"SECRET_WORD": "pizza is yummy",
				"LOGIN_COOKIE_NAME": "login"
			},
			"test" : {
				"TEST_USERNAME": "adam",
				"TEST_PASSWORD": "abacadae",
				"TEST_DOMAIN": "localhost",
				"TEST_EMAIL" : "ajcohen100@yahoo.com"
			},
			"email": {
				"USER": "jeremymasoncohen@gmail.com",
				"PASSWORD": "jndsvrbkaolwtmrf",
				"FROM_ADDRESS": "thediscoverables@gmail.com",
				"FROM_NAME": "The Discoverables Support"
			},
			"host" : {
				"HANDLER_WEB_ROOT": "http://localhost/lib/handlers/",
				"DOMAIN" : "localhost"
			},
			"artwork": {
				"sizes": {
					"thumbnail": {
						"width": 100,
						"height": 100
					},
					"small": {
						"width": 240,
						"height": 240
					},
					"medium": {
						"width": 500,
						"height": 500
					},
					"large": {
						"width": 1024,
						"height": 1024
					},
					"x-large": {
						"width": 1200,
						"height": 1200
					},
					"xx-large": {
						"width": 1600,
						"height": 1600
					}			
				}
			}
		}';
		// $json = file_get_contents(__dir__ . '/../config.json');
    	$json = preg_replace('/\s+/S', "", $json);
		$this->_settings = json_decode($json);
    }

	function getSettings()
	{
		return $this->_settings;
	}
}
