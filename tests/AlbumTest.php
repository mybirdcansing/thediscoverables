<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class AlbumTest extends TestBase
{
    public $albumHandlerPath = 'album/';

    public function testCreateAlbum()
    {
        $album = $this->fleshedOutAlbum();
        $createdAlbum = $this->createAlbum($album);
        try {
            $this->assertEquals($album->title, $createdAlbum->title);
            $this->assertEquals($album->playlist->id, $createdAlbum->playlist->id);
            $this->assertEquals($album->description, $createdAlbum->description);
        } finally {
            $this->deleteAlbum($createdAlbum->id);
        }
    }

    public function testUpdateAlbum()
    {
        $modifiedAlbum = $this->createAlbum($this->fleshedOutAlbum());
        $modifiedAlbum->title = GUID();
        $modifiedAlbum->description = 'pizza is good';
        $updatedAlbum = $this->updateAlbum($modifiedAlbum);
        try {
            $this->assertEquals($modifiedAlbum->title, $updatedAlbum->title);
            $this->assertEquals($modifiedAlbum->description, $updatedAlbum->description);
        } finally {
            $this->deleteAlbum($modifiedAlbum->id);
        }
    }

    public function testCreateAlbumWithDupeTitle()
    {
        $album = $this->fleshedOutAlbum();
        $album = $this->createAlbum($album);
        $expectedErrors = [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $album->title)];
        $album2 = $this->createAlbumWithErrors($album, $expectedErrors, 409);
        $this->deleteAlbum($album->id);
    }

    public function testCreateAlbumWithLongTitle()
    {
        // create with long title
        $album = $this->fleshedOutAlbum();
        $album->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $this->createAlbumWithErrors($album, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE], 422);
    }

    public function testCreateAlbumWithBlankTitle()
    {
        $album = $this->fleshedOutAlbum();
        $album->title = '';
        $this->createAlbumWithErrors($album, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE], 422);
    }

    public function getAlbum($albumId)
    {
        $client = $this->getHandlerClient();
        $response = $client->get($this->albumHandlerPath . $albumId, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return Album::fromJson($response->getBody()->getContents());
    }

    public function createAlbum($album)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->albumHandlerPath, [
            'json' => $album->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->albumCreated, '`albumCreated` should be true');
        try {
            $this->assertEquals(201, $response->getStatusCode());
            return $this->getAlbum($json->albumId);
        } catch (Exception $e) {
            $this->deleteAlbum($json->albumId);
            throw $e;
        }
    }

    public function createAlbumWithErrors($album, $expectedErrors, $expectedStatusCode)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->albumHandlerPath, [
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
        $client = $this->getHandlerClient();
        $response = $client->post($this->albumHandlerPath . $album->id, [
            'json' => $album->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->albumUpdated, '`albumUpdated` should be true');
        try {
            $this->assertEquals(200, $response->getStatusCode());
            return $this->getAlbum($json->albumId);
        } catch (Exception $e) {
            $this->deleteAlbum($json->albumId);
            throw $e;
        }
    }

    public function fleshedOutAlbum()
    {
        $album = new Album();
        $album->title = GUID();
        $album->description = 'This is a good album!';
        $album->playlist = $this->createPlaylist($this->fleshedOutPlaylist());
        return $album;
    }

    public function deleteAlbum($albumId)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->albumHandlerPath . $albumId . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

}