<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class TestBase extends TestCase
{
	private $_httpClient = 0;
	public $cookieJar = 0;
    public $settings;

    protected function setUp(): void
    {
        $this->settings = ((new Configuration())->getSettings());
        $testSettomgs = $this->settings->test;
        // login as the test user (see sql/schema.sql)
        $json = $this->authenticateUser($testSettomgs->TEST_USERNAME, $testSettomgs->TEST_PASSWORD);

        // set the cookie for future requests
        $this->cookieJar = CookieJar::fromArray([
            'login' => $json->cookie
        ], $testSettomgs->TEST_DOMAIN);
    }

    function getHandlerClient() 
    {
    	if (!$this->_httpClient) {
    		$this->_httpClient = new Client([
	            'base_uri' => ((new Configuration())->getSettings())->host->HANDLER_WEB_ROOT,
	            'timeout'  => 4.0,
	            'cookies' => true,
	            'http_errors' => false
	        ]);
    	}
    	return $this->_httpClient;
    }

    function authenticateUser($username, $password) 
    {
		$client = $this->getHandlerClient();
        $response = $client->post('authenticate.php', [
        	'json' => [
                'username' => $username,
                'password' => $password
            ]
        ]);
        $this->assertEquals($response->getStatusCode(), 200);
        $json = json_decode($response->getBody()->getContents());
        return $json;
    }
}
