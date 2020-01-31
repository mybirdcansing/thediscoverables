<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

final class AlbumTest extends TestBase
{
    public function testCreateAlbum()
    {
        $album = $this->fleshedOutAlbum();
        $createdAlbum = $this->getAlbumGateway()->createAlbum($album);
        try {
            $this->assertEquals($album->title, $createdAlbum->title);
            $this->assertEquals($album->playlist->id, $createdAlbum->playlist->id);
            $this->assertEquals($album->description, $createdAlbum->description);
        } finally {
            $this->getAlbumGateway()->deleteAlbum($createdAlbum);
            $this->getPlaylistGateway()->deletePlaylist($createdAlbum->playlist->id);
        }
    }

    public function testUpdateAlbum()
    {
        $modifiedAlbum = $this->getAlbumGateway()->createAlbum($this->fleshedOutAlbum());
        $modifiedAlbum->title = GUID();
        $modifiedAlbum->description = 'pizza is good';
        $updatedAlbum = $this->getAlbumGateway()->updateAlbum($modifiedAlbum);
        try {
            $this->assertEquals($modifiedAlbum->title, $updatedAlbum->title);
            $this->assertEquals($modifiedAlbum->description, $updatedAlbum->description);
        } finally {
            $this->getAlbumGateway()->deleteAlbum($modifiedAlbum);
            $this->getPlaylistGateway()->deletePlaylist($modifiedAlbum->playlist->id);
        }
    }

    public function testCreateAlbumWithDupeTitle()
    {
        $album1 = $this->getAlbumGateway()->createAlbum($this->fleshedOutAlbum());
        $expectedErrors = [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $album1->title)];
        $album2 = $this->fleshedOutAlbum();
        $album2->title = $album1->title;
        $this->getAlbumGateway()->createAlbumWithErrors($album2, $expectedErrors, 409);
        $this->getPlaylistGateway()->deletePlaylist($album2->playlist->id);
        $this->getAlbumGateway()->deleteAlbum($album1);
        $this->getPlaylistGateway()->deletePlaylist($album1->playlist->id);
    }

    public function testCreateAlbumWithLongTitle()
    {
        $album = $this->fleshedOutAlbum();
        $album->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $this->getAlbumGateway()->createAlbumWithErrors($album, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE], 422);
        $this->getPlaylistGateway()->deletePlaylist($album->playlist->id);
    }

    public function testCreateAlbumWithBlankTitle()
    {
        $album = $this->fleshedOutAlbum();
        $album->title = '';
        $this->getAlbumGateway()->createAlbumWithErrors($album, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE], 422);
        $this->getPlaylistGateway()->deletePlaylist($album->playlist->id);
    }
}