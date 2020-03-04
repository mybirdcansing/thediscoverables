<?php
require_once __DIR__ . '/../../messages.php';

class CatalogController {

    private $db;
    private $action;
    private $songData;
    private $playlistData;
    private $albumData;
    

    public function __construct($dbConnection, $action)
    {
        $this->action = $action;
        $this->songData = new SongData($dbConnection);
        $this->playlistData = new PlaylistData($dbConnection);
        $this->albumData = new AlbumData($dbConnection);
    }

    public function processRequest()
    {
        switch ($this->action) {
            case GET_ACTION:
                $response = $this->_getCatalog();
                break;
            default:
                $response = $this->_notFoundResponse();
                break;
        }

        if (array_key_exists('problem_header', $response)) {
            header('Content-Type: application/problem+json; charset=UTF-8');
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function _getCatalog()
    {
        $songs = $this->songData->findAll();
        $playlists = $this->playlistData->findAll();
        $playlistSongs = $this->playlistData->getPlaylistSongs();
        $albums = $this->albumData->findAll();

        $catalog = new stdClass();
        $catalog->songs = [];
        $catalog->songList = [];
        $catalog->playlists = [];
        $catalog->playlistList = [];
        $catalog->playlistSongIndex = [];
        $catalog->albums = [];
        $catalog->albumList = [];

        foreach($songs as $song) {
            $catalog->songs[$song->id] = $song;
            $catalog->songList[] = $song->id;
        }

        foreach($playlists as $playlist) {
            $playlist->songs = [];
            $catalog->playlists[$playlist->id] = $playlist;
            $catalog->playlistList[] = $playlist->id;
        }
        $listOfPlaylistSongs = [];
        foreach($playlistSongs as $entry) {
            if (!array_key_exists($entry->playlistId, $listOfPlaylistSongs)) {
                $listOfPlaylistSongs[$entry->playlistId] = [];
            }
            $listOfPlaylistSongs[$entry->playlistId][] = $entry;
            $catalog->playlistSongIndex[] = $entry;
        }

        foreach($catalog->playlists as $playlist) {
            // $indexList = array_filter($playlistSongs, function($v, $k) use ($playlist) {
            //     return $v->playlistId == $playlist->id;
            // }, ARRAY_FILTER_USE_BOTH);
            if (array_key_exists($playlist->id, $listOfPlaylistSongs)) {                
                $indexList = $listOfPlaylistSongs[$playlist->id];
                usort($indexList, function($a, $b) {
                    return strcmp(intval($a->orderIndex), intval($b->orderIndex));
                });
                foreach($indexList as $entry) {
                    $catalog->playlists[$entry->playlistId]->songs[] = $entry->songId;
                }
            }
            
        }

        foreach($albums as $album) {
            $album->playlist = $album->playlistId;
            unset($album->playlistId); // we don't need the playlistId here
            $catalog->albums[$album->id] = $album;
            $catalog->albumList[] = $album->id;
        }
        $catalog->playlistSongIndex = [];
        return $this->_okResponse(get_object_vars($catalog));
    }


    private function _okResponse($json = '{}')
    {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($json);
        return $response;
    }

    private function _notFoundResponse($json = null)
    {
        $response['problem_header'] = true;
        if ($json == null) {
            $json = [
                "errorMessages" => [
                    CATALOG_NOT_FOUND_CODE => CATALOG_NOT_FOUND_MESSAGE
                ]
            ];
        }
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode($json);
        return $response;
    }
}
