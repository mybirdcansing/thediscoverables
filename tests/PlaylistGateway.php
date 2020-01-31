<?php
declare(strict_types=1);
require_once __dir__ . '/GatewayBase.php';

class PlaylistGateway extends GatewayBase
{
    public $handlerPath = 'playlist/';
    
    public function getPlaylist($id)
    {
        $response = $this->httpClient->get($this->handlerPath . $id, [
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        return Playlist::fromJson($response->getBody()->getContents());
    }

    public function createPlaylist($playlist)
    {
        $response = $this->httpClient->post($this->handlerPath, [
            'json' => $playlist->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->playlistCreated, '`playlistCreated` should be true');
        $this->assertEquals(201, $response->getStatusCode());
        return $this->getPlaylist($json->playlistId);
    }

    public function createPlaylistWithErrors($playlist, $expectedErrors, $expectedStatusCode)
    {
        $response = $this->httpClient->post($this->handlerPath, [
            'json' => $playlist->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals($expectedStatusCode, $response->getStatusCode());
            $this->assertTrue(isset($json->playlistCreated));
            $this->assertFalse($json->playlistCreated, 'The created flag was not set to false.');
            $this->assertTrue(isset($json->errorMessages));
            $ems = (array)$json->errorMessages;
            foreach ($expectedErrors as $expectedErrorCode => $expectedErrorMessage) {
                $this->assertArrayHasKey($expectedErrorCode, $ems);
                $this->assertEquals($expectedErrorMessage, $ems[$expectedErrorCode]
                );
            }
        } catch (Exception $e) {
            // cleanup the database
            if ($json->playlistCreated) {
                $this->deletePlaylist($json->playlistId);
            }
        }
    }

    public function updatePlaylist($playlist)
    {
        $response = $this->httpClient->post($this->handlerPath . $playlist->id, [
            'json' => $playlist->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->playlistUpdated, '`playlistUpdated` should be true');
        $this->assertEquals(200, $response->getStatusCode());
        return $this->getPlaylist($json->playlistId);
        
    }

    public function addSongToPlaylist($playlist, $song)
    {
        $response = $this->httpClient->post($this->handlerPath . $playlist->id . '/addsong/' . $song->id, [
            'cookies' => $this->cookieJar
        ]);
        return Playlist::fromJson($response->getBody()->getContents());
    }

    public function removeSongFromPlaylist($playlist, $song)
    {   
        $response = $this->httpClient->post($this->handlerPath . $playlist->id . '/removesong/' . $song->id, [
            'cookies' => $this->cookieJar
        ]);
        return Playlist::fromJson($response->getBody()->getContents());

    }

    public function deletePlaylist($id)
    {
        $response = $this->httpClient->post($this->handlerPath . $id . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
