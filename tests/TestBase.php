<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class TestBase extends TestCase
{
	public $songHandlerPath = 'song/';
	private $httpClient = 0;
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
    	if (!$this->httpClient) {
    		$this->httpClient = new Client([
	            'base_uri' => ((new Configuration())->getSettings())->host->HANDLER_WEB_ROOT,
	            'timeout'  => 4.0,
	            'cookies' => true,
	            'http_errors' => false
	        ]);
    	}
    	return $this->httpClient;
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


    public function getSong($songId)
    {
        $client = $this->getHandlerClient();
        $response = $client->get($this->songHandlerPath . $songId, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return Song::fromJson($response->getBody()->getContents());
    }

    public function createSong($song)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->songHandlerPath, [
            'json' => $song->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->songCreated, '`songCreated` should be true');
        try {
            $this->assertEquals(201, $response->getStatusCode());
            return $this->getSong($json->songId);
        } catch (Exception $e) {
            $this->deleteSong($json->songId);
            throw $e;
        }
    }

    public function createSongWithErrors($song, $expectedErrors, $expectedStatusCode)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->songHandlerPath, [
            'json' => $song->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals($expectedStatusCode, $response->getStatusCode());
            $this->assertTrue(isset($json->songCreated));
            $this->assertFalse($json->songCreated, 'The created flag was not set to false.');
            $this->assertTrue(isset($json->errorMessages));
            $ems = (array)$json->errorMessages;
            foreach ($expectedErrors as $expectedErrorCode => $expectedErrorMessage) {
                $this->assertArrayHasKey($expectedErrorCode, $ems);
                $this->assertEquals($expectedErrorMessage, $ems[$expectedErrorCode]
                );
            }
        } finally {
            // cleanup the database
            if ($json->songCreated) {
                $this->deletesong($json->songId);
            }
        }
    }

    public function updateSong($song)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->songHandlerPath . $song->id, [
            'json' => $song->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->songUpdated, '`songUpdated` should be true');
        try {
            $this->assertEquals(200, $response->getStatusCode());
            return $this->getSong($json->songId);
        } catch (Exception $e) {
            $this->deleteSong($json->songId);
            throw $e;
        }
    }

    public function fleshedOutSong()
    {
        $song = new Song();
        $song->title = GUID();
        $song->filename = 'running_in_place.wav';
        $song->description = 'This is a good song!';
        return $song;
    }

    public function deleteSong($songId)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->songHandlerPath . $songId . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
