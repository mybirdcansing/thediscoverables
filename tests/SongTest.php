<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/Configuration.php';
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class SongTest extends TestBase
{
    public function testCreateSong()
    {
        $song = $this->fleshedOutSong();
        $createdSong = $this->createSong($song);
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
        $modifiedSong = $this->createSong($this->fleshedOutSong());
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
        $song = $this->createSong($song);
        $expectedErrors = [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $song->title)];
        $song2 = $this->createSongWithErrors($song, $expectedErrors, 409);
        $this->deleteSong($song->id);
    }

    public function testCreateWithLongTitle()
    {
        // create with long title
        $song = $this->fleshedOutSong();
        $song->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $this->createSongWithErrors($song, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE], 422);
    }

    public function testCreateWithBlankTitle()
    {
        $song = $this->fleshedOutSong();
        $song->title = '';
        $this->createSongWithErrors($song, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE], 422);
    }


}