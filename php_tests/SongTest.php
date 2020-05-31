<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

final class SongTest extends TestBase
{
    public function testCreateSong()
    {
        $song = $this->fleshedOutSong();
        $createdSong = $this->getSongGateway()->createSong($song);
        try {
            $this->assertEquals($song->title, $createdSong->title);
            $this->assertEquals($song->filename, $createdSong->filename);
            $this->assertEquals($song->description, $createdSong->description);
        } finally {
            $this->getSongGateway()->deleteSong($createdSong->id);
        }
    }

    public function testUpdateSong()
    {
        $modifiedSong = $this->getSongGateway()->createSong($this->fleshedOutSong());
        $modifiedSong->title =Guid::create();
        $modifiedSong->description = 'pizza is good';
        $modifiedSong->filename = 'pizza.wav';
        $updatedSong = $this->getSongGateway()->updateSong($modifiedSong);
        try {
            $this->assertEquals($modifiedSong->title, $updatedSong->title);
            $this->assertEquals($modifiedSong->filename, $updatedSong->filename);
            $this->assertEquals($modifiedSong->description, $updatedSong->description);
        } finally {
            $this->getSongGateway()->deleteSong($modifiedSong->id);
        }
    }

    public function testCreateSongWithDupeTitle()
    {
        // create a song
        $song1 = $this->getSongGateway()->createSong($this->fleshedOutSong());
        // make a song with the same title
        $song2 = $this->fleshedOutSong();
        $song2->title = $song1->title;
        $expectedErrors = [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $song1->title)];
        $this->getSongGateway()->createSongWithErrors($song2, $expectedErrors, 409);
        // delete the first song to cleanup
        $this->getSongGateway()->deleteSong($song1->id);
    }

    public function testCreateSongWithLongTitle()
    {
        // create with long title
        $song = $this->fleshedOutSong();
        $song->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $this->getSongGateway()->createSongWithErrors($song, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE], 422);
    }

    public function testCreateSongWithBlankTitle()
    {
        $song = $this->fleshedOutSong();
        $song->title = '';
        $this->getSongGateway()->createSongWithErrors($song, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE], 422);
    }

}