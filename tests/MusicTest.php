<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/Configuration.php';
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class MusicTest extends TestBase
{
    private $songHandlerPath = 'song/';
    private $settings;

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

    public function testCreateSong()
    {
        $song = new Song();
        $song->title = 'Running in Place';
        $song->filename = 'running_in_place.wav';
        $song->description = 'This is a good song!';
        $song = $this->_createSong($song);
        
        try {
            $this->assertEquals('Running in Place', $song->title);
            $this->assertEquals('running_in_place.wav', $song->filename);
            $this->assertEquals('This is a good song!', $song->description);
        } finally {
            $this->_deleteSong($song->id);
        }

    }

    private function _getSong($songId)
    {
        $client = $this->getHandlerClient();
        $response = $client->get($this->songHandlerPath . $songId, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return Song::fromJson($response->getBody()->getContents());
    }

    private function _createSong($song)
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
            

            return $this->_getSong($json->songId);
        } catch (Exception $e) {
            $this->_deleteSong($json->songId);
            throw $e;
        }
    }

    private function _deleteSong($songId)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->songHandlerPath . $songId . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

}