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

    public function createAlbum($album, $expectedStatusCode = 201)
    {
        $response = $this->httpClient->post($this->handlerPath, [
            'json' => $album->expose(),
            'cookies' => $this->cookieJar
        ]);
        try {
            $this->assertEquals($expectedStatusCode, $response->getStatusCode());
            $json = json_decode($response->getBody()->getContents());
            $this->assertEquals($json->albumCreated, $expectedStatusCode == 201);
            if ($json->albumCreated) {
                $album->id = $json->albumId;
            }
            return $json;
        } catch (Exception $e) {
            if (isset($album->id)) {
                $this->deleteAlbum($album);
            }
            throw $e;
        }
    }

    public function updateAlbum($album, $expectedStatusCode = 200)
    {
        $response = $this->httpClient->post($this->handlerPath . $album->id, [
            'json' => $album->expose(),
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals($response->getStatusCode(), $expectedStatusCode);
        $json = json_decode($response->getBody()->getContents());
        $this->assertEquals($json->albumUpdated, $expectedStatusCode == 200);
        return $json;
    }

    public function deleteAlbum($album)
    {
        $response = $this->httpClient->post($this->handlerPath . $album->id . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
