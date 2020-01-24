<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class PlaylistTest extends TestBase
{

    public function testCreatePlaylist_1()
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

    public function testUpdatePlaylist()
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

    public function testCreatePlaylistWithDupeTitle()
    {
        $playlist = $this->fleshedOutPlaylist();
        $playlist = $this->createPlaylist($playlist);
        $expectedErrors = [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $playlist->title)];
        $playlist2 = $this->createPlaylistWithErrors($playlist, $expectedErrors, 409);
        $this->deletePlaylist($playlist->id);
    }

    public function testCreatePlaylistWithLongTitle()
    {
        // create with long title
        $playlist = $this->fleshedOutPlaylist();
        $playlist->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $this->createPlaylistWithErrors($playlist, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE], 422);
    }

    public function testCreatePlaylistWithBlankTitle()
    {
        $playlist = $this->fleshedOutPlaylist();
        $playlist->title = '';
        $this->createPlaylistWithErrors($playlist, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE], 422);
    }

    public function testAddPlaylistToPlaylist()
    {
        $playlist = $this->createPlaylist($this->fleshedOutPlaylist());
        $song1 = $this->createSong($this->fleshedOutSong());
        $song2 = $this->createSong($this->fleshedOutSong());
        $playlistWithSong = $this->addSongToPlaylist($playlist, $song1);
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

    public function testRemovePlaylistFromPlaylist()
    {
        $playlist = $this->createPlaylist($this->fleshedOutPlaylist());
        $song1 = $this->createSong($this->fleshedOutSong());
        $playlistWithSongs = $this->addSongToPlaylist($playlist, $song1);
        $song2 = $this->createSong($this->fleshedOutSong());
        $playlis = $this->removeSongFromPlaylist($playlist, $song2);
        $this->assertEquals(count($playlis->songs), 1);
        $song1InPlaylist = $playlis->songs[0];
        $this->assertEquals($song1InPlaylist->title, $song1->title);
        $this->deletePlaylist($playlist->id);
        $this->deleteSong($song1->id);
        $this->deleteSong($song2->id);
    }
}