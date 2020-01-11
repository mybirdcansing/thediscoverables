<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/Configuration.php';
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class PlaylistTest extends TestBase
{
    private $handlerPath = 'playlist/';

    public function testCreate()
    {
        $playlist = $this->fleshedOutPlaylist();
        $createdPlaylist = $this->create($playlist);
        try {
            $this->assertEquals($playlist->title, $createdPlaylist->title);
            $this->assertEquals($playlist->description, $createdPlaylist->description);
        } finally {
            $this->delete($createdPlaylist->id);
        }
    }

    public function testUpdate()
    {
        $modifiedPlaylist = $this->create($this->fleshedOutPlaylist());
        $modifiedPlaylist->title = GUID();
        $modifiedPlaylist->description = 'pizza is good';
        $modifiedPlaylist->filename = 'pizza.wav';
        $updatedPlaylist = $this->update($modifiedPlaylist);
        try {
            $this->assertEquals($modifiedPlaylist->title, $updatedPlaylist->title);
            $this->assertEquals($modifiedPlaylist->description, $updatedPlaylist->description);
        } finally {
            $this->delete($modifiedPlaylist->id);
        }
    }

    public function testCreateDupeTitle()
    {
        $playlist = $this->fleshedOutPlaylist();
        $playlist = $this->create($playlist);
        $expectedErrors = [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $playlist->title)];
        $playlist2 = $this->createWithErrors($playlist, $expectedErrors, 409);
        $this->delete($playlist->id);
    }

    public function testCreateWithLongTitle()
    {
        // create with long title
        $playlist = $this->fleshedOutPlaylist();
        $playlist->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $this->createWithErrors($playlist, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE], 422);
    }

    public function testCreateWithBlankTitle()
    {
        $playlist = $this->fleshedOutPlaylist();
        $playlist->title = '';
        $this->createWithErrors($playlist, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE], 422);
    }

    public function get($id)
    {
        $client = $this->getHandlerClient();
        $response = $client->get($this->handlerPath . $id, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return Playlist::fromJson($response->getBody()->getContents());
    }

    public function create($playlist)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->handlerPath, [
            'json' => $playlist->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->playlistCreated, '`playlistCreated` should be true');
        try {
            $this->assertEquals(201, $response->getStatusCode());
            return $this->get($json->playlistId);
        } catch (Exception $e) {
            $this->delete($json->playlistId);
            throw $e;
        }
    }

    public function createWithErrors($playlist, $expectedErrors, $expectedStatusCode)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->handlerPath, [
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
        } finally {
            // cleanup the database
            if ($json->playlistCreated) {
                $this->delete($json->playlistId);
            }
        }
    }

    public function update($playlist)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->handlerPath . $playlist->id, [
            'json' => $playlist->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->playlistUpdated, '`playlistUpdated` should be true');
        try {
            $this->assertEquals(200, $response->getStatusCode());
            return $this->get($json->playlistId);
        } catch (Exception $e) {
            $this->deletePlaylist($json->playlistId);
            throw $e;
        }
    }

    public function fleshedOutPlaylist()
    {
        $playlist = new Playlist();
        $playlist->title = GUID();
        $playlist->description = 'This is a good playlist!';
        return $playlist;
    }

    public function delete($id)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->handlerPath . $id . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

}