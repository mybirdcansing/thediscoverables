<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/Configuration.php';
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class PlaylistTest extends TestBase
{
    public $playlistHandlerPath = 'playlist/';

    public function testCreate()
    {
        $playlist = $this->fleshedOutPlaylist();
        $createdPlaylist = $this->createPlaylist($playlist);
        try {
            $this->assertEquals($playlist->title, $createdPlaylist->title);
            $this->assertEquals($playlist->description, $createdPlaylist->description);
        } finally {
            $this->deletePlaylist($createdPlaylist->id);
        }
    }

    public function testUpdate()
    {
        $modifiedPlaylist = $this->createPlaylist($this->fleshedOutPlaylist());
        $modifiedPlaylist->title = GUID();
        $modifiedPlaylist->description = 'pizza is good';
        $modifiedPlaylist->filename = 'pizza.wav';
        $updatedPlaylist = $this->updatePlaylist($modifiedPlaylist);
        try {
            $this->assertEquals($modifiedPlaylist->title, $updatedPlaylist->title);
            $this->assertEquals($modifiedPlaylist->description, $updatedPlaylist->description);
        } finally {
            $this->deletePlaylist($modifiedPlaylist->id);
        }
    }

    public function testCreateDupeTitle()
    {
        $playlist = $this->fleshedOutPlaylist();
        $playlist = $this->createPlaylist($playlist);
        $expectedErrors = [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $playlist->title)];
        $playlist2 = $this->createPlaylistWithErrors($playlist, $expectedErrors, 409);
        $this->deletePlaylist($playlist->id);
    }

    public function testCreateWithLongTitle()
    {
        // create with long title
        $playlist = $this->fleshedOutPlaylist();
        $playlist->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $this->createPlaylistWithErrors($playlist, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE], 422);
    }

    public function testCreateWithBlankTitle()
    {
        $playlist = $this->fleshedOutPlaylist();
        $playlist->title = '';
        $this->createPlaylistWithErrors($playlist, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE], 422);
    }

    public function testAddToPlaylist()
    {
        $playlist = $this->createPlaylist($this->fleshedOutPlaylist());
        $song1 = $this->createSong($this->fleshedOutSong());
        $song2 = $this->createSong($this->fleshedOutSong());
        $playlistWithSong = $this->addSongToPlaylist($playlist, $song1);
        // error_log("Playlist::fromJson:  " . json_encode($playlistWithSong));
        $this->assertEquals(count($playlistWithSong->songs), 1);
        $song1InPlaylist = $playlistWithSong->songs[0];
        $this->assertEquals($song1InPlaylist->title, $song1->title);

        $playlistWithSong = $this->addSongToPlaylist($playlist, $song2);

        $this->assertEquals(count($playlistWithSong->songs), 2);
        $song2InPlaylist;
        foreach($playlistWithSong->songs as $song) {
            if ($song2->id == $song->id) {
                $song2InPlaylist = $song;
                break;
            }
        }
        $this->assertEquals($song2InPlaylist->title, $song2->title);
        $this->deletePlaylist($playlist->id);
        $this->deleteSong($song1->id);
        $this->deleteSong($song2->id);
    }

    public function testRemoveFromPlaylist()
    {

    }

    public function addSongToPlaylist($playlist, $song)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->playlistHandlerPath . $playlist->id . '/addsong/' . $song->id, [
            'cookies' => $this->cookieJar
        ]);
        return Playlist::fromJson($response->getBody()->getContents());
    }

    public function getPlaylist($id)
    {
        $client = $this->getHandlerClient();
        $response = $client->get($this->playlistHandlerPath . $id, [
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        return Playlist::fromJson($response->getBody()->getContents());
    }

    public function createPlaylist($playlist)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->playlistHandlerPath, [
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
        $client = $this->getHandlerClient();
        $response = $client->post($this->playlistHandlerPath, [
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
        $client = $this->getHandlerClient();
        $response = $client->post($this->playlistHandlerPath . $playlist->id, [
            'json' => $playlist->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->playlistUpdated, '`playlistUpdated` should be true');
        try {
            $this->assertEquals(200, $response->getStatusCode());
            return $this->getPlaylist($json->playlistId);
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

    public function deletePlaylist($id)
    {
        $client = $this->getHandlerClient();
        $response = $client->post($this->playlistHandlerPath . $id . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

}