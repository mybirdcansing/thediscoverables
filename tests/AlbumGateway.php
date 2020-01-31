<?php
declare(strict_types=1);
require_once __dir__ . '/GatewayBase.php';

class AlbumGateway extends GatewayBase
{
    public $handlerPath = 'album/';
   
    public function getAlbum($albumId)
    {
        $response = $this->httpClient->get($this->handlerPath . $albumId, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return Album::fromJson($response->getBody()->getContents());
    }

    public function createAlbum($album)
    {
        $response = $this->httpClient->post($this->handlerPath, [
            'json' => $album->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->albumCreated, '`albumCreated` should be true');
        $album->id = $json->albumId;
        try {
            $this->assertEquals(201, $response->getStatusCode());
            $album = $this->getAlbum($json->albumId);
            return $album;
        } catch (Exception $e) {
            $this->deleteAlbum($album);
            throw $e;
        }
    }

    public function createAlbumWithErrors($album, $expectedErrors, $expectedStatusCode)
    {
        $response = $this->httpClient->post($this->handlerPath, [
            'json' => $album->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals($expectedStatusCode, $response->getStatusCode());
            $this->assertTrue(isset($json->albumCreated));
            $this->assertFalse($json->albumCreated, 'The created flag was not set to false.');
            $this->assertTrue(isset($json->errorMessages));
            $ems = (array)$json->errorMessages;
            foreach ($expectedErrors as $expectedErrorCode => $expectedErrorMessage) {
                $this->assertArrayHasKey($expectedErrorCode, $ems);
                $this->assertEquals($expectedErrorMessage, $ems[$expectedErrorCode]
                );
            }
        } finally {
            // cleanup the database
            if ($json->albumCreated) {
                $this->deletealbum($json->albumId);
            }
        }
    }

    public function updateAlbum($album)
    {
        $response = $this->httpClient->post($this->handlerPath . $album->id, [
            'json' => $album->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->albumUpdated, '`albumUpdated` should be true');
        $this->assertEquals(200, $response->getStatusCode());
        $album = $this->getAlbum($json->albumId);
        return $album;
    }

    public function deleteAlbum($album)
    {
        $response = $this->httpClient->post($this->handlerPath . $album->id . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
