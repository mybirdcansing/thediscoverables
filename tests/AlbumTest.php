<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

final class AlbumTest extends TestBase
{
    public function testCreateAlbum()
    {
        $album = $this->fleshedOutAlbum();
        $json = $this->getAlbumGateway()->createAlbum($album);
        $createdAlbum = $this->getAlbumGateway()->getAlbum($json->albumId);
        try {
            $this->assertEquals($album->title, $createdAlbum->title);
            $this->assertEquals($album->playlist, $createdAlbum->playlist);
            $this->assertEquals($album->description, $createdAlbum->description);
        } finally {
            $this->getAlbumGateway()->deleteAlbum($createdAlbum);
            $this->getPlaylistGateway()->deletePlaylist($createdAlbum->playlist);
        }
    }

    public function testUpdateAlbum()
    {
        $json = $this->getAlbumGateway()->createAlbum($this->fleshedOutAlbum());
        $modifiedAlbum = $this->getAlbumGateway()->getAlbum($json->albumId);
        
        $modifiedAlbum->title = GUID();
        $modifiedAlbum->description = 'pizza is good';
        $json = $this->getAlbumGateway()->updateAlbum($modifiedAlbum);
        $updatedAlbum = $this->getAlbumGateway()->getAlbum($json->albumId);
        try {
            $this->assertEquals($modifiedAlbum->title, $updatedAlbum->title);
            $this->assertEquals($modifiedAlbum->description, $updatedAlbum->description);
        } finally {
            $this->getAlbumGateway()->deleteAlbum($modifiedAlbum);
            $this->getPlaylistGateway()->deletePlaylist($modifiedAlbum->playlist);
        }
    }

    public function testCreateAlbumWithDupeTitle()
    {
        $json = $this->getAlbumGateway()->createAlbum($this->fleshedOutAlbum());
        $album1 = $this->getAlbumGateway()->getAlbum($json->albumId);

        $album2 = $this->fleshedOutAlbum();
        $album2->title = $album1->title;
        $json = $this->getAlbumGateway()->createAlbum($album2, 409);

        $this->validateErrorMessages($json, [TITLE_TAKEN_CODE => sprintf(TITLE_TAKEN_MESSAGE, $album1->title)]);

        $this->getPlaylistGateway()->deletePlaylist($album2->playlist);
        $this->getAlbumGateway()->deleteAlbum($album1);
        $this->getPlaylistGateway()->deletePlaylist($album1->playlist);
    }

    public function testCreateAlbumWithLongTitle()
    {
        $album = $this->fleshedOutAlbum();
        $album->title = '123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456123456789012345678901234567890123456789012345678901234567890123456';
        $json = $this->getAlbumGateway()->createAlbum($album, 422);
        $this->validateErrorMessages($json, [TITLE_LONG_CODE => TITLE_LONG_MESSAGE]);
        $this->getPlaylistGateway()->deletePlaylist($album->playlist);
    }

    public function testCreateAlbumWithBlankTitle()
    {
        $album = $this->fleshedOutAlbum();
        $album->title = '';
        $json = $this->getAlbumGateway()->createAlbum($album, 422);
        $this->validateErrorMessages($json, [TITLE_BLANK_CODE => TITLE_BLANK_MESSAGE]);
        $this->getPlaylistGateway()->deletePlaylist($album->playlist);
    }
}