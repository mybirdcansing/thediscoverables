<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class TestBase extends TestCase
{
	private $_httpClient = 0;
    public function getHandlerClient() {
    	if (!$this->_httpClient) {
    		$this->_httpClient = new Client([
	            // Base URI is used with relative requests
	            'base_uri' => HANDLER_WEB_ROOT,
	            // You can set any number of default request options.
	            'timeout'  => 2.0,
	        ]);
    	}
    	return $this->_httpClient;
        
    }
}
