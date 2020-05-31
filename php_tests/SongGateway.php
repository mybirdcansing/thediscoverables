<?php
declare(strict_types=1);
require_once __dir__ . '/GatewayBase.php';

class SongGateway extends GatewayBase
{
	public $handlerPath = 'song/';
    
    public function getSong($songId)
    {
        $response = $this->httpClient->get($this->handlerPath . $songId, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return Song::fromJson($response->getBody()->getContents());
    }

    public function createSong($song)
    {
        $response = $this->httpClient->post($this->handlerPath, [
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
        try {
	        $response = $this->httpClient->post($this->handlerPath, [
	            'json' => $song->expose(),
	            'cookies' => $this->cookieJar
	        ]);
	        $json = json_decode($response->getBody()->getContents());
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
        $response = $this->httpClient->post($this->handlerPath . $song->id, [
            'json' => $song->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->songUpdated, '`songUpdated` should be true');
        $this->assertEquals(200, $response->getStatusCode());
        return $this->getSong($json->songId);
    }

    public function deleteSong($songId)
    {
        $response = $this->httpClient->post($this->handlerPath . $songId . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

}
