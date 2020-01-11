<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/Configuration.php';
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class SongTest extends TestBase
{
    private $handlerPath = 'song/';

    public function testCreateSong()
    {
        $song = $this->fleshedOutSong();
        $createdSong = $this->create($song);
        try {
            $this->assertEquals($song->title, $createdSong->title);
            $this->assertEquals($song->filename, $createdSong->filename);
            $this->assertEquals($song->description, $createdSong->description);
        } finally {
            $this->deleteSong($createdSong->id);
        }
    }

    public function testUpdateSong()
    {
        $modifiedSong = $this->create($this->fleshedOutSong());
        $modifiedSong->title = GUID();
        $modifiedSong->description = 'pizza is good';
        $modifiedSong->filename = 'pizza.wav';
        $updatedSong = $this->updateSong($modifiedSong);
        try {
            $this->assertEquals($modifiedSong->title, $updatedSong->title);
            $this->assertEquals($modifiedSong->filename, $updatedSong->filename);
            $this->assertEquals($modifiedSong->description, $updatedSong->description);
        } finally {
            $this->deleteSong($modifiedSong->id);
        }
    }

    public function testCreateDupeTitle()
    {
        $song = $this->fleshedOutSong();
        $song = $this->create($song);
        $expectedErrors = [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $song->title)];
        $song2 = $this->createWithErrors($song, $expectedErrors, 409);
        $this->deleteSong($song->id);
    }

    public function testCreateWithLongTitle()
    {
        // create with long title
        $song = $this->fleshedOutSong();
        $song->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $this->createWithErrors($song, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE], 422);
    }

    public function testCreateWithBlankTitle()
    {
        $song = $this->fleshedOutSong();
        $song->title = '';
        $this->createWithErrors($song, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE], 422);
    }

    public function getSong($songId)
    {
        $client = $this->getHandlerClient();
        $response = $client->get($this->handlerPath . $songId, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return Song::fromJson($response->getBody()->getContents());
    }

    public function create($song)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->handlerPath, [
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

    public function createWithErrors($song, $expectedErrors, $expectedStatusCode)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->handlerPath, [
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
        $response = $client->post($this->handlerPath . $song->id, [
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
        $response = $client->post($this->handlerPath . $songId . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

}